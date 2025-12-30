# Guru Assignment by Kelas in Observasi

## Changes Made:
1. **Updated PaudObservasiHarian seeder**: Modified to get guru_id from kelas instead of hardcoding to 1, with fallback to 1 if kelas has no guru.

2. **Enhanced ObservasiHarianForm**: 
   - Auto-set guru_id when kelas_id is selected, with fallback to first available guru if kelas has no guru assigned
   - Disabled guru_id field once it's set to prevent manual changes
   - Updated helper text to indicate automatic assignment

## Technical Details:
- Guru assignment now follows: Kelas.guru_id -> Default Guru (first in table) -> Fallback to ID 1
- Form ensures guru is always assigned when kelas is selected
- Field becomes read-only after assignment to maintain data integrity

## Testing:
- Verify that selecting a kelas automatically populates the guru field
- Ensure fallback works when kelas has no assigned guru
- Confirm field is disabled after assignment
