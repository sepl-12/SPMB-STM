# System Fields Protection Guide

## 📖 Overview

Sistem proteksi untuk field-field penting yang terhubung langsung ke kolom `applicants` table.

---

## 🎯 Problem Statement

### **Sebelum Proteksi:**

```
Applicants Table (Fixed Columns):
├─ applicant_full_name      
├─ applicant_nisn           
├─ applicant_phone_number   
├─ applicant_email_address  
└─ chosen_major_name        

RegistrationController (Hardcoded Mapping):
$applicant = Applicant::create([
    'applicant_full_name' => $data['nama_lengkap'],  ← Hardcoded!
    'applicant_nisn' => $data['nisn'],                ← Hardcoded!
    ...
]);

❌ MASALAH:
1. Admin ubah field_key → Data jadi NULL
2. Admin hapus field → Registrasi error
3. Admin ubah field_type → Data inconsistent
4. Tidak ada warning di UI
```

---

## ✅ Solution: System Fields Protection

### **Proteksi yang Diterapkan:**

```
System Fields (is_system_field = true):
├─ nama_lengkap / full_name
├─ nisn
├─ no_hp / phone / telepon
├─ email / email_address
└─ jurusan / major / program_studi

🔒 RESTRICTIONS:
1. ❌ field_key TIDAK dapat diubah
2. ❌ field_type TIDAK dapat diubah
3. ❌ TIDAK dapat diarsipkan
4. ❌ TIDAK dapat dihapus
5. ✅ field_label BOLEH diubah (customization)
```

---

## 🔧 Implementation Details

### **1. Database Level**

**Migration:** `add_is_system_field_to_form_fields_table`

```sql
ALTER TABLE form_fields 
ADD COLUMN is_system_field BOOLEAN DEFAULT FALSE;

UPDATE form_fields 
SET is_system_field = TRUE 
WHERE field_key IN ('nama_lengkap', 'nisn', 'email', ...);
```

---

### **2. Model Level Protection**

**File:** `app/Models/FormField.php`

```php
protected static function booted(): void
{
    // Prevent changing critical properties
    static::updating(function (FormField $field) {
        if ($field->is_system_field) {
            if ($field->isDirty('field_key')) {
                throw new \Exception('Cannot change field_key of system field!');
            }
            
            if ($field->isDirty('field_type')) {
                throw new \Exception('Cannot change field_type of system field!');
            }
            
            if ($field->isDirty('is_archived') && $field->is_archived) {
                throw new \Exception('Cannot archive system field!');
            }
        }
    });
    
    // Prevent deletion
    static::deleting(function (FormField $field) {
        if ($field->is_system_field) {
            throw new \Exception('Cannot delete system field!');
        }
    });
}
```

---

### **3. UI Level Protection**

**File:** `app/Filament/Resources/FormResource/RelationManagers/FormFieldsRelationManager.php`

#### **A. Warning Banner**

```
⚠️ SYSTEM REQUIRED FIELD
Field ini terhubung ke kolom `applicants` table.
field_key dan field_type tidak dapat diubah.
Label boleh diubah untuk customization.
```

#### **B. Disabled Inputs**

```php
TextInput::make('field_key')
    ->disabled()  // Always disabled
    ->helperText(fn ($record) => $record?->is_system_field 
        ? '🔒 LOCKED - Field ini terhubung ke applicants table'
        : 'Otomatis dibuat dari label'
    )

Select::make('field_type')
    ->disabled(fn ($record) => $record?->is_system_field)
    ->helperText(fn ($record) => $record?->is_system_field 
        ? '🔒 LOCKED - Tipe tidak dapat diubah'
        : 'Pilih tipe yang sesuai'
    )

Toggle::make('is_archived')
    ->disabled(fn ($record) => $record?->is_system_field)
    ->helperText(fn ($record) => $record?->is_system_field 
        ? '🔒 System field tidak dapat diarsipkan'
        : 'Arsipkan jika tidak digunakan'
    )
```

