<?php

namespace App\Filament\Resources\AccountingResource\Pages;

use App\Filament\Resources\AccountingResource;
use App\Models\Accounting;
use App\Models\Employee;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageAccountings extends ManageRecords
{
    protected static string $resource = AccountingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->using(function (array $data, string $model): Accounting {
                
                $slashPosition = strpos($data['date'], '/');
                $dateParts = explode('/', $data['date']);

                if ($slashPosition !== 4) {            
                    $formattedDate = $dateParts[1] . '/' . $dateParts[0];
                    $data['date'] = $formattedDate;
                }

                $month = $dateParts[1];
                $year = $dateParts[0];

                if ($month < 1 || $month > 12 || $year < 1900 || $year > date('Y')) {
                    $this->sendNotification("Formato incorreto de Data!","A data deve está no formato Ano/mês.");
                }

                $accounting = $model::where('employee_id', $data['employee_id'])->where('date', $data['date'])->first();

                if($accounting) {
                    $this->sendNotification("ATENÇÃO!","O Resp. Contábil já está cadastrado!");
                }

                $employee = Employee::find($data['employee_id']);

                $companiesData = array_fill_keys($data['companies']['company_id'], ['date' => $data['date']]);

                $employee->companies()->syncWithoutDetaching($companiesData);

                return $model::create($data);
            }),
        ];
    }

    private function sendNotification($title, $body) {
        Notification::make()
            ->danger()
            ->title($title)
            ->body($body)
            ->send();                
        $this->halt();
    }
}
