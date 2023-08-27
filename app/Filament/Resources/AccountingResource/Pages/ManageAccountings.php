<?php

namespace App\Filament\Resources\AccountingResource\Pages;

use App\Filament\Resources\AccountingResource;
use App\Models\Accounting;
use App\Models\Employee;
use Filament\Actions;
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

                    if ($slashPosition !== 4) {
                                                
                        $dateParts = explode('/', $data['date']);
                        $formattedDate = $dateParts[1] . '/' . $dateParts[0];
                        $data['date'] = $formattedDate;
                    }
                    
                    // $employee = Employee::find($data['employee_id']);
                    // $employee->companies()->syncWithoutDetaching($data['employee']['companies']);
                    
                    return $model::firstOrCreate([
                        'employee_id' => $data['employee_id'],
                        'date' => $data['date'],
                    ], [
                        'employee_id' => $data['employee_id'],
                        'date' => $data['date'],
                    ]);
                }),
        ];
    }
}
