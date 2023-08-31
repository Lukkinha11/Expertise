<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Concerns\HasAffixes;
use Filament\Forms\Components\Field;

class Money extends Field
{
    use HasAffixes;

    protected string $view = 'forms.components.money';
}
