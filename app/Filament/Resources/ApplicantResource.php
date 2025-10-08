<?php

namespace App\Filament\Resources;

use App\Exports\ApplicantsExport;
use App\Filament\Resources\ApplicantResource\Pages;
use App\Models\Applicant;
use App\Models\ExportTemplate;
use App\Models\FormField;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ApplicantResource extends Resource
{
    protected static ?string $model = Applicant::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'PPDB';

    protected static ?string $navigationLabel = 'Calon Siswa';

    protected static ?string $slug = 'applicants';
    
    protected static ?string $modelLabel = 'Calon Siswa';
    
    protected static ?string $pluralModelLabel = 'Calon Siswa';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        $table = $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['wave', 'latestSubmission']))
            ->defaultSort('registered_datetime', 'desc');

        $columns = [
            TextColumn::make('registration_number')
                ->label('No. Pendaftaran')
                ->searchable()
                ->sortable(),
            TextColumn::make('applicant_full_name')
                ->label('Nama')
                ->searchable()
                ->sortable(),
            TextColumn::make('chosen_major_name')
                ->label('Jurusan')
                ->badge()
                ->color('info')
                ->sortable(),
            TextColumn::make('wave.wave_name')
                ->label('Gelombang')
                ->badge()
                ->color('warning')
                ->sortable(),
            BadgeColumn::make('payment_status')
                ->label('Status Bayar')
                ->colors([
                    'success' => ['paid', 'success'],
                    'danger' => ['failed'],
                    'warning' => ['pending', 'unpaid'],
                    'gray' => ['refunded', 'void'],
                ])
                ->formatStateUsing(fn (?string $state) => match ($state) {
                    'paid', 'success' => 'Lunas',
                    'unpaid' => 'Belum Bayar',
                    'pending' => 'Menunggu',
                    'failed' => 'Gagal',
                    'refunded' => 'Dikembalikan',
                    default => ucfirst((string) $state),
                })
                ->sortable(),
            TextColumn::make('registered_datetime')
                ->label('Tgl Daftar')
                ->dateTime('d M Y H:i')
                ->sortable(),
        ];

        foreach (self::getExportableFields() as $field) {
            $columns[] = TextColumn::make('answers.' . $field->field_key)
                ->label($field->field_label)
                ->state(fn (Applicant $record) => self::formatAnswerValue($record->getLatestAnswerForField($field->field_key)))
                ->toggleable(isToggledHiddenByDefault: true)
                ->wrap();
        }

        return $table
            ->columns($columns)
            ->filters(self::getFilters())
            ->actions([
                ViewAction::make(),
                self::makeExportAction(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    self::makeBulkExportAction(),
                ]),
            ]);
    }

    protected static function getFilters(): array
    {
        $filters = [
            SelectFilter::make('wave_id')
                ->label('Gelombang')
                ->relationship('wave', 'wave_name'),
            SelectFilter::make('chosen_major_name')
                ->label('Jurusan')
                ->options(fn () => Applicant::query()
                    ->orderBy('chosen_major_name')
                    ->distinct()
                    ->pluck('chosen_major_name', 'chosen_major_name')
                    ->filter()
                    ->all()),
            SelectFilter::make('payment_status')
                ->label('Status Bayar')
                ->options([
                    'paid' => 'Lunas',
                    'unpaid' => 'Belum Bayar',
                    'pending' => 'Menunggu',
                    'failed' => 'Gagal',
                    'refunded' => 'Dikembalikan',
                ]),
        ];

        foreach (self::getFilterableFields() as $field) {
            $filters[] = Filter::make('field_' . $field->id)
                ->label($field->field_label)
                ->form([
                    self::buildFieldFilterComponent($field),
                ])
                ->query(fn (Builder $query, array $data) => self::applyFieldFilter($query, $field, $data['value'] ?? null))
                ->indicateUsing(fn (array $data) => self::formatFilterIndicator($field->field_label, $data['value'] ?? null));
        }

        return $filters;
    }

    protected static function makeExportAction(): Action
    {
        return Action::make('export')
            ->label('Export')
            ->icon('heroicon-o-arrow-down-tray')
            ->visible(fn () => ExportTemplate::query()->exists())
            ->form([
                Forms\Components\Select::make('template_id')
                    ->label('Template Ekspor')
                    ->options(fn () => ExportTemplate::query()
                        ->orderByDesc('is_default')
                        ->orderBy('template_name')
                        ->pluck('template_name', 'id')
                        ->all())
                    ->required(),
            ])
            ->action(function (Applicant $record, array $data) {
                $template = ExportTemplate::find($data['template_id']);
                
                if (!$template) {
                    Notification::make()
                        ->title('Template tidak ditemukan')
                        ->danger()
                        ->send();
                    return;
                }

                $applicants = collect([$record]);
                $filename = 'pendaftar_' . $record->registration_number . '_' . now()->format('YmdHis') . '.xlsx';

                try {
                    return Excel::download(
                        new ApplicantsExport($template, $applicants),
                        $filename
                    );
                } catch (\Throwable $e) {
                    Notification::make()
                        ->title('Ekspor gagal')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    protected static function makeBulkExportAction(): BulkAction
    {
        return BulkAction::make('bulkExport')
            ->label('Export terpilih')
            ->icon('heroicon-o-arrow-down-tray')
            ->requiresConfirmation()
            ->form([
                Forms\Components\Select::make('template_id')
                    ->label('Template Ekspor')
                    ->options(fn () => ExportTemplate::query()
                        ->orderByDesc('is_default')
                        ->orderBy('template_name')
                        ->pluck('template_name', 'id')
                        ->all())
                    ->required(),
            ])
            ->action(function (Collection $records, array $data) {
                $template = ExportTemplate::find($data['template_id']);
                
                if (!$template) {
                    Notification::make()
                        ->title('Template tidak ditemukan')
                        ->danger()
                        ->send();
                    return;
                }

                $filename = 'pendaftar_bulk_' . now()->format('YmdHis') . '.xlsx';

                try {
                    return Excel::download(
                        new ApplicantsExport($template, $records),
                        $filename
                    );
                } catch (\Throwable $e) {
                    Notification::make()
                        ->title('Ekspor gagal')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    protected static function getExportableFields(): Collection
    {
        return FormField::query()
            ->where('is_exportable', true)
            ->where('is_archived', false)
            ->whereHas('formVersion', fn (Builder $query) => $query->where('is_active', true))
            ->orderBy('field_order_number')
            ->get();
    }

    protected static function getFilterableFields(): Collection
    {
        return FormField::query()
            ->where('is_filterable', true)
            ->where('is_archived', false)
            ->whereHas('formVersion', fn (Builder $query) => $query->where('is_active', true))
            ->orderBy('field_order_number')
            ->get();
    }

    protected static function buildFieldFilterComponent(FormField $field): Component
    {
        $label = $field->field_label;
        $options = self::extractFieldOptions($field);

        return match ($field->field_type) {
            'date' => Forms\Components\DatePicker::make('value')->label($label),
            'number' => Forms\Components\TextInput::make('value')->label($label)->numeric(),
            'boolean' => Forms\Components\Select::make('value')
                ->label($label)
                ->options(['1' => 'Ya', '0' => 'Tidak']),
            'multi_select', 'checkbox' => Forms\Components\Select::make('value')
                ->label($label)
                ->multiple()
                ->options($options),
            default => filled($options)
                ? Forms\Components\Select::make('value')->label($label)->options($options)->searchable()
                : Forms\Components\TextInput::make('value')->label($label),
        };
    }

    protected static function extractFieldOptions(FormField $field): array
    {
        return collect($field->field_options_json ?? [])
            ->mapWithKeys(function ($option, $key) {
                if (is_array($option)) {
                    $value = $option['value'] ?? $option['key'] ?? $option['id'] ?? null;
                    $label = $option['label'] ?? $value ?? $key;

                    return $value !== null ? [$value => $label] : [];
                }

                if (is_string($option)) {
                    return [$option => $option];
                }

                return [$key => $option];
            })
            ->filter(fn ($label, $value) => filled($value))
            ->all();
    }

    protected static function applyFieldFilter(Builder $query, FormField $field, mixed $value): Builder
    {
        if ((is_array($value) && blank($value)) || (! is_array($value) && blank($value) && $value !== 0 && $value !== '0')) {
            return $query;
        }

        return $query->whereHas('submissions.submissionAnswers', function (Builder $answers) use ($field, $value) {
            $answers->where('form_field_id', $field->id);

            if (is_array($value)) {
                $values = array_filter($value, fn ($item) => filled($item));

                if ($values === []) {
                    return;
                }

                $answers->whereIn('answer_value_text', $values);

                return;
            }

            switch ($field->field_type) {
                case 'number':
                    $answers->where('answer_value_number', (float) $value);
                    break;
                case 'boolean':
                    $answers->where('answer_value_boolean', filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE));
                    break;
                case 'date':
                    $answers->whereDate('answer_value_date', $value);
                    break;
                default:
                    $answers->where('answer_value_text', 'like', '%' . $value . '%');
                    break;
            }
        });
    }

    protected static function formatFilterIndicator(string $label, mixed $value): ?string
    {
        if (blank($value) && $value !== '0' && $value !== 0) {
            return null;
        }

        $display = is_array($value)
            ? implode(', ', array_filter($value, fn ($item) => filled($item)))
            : (string) $value;

        if ($display === '') {
            return null;
        }

        return $label . ': ' . $display;
    }

    public static function formatAnswerValue(mixed $value): ?string
    {
        if (is_array($value)) {
            $flattened = Arr::flatten($value);
            $display = implode(', ', array_filter($flattened, fn ($item) => filled($item)));

            return $display === '' ? null : $display;
        }

        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }

        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApplicants::route('/'),
            'view' => Pages\ViewApplicant::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

     public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() > 0 ? 'success' : 'gray';
    }
}
