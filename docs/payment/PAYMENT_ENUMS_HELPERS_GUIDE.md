# Payment Enums & Helpers Documentation

## Overview

Untuk membuat kode lebih rapi dan mudah dibaca, telah dibuat beberapa Enum dan Helper class untuk mengelola data-data pembayaran. Sistem ini disederhanakan karena hanya menggunakan **Midtrans** sebagai payment gateway dan **IDR (Rupiah)** sebagai mata uang.

## File-File yang Dibuat

### 1. Enums

#### `App\Enum\PaymentStatus`
Mengelola status pembayaran dengan berbagai method utility:

```php
use App\Enum\PaymentStatus;

// Penggunaan basic
$status = PaymentStatus::PENDING;
$status = PaymentStatus::PAID;
$status = PaymentStatus::FAILED;

// Method utility
$status->label();           // "Menunggu Pembayaran"
$status->color();           // "warning" 
$status->icon();            // "heroicon-o-clock"
$status->isSuccess();       // false
$status->isPending();       // true
$status->getSimplifiedStatus(); // "unpaid"

// Static methods
PaymentStatus::values();    // Array semua nilai
```

#### `App\Enum\PaymentMethod`
Mengelola metode pembayaran:

```php
use App\Enum\PaymentMethod;

$method = PaymentMethod::BCA_VA;
$method->label();           // "BCA Virtual Account"
$method->category();        // "virtual_account"
$method->isVirtualAccount(); // true
$method->isRealTime();      // false
$method->processingTime();  // "Real-time"
```

### 2. Helper Class

#### `App\Helpers\PaymentHelper`
Class utility untuk operasi pembayaran:

```php
use App\Helpers\PaymentHelper;

// Format currency (IDR only)
PaymentHelper::formatIDR(50000);        // "Rp 50.000"
PaymentHelper::formatCurrency(50000);   // "Rp 50.000" (alias)

// Generate IDs
PaymentHelper::generateOrderId('ORD', 'REG123'); // "ORD-REG123-1633024800-1234"
PaymentHelper::generatePaymentReference('APP123'); // "PAY-APP123-20241010120000-123"

// Status utilities
PaymentHelper::getStatusOptions();      // Array untuk dropdown
PaymentHelper::getStatusBadge($status); // Badge data
PaymentHelper::mapMidtransStatus('settlement'); // PaymentStatus::SETTLEMENT

// Payment instructions
PaymentHelper::getPaymentInstructions(PaymentMethod::BCA_VA); // Array instruksi
```

### 3. Trait

#### `App\Traits\HasPaymentAttributes`
Trait untuk model yang memiliki atribut pembayaran:

```php
use App\Traits\HasPaymentAttributes;

class Payment extends Model
{
    use HasPaymentAttributes;
    
    // Otomatis mendapat method:
    // - getFormattedAmountAttribute() (format IDR)
    // - getStatusBadgeAttribute()
    // - getMethodLabelAttribute()
    // - getGatewayLabelAttribute() (selalu "Midtrans Payment Gateway")
    // - isPaymentSuccessful()
    // - isPaymentPending()
    // - isPaymentFailed()
}
```

## Implementasi di Model Payment

Model Payment telah diupdate untuk menggunakan Enum casting dan method-method baru:

```php
// Casting otomatis (simplified)
protected $casts = [
    'payment_status_name' => PaymentStatus::class,
    'payment_method_name' => PaymentMethod::class,
    // payment_gateway_name dan currency_code menggunakan string biasa
    // karena selalu 'Midtrans' dan 'IDR'
];

// Scope baru
Payment::withStatus(PaymentStatus::PAID)->get();
Payment::successful()->get();
Payment::pending()->get();
Payment::failed()->get();
Payment::today()->get();
Payment::thisMonth()->get();

// Accessor attributes
$payment->formatted_amount;     // "Rp 50.000"
$payment->status_badge;         // Array badge data
$payment->method_label;         // "BCA Virtual Account"
$payment->gateway_label;        // "Midtrans Payment Gateway" (selalu sama)
$payment->processing_time;      // "Real-time"
$payment->payment_instructions; // Array instruksi
```

