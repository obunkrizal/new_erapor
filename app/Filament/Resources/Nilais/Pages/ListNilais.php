<?php

namespace App\Filament\Resources\NilaiResource\Pages;

use App\Filament\Resources\NilaiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListNilais extends ListRecords
{
    protected static string $resource = NilaiResource::class;

    public function mount(): void
    {
        // Redirect non-admin users
        if (!Auth::user()->isAdmin()) {
            \Filament\Notifications\Notification::make()
                ->title('Akses Ditolak')
                ->body('Hanya admin yang dapat mengakses halaman ini.')
                ->warning()
                ->send();

            $this->redirect('/admin');
            return;
        }

        parent::mount();
    }

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\Action::make('export_all')
    //             ->label('Export Semua Data')
    //             ->icon('heroicon-o-arrow-down-tray')
    //             ->color('info')
    //             ->action(function () {
    //                 // Implement export functionality
    //                 \Filament\Notifications\Notification::make()
    //                     ->title('Export Data')
    //                     ->body('Fitur export akan segera tersedia')
    //                     ->info()
    //                     ->send();
    //             }),

    //         // Removed Statistik button as per user request
    //     ];
    // }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Penilaian')
                ->badge(fn () => $this->getModel()::count()),

            'this_month' => Tab::make('Bulan Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereMonth('created_at', now()->month))
                ->badge(fn () => $this->getModel()::whereMonth('created_at', now()->month)->count()),

            'this_week' => Tab::make('Minggu Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                ->badge(fn () => $this->getModel()::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()),
        ];
    }
}
