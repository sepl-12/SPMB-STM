# Troubleshooting Documentation

> Common issues, bug fixes & solutions.

---

## 📚 Documentation Files

### **Export Issues**
1. **[FIX_EXPORT_UNDEFINED_RELATIONSHIP.md](FIX_EXPORT_UNDEFINED_RELATIONSHIP.md)** (4.0K)
   - Relationship errors in export
   - Fix implementation

### **Wave Management**
2. **[FIX_WAVE_STATUS_PRIORITY.md](FIX_WAVE_STATUS_PRIORITY.md)** (6.7K)
   - Wave status priority issues
   - Resolution steps

### **UI/Layout**
3. **[FIX_APPLICANT_VIEW_LAYOUT.md](FIX_APPLICANT_VIEW_LAYOUT.md)** (9.0K)
   - Applicant view layout fixes
   - UI improvements

4. **[FIX_TIMELINE_ICON_ERROR.md](FIX_TIMELINE_ICON_ERROR.md)** (5.3K)
   - Timeline icon rendering issues

5. **[IMPROVEMENT_ANSWER_LAYOUT_V2.md](IMPROVEMENT_ANSWER_LAYOUT_V2.md)** (10K)
   - Answer display improvements
   - Layout enhancements

### **File Upload**
6. **[TROUBLESHOOTING_IMAGE_UPLOAD.md](TROUBLESHOOTING_IMAGE_UPLOAD.md)** (4.6K)
   - Image upload problems
   - Common solutions
   - Permissions & storage issues

7. **[FIX_LIVEWIRE_UPLOAD_LOADING.md](FIX_LIVEWIRE_UPLOAD_LOADING.md)** (7.5K)
   - Livewire upload loading indicators
   - Progress bar fixes

---

## 🆘 Common Issues

### **Payment Not Updating**
**Symptoms:** Payment status stuck on "pending"  
**Solution:** Check webhook URL configuration in Midtrans dashboard  
**See:** [../payment/PAYMENT_GATEWAY_MIDTRANS.md](../payment/PAYMENT_GATEWAY_MIDTRANS.md)

### **File Upload Fails**
**Symptoms:** Upload returns error or file not saved  
**Solution:** Check storage permissions & disk space  
**See:** [TROUBLESHOOTING_IMAGE_UPLOAD.md](TROUBLESHOOTING_IMAGE_UPLOAD.md)

### **Form Not Showing**
**Symptoms:** Public form shows "No active wave"  
**Solution:** Activate a wave in admin panel  
**See:** [../features/DYNAMIC_WAVE_COMPONENT.md](../features/DYNAMIC_WAVE_COMPONENT.md)

### **Export Fails**
**Symptoms:** Export returns error or empty file  
**Solution:** Check relationship loading & memory limits  
**See:** [FIX_EXPORT_UNDEFINED_RELATIONSHIP.md](FIX_EXPORT_UNDEFINED_RELATIONSHIP.md)

---

## 🔗 Related Documentation

- [Main Documentation Index](../INDEX.md)
- [Developer Guide](../../DEVELOPER_GUIDE.md)

---

**Last Updated:** 2025-01-13
