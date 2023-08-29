<?php

namespace App\Filament\Resources\SupervasionResource\Pages;

use App\Filament\Resources\SupervasionResource;
use App\Models\Employee;
use App\Models\Supervasion;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Closure;

class ManageSupervasions extends ManageRecords
{
    protected static string $resource = SupervasionResource::class;

    protected function getTableRecordUrlUsing(): ?Closure
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->using(function (array $data, string $model): Supervasion {
                
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
                    $this->sendNotification("ATENÇÃO!","O Resp. Fiscal já está cadastrado!");
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