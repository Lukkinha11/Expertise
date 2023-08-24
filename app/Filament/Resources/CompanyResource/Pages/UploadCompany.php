<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use App\Imports\CompanyImport;
use App\Models\Employee;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class UploadCompany
{
    protected static string $resource = CompanyResource::class;

    public static $uploadedFileName = '';

    public function upload()
    {
        $filePath = Storage::path('public/' . self::$uploadedFileName);

        if (!file_exists($filePath)) {
            return $this->sendErrorNotification('Erro ao importar planilha');
        }

        try {
            $result = $this->importAndDeleteFile($filePath);

            if ($result === "Ocorreu um erro ao importar a planilha!") {
                return $this->sendErrorNotification('Ocorreu um erro ao importar a planilha!');
            }
            return $this->sendSuccessNotification('Planilha Importada com Sucesso');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $this->handleValidationErrors($e);
        }
    }

    private function importAndDeleteFile($filePath)
    {
        //Fazer tratamento da exception da transation
        $import = new CompanyImport;
        Excel::import($import, $filePath);

        if ($import->employeesAndCompany) {
            foreach ($import->employeesAndCompany as $item) {

                $employee = Employee::firstOrCreate([
                        'name' => $item['responsible']
                    ], [
                        'name' => $item['responsible'],
                        'email' => "DEFINIR_EMAIL@TESTE.COM",
                        'departament' => 'Definir Departamento',
                    ]);

                $employee->companies()->syncWithoutDetaching($item['company_id']);
            }
        }

        if ($import->exceptionTransation) {
            $messages = 'Ocorreu um erro ao importar a planilha!';
            return $messages ;
        }
        
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
