<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Exports\SiswaImportTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class GenerateSiswaImportTemplate extends Command
{
    protected $signature = 'generate:siswa-import-template';

    protected $description = 'Generate the styled Excel import template for Siswa';

    public function handle()
    {
        $filePath = public_path('templates/siswa_import_template.xlsx');

        Excel::store(new SiswaImportTemplateExport, 'siswa_import_template.xlsx', 'public');

        $this->info("Siswa import template generated at: {$filePath}");

        return 0;
    }
}
