<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Component;

class Wizard extends Component
{
    protected string $view = 'forms.components.wizard';

    public static function make(): static
    {
        return app(static::class);
    }
}
