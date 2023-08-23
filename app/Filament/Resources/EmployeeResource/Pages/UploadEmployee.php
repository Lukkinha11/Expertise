<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Imports\EmployeeImport;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class UploadEmployee
{
    protected static string $resource = EmployeeResource::class;

    public static $uploadedFileName = '';

    public function upload()
    {
        $filePath = Storage::path('public/' . self::$uploadedFileName);

        if (!file_exists($filePath)) {
            return $this->sendErrorNotification('Erro ao importar planilha');
        }

        try {
            $this->importAndDeleteFile($filePath);
            return $this->sendSuccessNotification('Planilha Importada com Sucesso');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $this->handleValidationErrors($e);
        }
    }

    private function importAndDeleteFile($filePath)
    {
        $import = new EmployeeImport;
        Excel::import($import, $filePath);

        // Excluir o arquivo após a importação
        Storage::delete('public/' . self::$uploadedFileName);
    }

    private function sendErrorNotification($message)
    {
        return Notification::make()
            ->title($message)
            ->danger()
            ->send();
    }

    private function sendSuccessNotification($message)
    {
        return Notification::make()
            ->title($message)
            ->success()
            ->send();
    }

    private function handleValidationErrors($e)
    {
        $data = [];

        $failures = $e->failures();
        foreach ($failures as $failure) {
            if (array_filter($failure->values())) {
                $data[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                ];
            }
        }

        Notification::make()
            ->title('Atenção')
            ->warning()
            ->body('Ocorreram erros ao importar a planilha')
            ->actions([
                Action::make('view')
                    ->label('visualizar')
                    ->button()
                    ->url(route('show.exceptions', ['data' => $data]), shouldOpenInNewTab: true)
            ])
            ->send();
    }
}