#### **C. Hidden Actions**

```php
Action::make('toggleArchive')
    ->hidden(fn (FormField $record) => $record->is_system_field)

Tables\Actions\DeleteAction::make()
    ->hidden(fn (FormField $record) => $record->is_system_field)
```

#### **D. Table Badge**

```php
TextColumn::make('field_label')
    ->badge(fn (FormField $record) => $record->is_system_field)
    ->color(fn (FormField $record) => $record->is_system_field ? 'warning' : null)
    ->icon(fn (FormField $record) => $record->is_system_field ? 'heroicon-o-lock-closed' : null)
```

---

## 📋 System Fields List

### **Current System Fields:**

| field_key | Mapped To Applicant Column | Type | Purpose |
|-----------|---------------------------|------|---------|
| **nama_lengkap** | `applicant_full_name` | text | Nama lengkap pendaftar |
| **full_name** | `applicant_full_name` | text | Alternative key untuk nama |
| **nisn** | `applicant_nisn` | text | Nomor Induk Siswa Nasional |
| **no_hp** | `applicant_phone_number` | tel | Nomor HP |
| **phone** | `applicant_phone_number` | tel | Alternative untuk no HP |
| **telepon** | `applicant_phone_number` | tel | Alternative untuk no HP |
| **email** | `applicant_email_address` | email | Email pendaftar |
| **email_address** | `applicant_email_address` | email | Alternative untuk email |
| **jurusan** | `chosen_major_name` | select | Jurusan pilihan |
| **major** | `chosen_major_name` | select | Alternative untuk jurusan |
| **program_studi** | `chosen_major_name` | select | Alternative untuk jurusan |

---

## 🧪 Testing Guide

### **Test 1: Attempt to Change field_key**

**Steps:**
1. Login admin → Form Management
2. Edit system field (e.g., "nama_lengkap")
3. Try to change field_key

**✅ Expected:**
- field_key input is DISABLED
- Helper text shows: "🔒 LOCKED - Field ini terhubung ke applicants table"

---

### **Test 2: Attempt to Change field_type**

**Steps:**
1. Edit system field
2. Try to change field_type from "text" to "textarea"

**✅ Expected:**
- field_type select is DISABLED
- Helper text shows: "🔒 LOCKED - Tipe tidak dapat diubah"

---

### **Test 3: Attempt to Archive**

**Steps:**
1. Edit system field
2. Try to enable "Arsipkan Pertanyaan"

**✅ Expected:**
- Toggle is DISABLED
- Helper text shows: "🔒 System field tidak dapat diarsipkan"

---

### **Test 4: Attempt to Delete**

**Steps:**
1. View system field in table
2. Look for delete action

**✅ Expected:**
- Delete action is HIDDEN
- Archive action is HIDDEN

---

### **Test 5: Label Can Be Changed**

**Steps:**
1. Edit system field
2. Change "Nama Lengkap" → "Nama Lengkap Sesuai KTP"
3. Save

**✅ Expected:**
- Save successful ✅
- Label updated in form
- field_key remains unchanged
- Helper text shows: "✅ Label boleh diubah untuk customization"

---

### **Test 6: Registation Still Works**

**Steps:**
1. User fills registration form
2. Submit

**✅ Expected:**
- Registration successful
- Applicant created with correct data mapping
- No errors

---

## 🔍 Verification Commands

### **Check System Fields:**

```bash
php artisan tinker --execute="
\$systemFields = App\Models\FormField::where('is_system_field', true)->get();
dump(\$systemFields->pluck('field_label', 'field_key'));
"
```

### **Test Protection (Should Fail):**

```bash
php artisan tinker --execute="
\$field = App\Models\FormField::where('is_system_field', true)->first();
try {
    \$field->update(['field_key' => 'test_change']);
    echo '❌ PROTECTION FAILED - field_key was changed!';
} catch (\Exception \$e) {
    echo '✅ PROTECTION WORKS: ' . \$e->getMessage();
}
"
```

