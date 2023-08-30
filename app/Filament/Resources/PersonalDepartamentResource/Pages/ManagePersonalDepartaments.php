<?php

namespace App\Filament\Resources\PersonalDepartamentResource\Pages;

use App\Filament\Resources\PersonalDepartamentResource;
use App\Models\Employee;
use App\Models\PersonalDepartament;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;

class ManagePersonalDepartaments extends ManageRecords
{
    protected static string $resource = PersonalDepartamentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make()
            // ->using(function (array $data, string $model): PersonalDepartament {
                
            //     $slashPosition = strpos($data['date'], '/');
            //     $dateParts = explode('/', $data['date']);

            //     if ($slashPosition !== 4) {
            //         $formattedDate = $dateParts[1] . '/' . $dateParts[0];
            //         $data['date'] = $formattedDate;
            //     }
                
            //     $month = $dateParts[1];
            //     $year = $dateParts[0];

            //     if ($month < 1 || $month > 12 || $year < 1900 || $year > date('Y')) {
            //         $this->sendNotification("Formato incorreto de Data!","A data deve está no formato Ano/mês.");
            //     }

            //     $personalDepartament = $model::where('employee_id', $data['employee_id'])->where('date', $data['date'])->first();

            //     if($personalDepartament) {
            //         $this->sendNotification("ATENÇÃO!","O Resp. DP já está cadastrado!");
            //     }

            //     $employee = Employee::find($data['employee_id']);

            //     $companiesData = array_fill_keys($data['companies']['company_id'], [
            //         'date' => $data['date'],
            //         'number_of_employees' => $data['number_of_employees'],
            //         'number_of_partners' => $data['number_of_partners'],
            //         'admissions' => $data['admissions'],
            //         'layoffs' => $data['layoffs'],
            //     ]);
            
            //     $employee->companies()->syncWithoutDetaching($companiesData);

            //     return $model::create($data);
            // }),
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
