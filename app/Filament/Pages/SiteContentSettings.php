<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SiteContentSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationGroup = 'Konten Website';

    protected static ?string $navigationLabel = 'Halaman Utama';
    
    protected static ?string $title = 'Pengaturan Halaman Utama';

    protected static ?string $slug = 'site-content';

    protected static string $view = 'filament.pages.site-content-settings';

    public ?SiteSetting $setting = null;

    public array $data = [];

    public function mount(): void
    {
        $this->setting = SiteSetting::query()->first();

        $this->data = $this->setting ? [
            'hero_title_text' => $this->setting->hero_title_text,
            'hero_subtitle_text' => $this->setting->hero_subtitle_text,
            'hero_image_path' => $this->setting->hero_image_path,
            'requirements_markdown' => $this->setting->requirements_markdown,
            'faq_items' => $this->setting->faq_items_json ?? [],
            'cta_button_label' => $this->setting->cta_button_label,
            'cta_button_url' => $this->setting->cta_button_url,
            'timeline_items' => $this->setting->timeline_items_json ?? [],
        ] : [
            'hero_title_text' => '',
            'hero_subtitle_text' => null,
            'hero_image_path' => null,
            'requirements_markdown' => null,
            'faq_items' => [],
            'cta_button_label' => null,
            'cta_button_url' => null,
            'timeline_items' => [],
        ];

        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Hero Halaman')
                    ->description('Atur teks dan gambar hero pada halaman utama.')
                    ->schema([
                        TextInput::make('hero_title_text')
                            ->label('Judul')
                            ->required()
                            ->maxLength(120),
                        Textarea::make('hero_subtitle_text')
                            ->label('Subjudul')
                            ->rows(3),
                        FileUpload::make('hero_image_path')
                            ->label('Gambar Hero')
                            ->image()
                            ->visibility('public')
                            ->directory('site/hero')
                            ->imageEditor()
                            ->maxSize(2048)
                            ->helperText('Unggah gambar dengan rasio 16:9 untuk tampilan terbaik.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Syarat & Informasi')
                    ->schema([
                        MarkdownEditor::make('requirements_markdown')
                            ->label('Syarat Pendaftaran')
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'heading', 'bold', 'italic', 'strike', 'link', 'bulletList', 'orderedList', 'blockquote', 'codeBlock', 'table', 'undo', 'redo',
                            ]),
                    ]),
                Section::make('FAQ')
                    ->schema([
                        Repeater::make('faq_items')
                            ->label('Pertanyaan Umum')
                            ->schema([
                                TextInput::make('question')
                                    ->label('Pertanyaan')
                                    ->required()
                                    ->maxLength(150),
                                Textarea::make('answer')
                                    ->label('Jawaban')
                                    ->rows(3)
                                    ->required(),
                            ])
                            ->default([])
                            ->minItems(0)
                            ->collapsible()
                            ->reorderable(),
                    ]),
                Section::make('Call To Action')
                    ->schema([
                        TextInput::make('cta_button_label')
                            ->label('Label Tombol')
                            ->maxLength(50),
                        TextInput::make('cta_button_url')
                            ->label('URL Tombol')
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make('Timeline')
                    ->schema([
                        Repeater::make('timeline_items')
                            ->label('Tahapan Timeline')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Judul')
                                    ->required()
                                    ->maxLength(120),
                                DatePicker::make('date')
                                    ->label('Tanggal')
                                    ->required(),
                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->rows(2),
                            ])
                            ->default([])
                            ->collapsible()
                            ->reorderable(),
                    ]),
            ])
            ->columns(1)
            ->statePath('data');
    }

    public function submit(): void
    {
        $state = $this->form->getState();

        $payload = [
            'hero_title_text' => $state['hero_title_text'],
            'hero_subtitle_text' => $state['hero_subtitle_text'],
            'hero_image_path' => $state['hero_image_path'],
            'requirements_markdown' => $state['requirements_markdown'],
            'faq_items_json' => array_values($state['faq_items'] ?? []),
            'cta_button_label' => $state['cta_button_label'],
            'cta_button_url' => $state['cta_button_url'],
            'timeline_items_json' => array_values($state['timeline_items'] ?? []),
        ];

        $setting = $this->setting ?? new SiteSetting();
        $setting->fill($payload);
        $setting->save();

        $this->setting = $setting;

        Notification::make()
            ->title('Konten halaman utama diperbarui')
            ->success()
            ->send();
    }
}
