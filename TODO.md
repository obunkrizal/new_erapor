# TODO: Fix SQL Column Error 'gurus.id'

## Status: COMPLETED ✅

### Problem
- SQLSTATE[42S22]: Column not found: 1054 Unknown column 'gurus.id' in 'where clause'
- Error occurred during Laravel validation when creating/updating guru records
- The unique validation rule was incorrectly trying to reference 'gurus.id' instead of 'users.id'

### Root Cause
- In `app/Filament/Resources/Gurus/GuruResource.php`, the unique validation rules for email fields were using the old syntax `->unique('users', 'email')`
- Laravel's validation system was trying to ignore the current record during updates, but since the form is for Guru model, it was incorrectly referencing 'gurus.id' instead of the related 'users.id'

### Solution Applied
- [x] Updated first unique validation rule (line ~129): Changed `->unique('users', 'email')` to `->unique(table: 'users', column: 'email', ignoreRecord: false)`
- [x] Updated second unique validation rule (line ~168): Changed `->unique('users', 'email')` to `->unique(table: 'users', column: 'email', ignoreRecord: false)`
- [x] Set `ignoreRecord: false` since these are for creating new user accounts, not updating existing ones

### Files Modified
- `app/Filament/Resources/Gurus/GuruResource.php`

### Testing
- The validation should now work correctly without the SQL column error
- Email uniqueness will be properly validated against the users table
