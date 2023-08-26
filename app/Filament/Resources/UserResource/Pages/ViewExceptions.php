<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;

class ViewExceptions 
{
    protected static string $resource = UserResource::class;

    public function showException()
    {
        return view('filament.custom.show-execeptions');
    }
}
