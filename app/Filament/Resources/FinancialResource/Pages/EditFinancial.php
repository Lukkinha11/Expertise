<?php

namespace App\Filament\Resources\FinancialResource\Pages;

use App\Filament\Resources\FinancialResource;
use App\Models\Financial;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditFinancial extends EditRecord
{
    protected static string $resource = FinancialResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); //Redireciona para a index após a criação do registro
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $financial = Financial::where('company_id', $data['company_id'])->where('accrual_date', $data['accrual_date'])->exists();
          
        if($financial) {
            $this->sendNotification("ATENÇÃO!","O cliente já está cadastrado na data de competência informada!");
        }
        return $data;
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
