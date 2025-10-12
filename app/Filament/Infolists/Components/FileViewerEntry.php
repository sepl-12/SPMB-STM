<?php

namespace App\Filament\Infolists\Components;

use Filament\Infolists\Components\Entry;

class FileViewerEntry extends Entry
{
    protected string $view = 'filament.infolists.components.file-viewer-entry';

    protected ?string $fileName = null;
    protected ?string $fileSize = null;
    protected ?string $mimeType = null;
    protected ?string $downloadUrl = null;
    protected ?string $previewUrl = null;
    protected bool $canPreview = false;

    public function fileName(?string $fileName): static
    {
        $this->fileName = $fileName;
        return $this;
    }

    public function fileSize(?string $fileSize): static
    {
        $this->fileSize = $fileSize;
        return $this;
    }

    public function mimeType(?string $mimeType): static
    {
        $this->mimeType = $mimeType;
        $this->canPreview = $this->determineCanPreview($mimeType);
        return $this;
    }

    public function downloadUrl(?string $downloadUrl): static
    {
        $this->downloadUrl = $downloadUrl;
        return $this;
    }

    public function previewUrl(?string $previewUrl): static
    {
        $this->previewUrl = $previewUrl;
        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function getFileSize(): ?string
    {
        return $this->fileSize;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function getDownloadUrl(): ?string
    {
        return $this->downloadUrl;
    }

    public function getPreviewUrl(): ?string
    {
        return $this->previewUrl ?? $this->downloadUrl;
    }

    public function canPreview(): bool
    {
        return $this->canPreview;
    }

    public function getFileIcon(): string
    {
        $mimeType = $this->mimeType ?? '';
        
        return match (true) {
            str_contains($mimeType, 'pdf') => 'heroicon-o-document-text',
            str_contains($mimeType, 'word') || str_contains($mimeType, 'document') => 'heroicon-o-document',
            str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet') => 'heroicon-o-table-cells',
            str_contains($mimeType, 'image') => 'heroicon-o-photo',
            str_contains($mimeType, 'text') => 'heroicon-o-document-text',
            default => 'heroicon-o-document',
        };
    }

    public function getFileIconColor(): string
    {
        $mimeType = $this->mimeType ?? '';
        
        return match (true) {
            str_contains($mimeType, 'pdf') => 'danger',
            str_contains($mimeType, 'word') || str_contains($mimeType, 'document') => 'primary',
            str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet') => 'success',
            str_contains($mimeType, 'image') => 'warning',
            default => 'gray',
        };
    }

    protected function determineCanPreview(?string $mimeType): bool
    {
        if (!$mimeType) {
            return false;
        }

        $previewableMimeTypes = [
            'application/pdf',
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
            'text/plain',
        ];

        return in_array(strtolower($mimeType), $previewableMimeTypes);
    }
}
