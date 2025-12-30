# Fixes Applied to KelasSiswaResource

## Issues Fixed:
1. **Table Name Correction**: Changed `->from('kelas_siswa')` to `->from('kelas_siswas')` in `getAvailableSiswaOptions` method to match the actual database table name.

2. **Duplicate Search Condition**: Removed duplicate `->orWhere('nama_lengkap', 'like', "%{$search}%")` in `searchAvailableSiswa` method.

3. **Show All Students**: Modified `getAvailableSiswaOptions` to show all students from the `siswa` table instead of excluding those already enrolled in active classes, allowing selection of any student for multiple enrollment.

## Problem Solved:
- The "multiple siswa not shown" issue was caused by incorrect table name in the query that determines which students are available for selection. This prevented the multiple selection dropdown from showing available students.
- Now all students from the siswa table are shown, allowing administrators to select and enroll multiple students at once, even if they are already enrolled in other classes.

## Changes Made:
- `app/Filament/Resources/KelasSiswas/KelasSiswaResource.php`:
  - Line ~962: Changed `->from('kelas_siswa')` to `->from('kelas_siswas')`
  - Line ~964-976: Removed the `whereNotIn` clause that excluded enrolled students
  - Line ~994: Removed duplicate nama_lengkap search condition
  - Line ~981: Simplified name fallback from `$siswa->nama_lengkap ?? $siswa->nama_lengkap ?? 'N/A'` to `$siswa->nama_lengkap ?? 'N/A'`

## Testing Required:
- Test the create form with multiple selection enabled
- Verify that all students from the siswa table appear in the dropdown
- Confirm that students can be selected and enrolled even if already in other classes
- Test that the unique constraint prevents duplicate enrollment in the same class/period combination