**Expected Output:**
```
✅ PROTECTION WORKS: Cannot change field_key of system field! This field is mapped to applicants table.
```

---

## ⚙️ Configuration

### **Add New System Field:**

If you need to add a new system field mapping:

**1. Update Migration:**

Edit `database/migrations/2025_10_13_174310_add_is_system_field_to_form_fields_table.php`

```php
$systemFields = [
    'nama_lengkap',
    // ... existing fields
    'new_field_key',  // ← Add here
];
```

**2. Update RegistrationController:**

```php
$applicant = Applicant::create([
    // ... existing mappings
    'new_applicant_column' => $registrationData['new_field_key'] ?? 'default',
]);
```

**3. Re-run Migration:**

```bash
php artisan migrate:fresh --seed
```

---

## 🚨 Emergency Procedures

### **If System Field Accidentally Deleted:**

**1. Check Soft Deletes:**
```bash
php artisan tinker --execute="
\$deleted = App\Models\FormField::onlyTrashed()->where('field_key', 'nama_lengkap')->first();
if (\$deleted) {
    \$deleted->restore();
    echo 'Field restored!';
}
"
```

**2. Recreate from Seed:**
```bash
php artisan db:seed --class=FormSeeder
```

---

### **If Protection Needs to be Temporarily Disabled:**

⚠️ **NOT RECOMMENDED** - Only for emergency fixes!

```php
// In FormField model, comment out protection:
protected static function booted(): void
{
    // static::updating(...); // ← Comment this out
    // static::deleting(...); // ← Comment this out
}
```

**After fix, re-enable immediately!**

---

## 📊 Impact Analysis

### **Before Protection:**

| Risk | Severity | Frequency |
|------|----------|-----------|
| Admin changes field_key | 🔴 Critical | Medium |
| Admin deletes field | 🔴 Critical | Low |
| Admin changes field_type | 🟡 High | Medium |
| Data inconsistency | 🟡 High | High |

### **After Protection:**

| Risk | Severity | Frequency |
|------|----------|-----------|
| All above risks | ✅ Eliminated | N/A |
| Admin confused why locked | 🟢 Low | Low (clear warning) |

---

## 🎓 Best Practices

### **For Developers:**

1. ✅ Always check `is_system_field` before programmatic changes
2. ✅ Use fallback values in RegistrationController mapping
3. ✅ Test system field protection after deployment
4. ✅ Document any new system field mappings

### **For Admins:**

1. ✅ System fields have 🔒 lock icon - don't try to change
2. ✅ Only labels can be customized
3. ✅ Contact developer if need to add new system field
4. ✅ Regular fields can be freely edited

---

## 🔄 Migration Path

### **Existing Projects:**

If you're adding this to an existing project:

1. **Backup database first!**
   ```bash
   mysqldump -u root -p spmb_stm > backup.sql
   ```

2. **Run migration:**
   ```bash
   php artisan migrate
   ```

3. **Verify system fields marked:**
   ```bash
   php artisan tinker --execute="
   echo 'System Fields: ' . App\Models\FormField::where('is_system_field', true)->count();
   "
   ```

4. **Test in staging before production!**

---

## ❓ FAQ

### **Q: Can I change the label of system fields?**
**A:** ✅ Yes! Labels can be freely changed for customization.

### **Q: What happens if I try to delete a system field?**
**A:** ❌ You'll get an error: "Cannot delete system field! This field is required for applicant data."

### **Q: Can I add new system fields?**
**A:** Yes, but requires code changes. Contact developer to add mapping in RegistrationController.

### **Q: Why can't I archive system fields?**
**A:** System fields are required for applicant registration to work. Archiving them would break the form.

### **Q: What if the label I want to use is very different from the original?**
**A:** No problem! You can change labels freely. The system uses field_key for mapping, not labels.

---

**Status:** ✅ Active  
**Version:** 1.0  
**Last Updated:** 2025-01-13
