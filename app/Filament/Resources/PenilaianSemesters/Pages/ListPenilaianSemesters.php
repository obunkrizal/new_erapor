<?php

namespace App\Filament\Resources\PenilaianSemesters\Pages;

use App\Filament\Resources\PenilaianSemesters\PenilaianSemesterResource;
use App\Models\Periode;
use App\Models\Siswa;
use App\Services\AutoNarasiGenerator;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPenilaianSemesters extends ListRecords
{
    protected static string $resource = PenilaianSemesterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('generateAutoPenilaian')
                ->label('Generate Auto Penilaian')
                ->icon('heroicon-o-sparkles')
                ->color('success')
                ->form([
                    Select::make('periode_id')
                        ->label('Periode')
                        ->options(Periode::all()->pluck('nama_periode', 'id'))
                        ->required()
                        ->searchable()
                        ->preload(),
                ])
                ->action(function (array $data): void {
                    $periode = Periode::find($data['periode_id']);
                    $siswaList = Siswa::all();

                    $generator = new AutoNarasiGenerator();
                    $totalGenerated = 0;

                    foreach ($siswaList as $siswa) {
                        $hasil = $generator->generatePenilaianSemester($siswa->id, $periode->id);
                        $totalGenerated += count($hasil);
                    }

                    Notification::make()
                        ->title('Auto Penilaian Generated')
                        ->body("Successfully generated {$totalGenerated} assessments for period {$periode->nama_periode}")
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Generate Auto Penilaian')
                ->modalDescription('This will generate automatic assessments for all students in the selected period based on their daily observations.')
                ->modalSubmitActionLabel('Generate'),
        ];
    }
}
