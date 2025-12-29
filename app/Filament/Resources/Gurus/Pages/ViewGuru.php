<?php

namespace App\Filament\Resources\Gurus\Pages;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Gurus\GuruResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewGuru extends ViewRecord
{
    protected static string $resource = GuruResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn() => Auth::user()?->isAdmin()),

            DeleteAction::make()
                ->visible(fn() => Auth::user()?->isAdmin()),
        ];
    }

    // Override to control access
    public function mount(int | string $record): void
    {
        // Only admin can view guru details
        if (!Auth::user()?->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        parent::mount($record);
    }
}
