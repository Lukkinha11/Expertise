<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ViewExceptions 
{
    protected static string $resource = UserResource::class;

    public function showException(Request $request)
    {
        // $messages = $request->data;
        // return view('filament.custom.show-execeptions', compact('messages'));
        return view('filament.custom.show-execeptions');
    }
}
