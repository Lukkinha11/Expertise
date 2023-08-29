<?php

namespace App\Filament\Resources\SupervasionResource\Pages;

use App\Filament\Resources\SupervasionResource;
use App\Models\Supervasion;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageSupervasions extends ManageRecords
{
    protected static string $resource = SupervasionResource::class;

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