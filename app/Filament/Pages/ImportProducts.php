<?php

namespace App\Filament\Pages;

use App\Imports\ProductsImport;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;

class ImportProducts extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string|\UnitEnum|null $navigationGroup = 'Commerce';

    protected static ?string $navigationLabel = 'Import Products';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.import-products';

    public string $stage = 'upload'; // 'upload' | 'preview' | 'done'

    public ?string $tempPath = null;

    public array $previewRows = [];

    public array $previewHeaders = [];

    public int $totalRows = 0;

    public int $created = 0;

    public int $updated = 0;

    public array $importErrors = [];

    private function resolvedPath(): ?string
    {
        if (! $this->tempPath) {
            return null;
        }
        $base = storage_path('app/imports');
        $safe = $base . DIRECTORY_SEPARATOR . basename($this->tempPath);
        return str_starts_with(realpath($safe) ?: '', $base) ? $safe : null;
    }

    public function goToPreview(): void
    {
        $path = $this->resolvedPath();

        if (! $path || ! file_exists($path)) {
            Notification::make()->title('File not found. Please re-upload.')->danger()->send();
            return;
        }

        $handle = fopen($path, 'r');
        $this->previewHeaders = fgetcsv($handle) ?: [];
        $previewData = [];
        $count = 0;
        while (($row = fgetcsv($handle)) !== false && $count < 5) {
            $previewData[] = $row;
            $count++;
        }
        fclose($handle);
        $this->previewRows = $previewData;

        $this->stage = 'preview';
    }

    public function runImport(): void
    {
        $path = $this->resolvedPath();

        if (! $path || ! file_exists($path)) {
            Notification::make()->title('Import file not found. Please re-upload.')->danger()->send();
            $this->stage = 'upload';
            return;
        }

        $import = new ProductsImport();

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $type = $ext === 'xlsx' ? \Maatwebsite\Excel\Excel::XLSX : \Maatwebsite\Excel\Excel::CSV;

        Excel::import($import, $path, null, $type);

        $this->created      = $import->created;
        $this->updated      = $import->updated;
        $this->importErrors = $import->errors;

        @unlink($path);
        $this->tempPath = null;

        $this->stage = 'done';

        Notification::make()
            ->title("Import complete: {$this->created} created, {$this->updated} updated" . (count($this->importErrors) ? ', ' . count($this->importErrors) . ' errors' : ''))
            ->success()
            ->send();
    }

    public function resetImport(): void
    {
        $this->stage          = 'upload';
        $this->tempPath       = null;
        $this->previewRows    = [];
        $this->previewHeaders = [];
        $this->totalRows      = 0;
        $this->created        = 0;
        $this->updated        = 0;
        $this->importErrors   = [];
    }
}
