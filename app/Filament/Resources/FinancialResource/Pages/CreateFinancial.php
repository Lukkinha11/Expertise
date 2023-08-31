<?php

namespace App\Filament\Resources\FinancialResource\Pages;

use App\Filament\Resources\FinancialResource;
use App\Models\Financial;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Notifications\Notification;

class CreateFinancial extends CreateRecord
{
    protected static string $resource = FinancialResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); //Redireciona para a index após a criação do registro
    }

    // public function create(bool $another = false): void
    // {
    //     $this->authorizeAccess();

    //     try {
    //         $this->callHook('beforeValidate');

    //         $data = $this->form->getState();

    //         $this->callHook('afterValidate');

    //         $data = $this->mutateFormDataBeforeCreate($data);

    //         dd($data);

    //         /** @internal Read the DocBlock above the following method. */
    //         $this->createRecordAndCallHooks($data);
    //     } catch (Halt $exception) {
    //         return;
    //     }

    //     /** @internal Read the DocBlock above the following method. */
    //     $this->sendCreatedNotificationAndRedirect(shouldCreateAnotherInsteadOfRedirecting: $another);
    // }

    protected function mutateFormDataBeforeCreate(array $data): array
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