## Implementasi di MidtransService

Service telah diupdate untuk menggunakan Enum (simplified):

```php
// Membuat payment record
Payment::create([
    'payment_gateway_name' => 'Midtrans', // String biasa
    'payment_method_name' => PaymentMethod::MIDTRANS_SNAP,
    'payment_status_name' => PaymentStatus::PENDING,
    'currency_code' => 'IDR', // String biasa
    // ...
]);

// Mapping status dari Midtrans
$status = PaymentHelper::mapMidtransStatus($transactionStatus, $fraudStatus);

// Update status
$payment->update([
    'payment_status_name' => $status,
    'payment_method_name' => $this->mapPaymentMethod($paymentType),
]);

// Check status
if ($status->isSuccess()) {
    // Payment berhasil
} elseif ($status->isFailed()) {
    // Payment gagal
}
```

## Penggunaan di Filament Resources

```php
// Di PaymentResource.php
BadgeColumn::make('payment_status_name')
    ->formatStateUsing(fn($state) => $state->label())
    ->color(fn($state) => $state->color()),

TextColumn::make('payment_method_name')
    ->formatStateUsing(fn($state) => $state->label()),

TextColumn::make('payment_gateway_name')
    ->label('Gateway')
    ->default('Midtrans'), // Selalu Midtrans

SelectFilter::make('payment_status_name')
    ->options(PaymentHelper::getStatusOptions()),
```

## Penggunaan di Blade Templates

```blade
{{-- Status badge --}}
<span class="badge {{ $payment->status_badge['css_class'] }}">
    <i class="{{ $payment->status_badge['icon'] }}"></i>
    {{ $payment->status_badge['label'] }}
</span>

{{-- Formatted amount (IDR) --}}
<p>Total: {{ $payment->formatted_amount }}</p>

{{-- Gateway label (selalu Midtrans) --}}
<p>Gateway: {{ $payment->gateway_label }}</p>

{{-- Payment instructions --}}
@if($payment->payment_instructions)
    <div class="instructions">
        <h4>{{ $payment->payment_instructions['title'] }}</h4>
        <ol>
            @foreach($payment->payment_instructions['steps'] as $step)
                <li>{{ $step }}</li>
            @endforeach
        </ol>
    </div>
@endif
```

## Perubahan dari Versi Sebelumnya

### Dihapus:
- ❌ `App\Enum\CurrencyCode` - Tidak diperlukan karena hanya menggunakan IDR
- ❌ `App\Enum\PaymentGateway` - Tidak diperlukan karena hanya menggunakan Midtrans

### Disederhanakan:
- ✅ `payment_gateway_name` sekarang menggunakan string 'Midtrans'
- ✅ `currency_code` sekarang menggunakan string 'IDR'
- ✅ Helper methods disesuaikan untuk IDR only
- ✅ Trait methods disesuaikan untuk single gateway

## Keuntungan Implementasi

1. **Simplified Architecture**: Tidak ada over-engineering karena disesuaikan dengan kebutuhan aktual
2. **Type Safety**: Enum memberikan type safety untuk field yang benar-benar bervariasi
3. **Readability**: Kode lebih mudah dibaca dan dipahami
4. **Maintainability**: Mudah di-maintain karena tidak kompleks
5. **Performance**: Lebih ringan karena tidak ada enum yang tidak diperlukan
6. **Consistency**: Data tetap konsisten dengan validation yang tepat

## Testing

```php
// Test dengan Enum (simplified)
$this->assertEquals(PaymentStatus::PAID, $payment->payment_status_name);
$this->assertTrue($payment->isSuccess());
$this->assertStringContains('Rp', $payment->formatted_amount);
$this->assertEquals('Midtrans', $payment->payment_gateway_name);
$this->assertEquals('IDR', $payment->currency_code);

// Test helper
$this->assertEquals('Rp 50.000', PaymentHelper::formatIDR(50000));
$this->assertTrue(PaymentHelper::validateAmount(50000));
```

Dengan implementasi yang disederhanakan ini, kode menjadi lebih efisien dan sesuai dengan kebutuhan aktual sistem yang hanya menggunakan Midtrans dan IDR! 🚀
