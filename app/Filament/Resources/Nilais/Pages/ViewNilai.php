<?php

namespace App\Filament\Resources\Nilais\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Nilais\NilaiResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewNilai extends ViewRecord
{
    protected static string $resource = NilaiResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        // Always show print action
        // $actions[] = Actions\Action::make('print')
        //     ->label('Print Rapor')
        //     ->icon('heroicon-o-printer')
        //     ->color('success')
        //     ->url(fn (): string => route('nilai.print', ['nilai' => $this->record]))
        //     ->openUrlInNewTab();

        $actions[] = Action::make('select_date')
            ->label('Pilih Tanggal Tanda Tangan')
            ->icon('heroicon-o-calendar')
            ->color('primary')
            ->schema([
                DatePicker::make('signature_date')
                    ->label('Tanggal Tanda Tangan')
                    ->native(false)
                    ->required()
                    ->default(now()->format('Y-m-d')),
            ])
            ->modalHeading('Pilih Tanggal Tanda Tangan')
            ->modalSubmitActionLabel('Cetak')
            ->action(function (array $data) {
                $url = route('nilai.print', ['nilai' => $this->record, 'date' => $data['signature_date']]);
                return redirect()->to($url);
            });

        // Only show edit/delete if not admin
        if (!Auth::user()?->isAdmin()) {
            $actions[] = EditAction::make();
            $actions[] = DeleteAction::make();
        }

        // Show info action for admin
        if (Auth::user()?->isAdmin()) {
            $actions[] = Action::make('info')
                ->label('Info')
                ->icon('heroicon-o-information-circle')
                ->color('info')
                ->modalHeading('Informasi Nilai')
                ->modalContent(view('filament.pages.nilai-info', ['record' => $this->record]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup');
        }

        return $actions;
    }
}
