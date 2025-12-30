# Fixes Applied to ObservasiHarianForm

## Issues Fixed:
1. **Table Name Correction**: Ensured both `getAvailableSiswaOptions` and `searchAvailableSiswa` methods use `->from('kelas_siswas')` to match the actual database table name.

2. **Query Logic Correction**: Changed from `whereNotIn` to `whereIn` in both methods to select siswa that ARE in the selected kelas and periode, instead of those not in any kelas.

3. **Duplicate Query Condition**: Removed duplicate `->orWhere('nama_lengkap', 'like', "%{$search}%")` in the search query.

4. **Simplified Name Fallback**: Changed `$name = $siswa->nama_lengkap ?? $siswa->nama_lengkap ?? 'N/A';` to `$name = $siswa->nama_lengkap ?? 'N/A';` to remove redundancy.

## Previous Errors Resolved:
- The "Undefined variable $rentangUsia" error was likely from an older version of the code. The current code properly defines `$rentangUsia` within the closure for indikator_id options.
- The "Table 'my_filament.kelas_siswa' doesn't exist" error is now fixed by using the correct table name 'kelas_siswas'.
- The "not load siswa" issue is now fixed by correcting the query logic to select siswa in the kelas instead of those not in any kelas.

## Notes:
- The class contains methods typically found in Resource classes (like `getEloquentQuery`), which may indicate this file is misnamed or misplaced, but the form schema itself appears functional after fixes.
- No syntax errors remain in the file.
