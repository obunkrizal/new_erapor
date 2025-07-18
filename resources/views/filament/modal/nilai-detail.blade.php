<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="font-semibold text-gray-900 mb-2">Informasi Siswa</h3>
            <p><strong>Nama:</strong> {{ $record->siswa->nama_lengkap }}</p>
            <p><strong>NIS:</strong> {{ $record->siswa->nis }}</p>
            <p><strong>NISN:</strong> {{ $record->siswa->nisn }}</p>
            <p><strong>Kelas:</strong> {{ $record->kelas->nama_kelas }}</p>
        </div>

        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="font-semibold text-gray-900 mb-2">Informasi Guru
