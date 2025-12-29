<?php

namespace App\Filament\Resources\GuruNilais\Pages;

use Filament\Schemas\Components\Tabs\Tab;
use App\Filament\Resources\GuruNilais\GuruNilaiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListGuruNilais extends ListRecords
{
    protected static string $resource = GuruNilaiResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\Action::make('create_nilai')
    //             ->label('Buat Penilaian Baru')
    //             ->icon('heroicon-o-plus')
    //             ->color('primary')
    //             ->url(function () {
    //                 $guru = \Illuminate\Support\Facades\Auth::user()->guru;
    //                 $url = \App\Filament\Resources\NilaiResource::getUrl('create');

    //                 if ($guru) {
    //                     $url .= '?guru_id=' . $guru->id;
    //                 }

    //                 return $url;
    //             })
    //             ->button()
    //             ->keyBindings(['cmd+n', 'ctrl+n']),
    //     ];
    // }
    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Penilaian'),
            'this_month' => Tab::make('Bulan Ini')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereMonth('created_at', now()->month))
                ->badge(fn() => $this->getModel()::whereMonth('created_at', now()->month)->count()),
            'this_week' => Tab::make('Minggu Ini')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                ->badge(fn() => $this->getModel()::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()),
        ];
    }
}
