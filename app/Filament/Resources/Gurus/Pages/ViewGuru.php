<?php

namespace App\Filament\Resources\GuruResource\Pages;

use App\Filament\Resources\GuruResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewGuru extends ViewRecord
{
    protected static string $resource = GuruResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn() => Auth::user()?->isAdmin()),

            Actions\DeleteAction::make()
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
