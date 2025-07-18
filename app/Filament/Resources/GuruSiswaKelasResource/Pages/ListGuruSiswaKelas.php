<?php

namespace App\Filament\Resources\GuruSiswaKelasResource\Pages;

use App\Filament\Resources\GuruSiswaKelasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListGuruSiswaKelas extends ListRecords
{
    protected static string $resource = GuruSiswaKelasResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make()
    //             ->label('Tambah Siswa ke Kelas'),
    //     ];
    // }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Siswa'),
            'aktif' => Tab::make('Aktif')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'aktif'))
                ->badge(fn () => $this->getModel()::where('status', 'aktif')->count()),
            'tidak_aktif' => Tab::make('Tidak Aktif')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'tidak_aktif'))
                ->badge(fn () => $this->getModel()::where('status', 'tidak_aktif')->count()),
            'pindah' => Tab::make('Pindah')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pindah'))
                ->badge(fn () => $this->getModel()::where('status', 'pindah')->count()),
            'lulus' => Tab::make('Lulus')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'lulus'))
                ->badge(fn () => $this->getModel()::where('status', 'lulus')->count()),
        ];
    }

    public function getTitle(): string
    {
        $kelasId = request()->get('kelas');
        if ($kelasId) {
            $kelas = \App\Models\Kelas::find($kelasId);
            return $kelas ? "Siswa Kelas {$kelas->nama_kelas}" : 'Siswa Kelas';
        }
        return 'Siswa Kelas';
    }
}
