<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected static string $view = 'filament.widgets.quick-actions-widget';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';
    public static function canView(): bool
    {
        return auth()->user()->isAdmin();
    }
}
