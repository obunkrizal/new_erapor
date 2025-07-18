<div>
    <h2>Informasi Nilai</h2>
    <p>Detail penilaian siswa:</p>
    <ul>
        <li><strong>Nama Siswa:</strong> {{ $record->siswa->nama_lengkap ?? 'N/A' }}</li>
        <li><strong>Kelas:</strong> {{ $record->kelas->nama_kelas ?? 'N/A' }}</li>
        <li><strong>Guru:</strong> {{ $record->guru->nama_guru ?? 'N/A' }}</li>
        <li><strong>Periode:</strong> {{ $record->periode->nama_periode ?? 'N/A' }}</li>
        <li><strong>Nilai Agama:</strong> {{ $record->nilai_agama ?? 'Belum Ada Nilai' }}</li>
        <li><strong>Nilai Jati Diri:</strong> {{ $record->nilai_jatiDiri ?? 'Belum Ada Nilai' }}</li>
        <li><strong>Nilai Literasi:</strong> {{ $record->nilai_literasi ?? 'Belum Ada Nilai' }}</li>
        <li><strong>Nilai Narasi:</strong> {{ $record->nilai_narasi ?? 'Belum Ada Nilai' }}</li>
        <li><strong>Refleksi Guru:</strong> {{ $record->refleksi_guru ?? 'Belum Ada Nilai' }}</li>
    </ul>
</div>
