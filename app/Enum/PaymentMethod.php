<?php

namespace App\Enum;

enum PaymentMethod: string
{
    // Credit/Debit Card
    case CREDIT_CARD = 'credit_card';

        // Virtual Accounts
    case BCA_VA = 'bca_va';
    case BNI_VA = 'bni_va';
    case BRI_VA = 'bri_va';
    case MANDIRI_VA = 'mandiri_va';
    case PERMATA_VA = 'permata_va';
    case OTHER_VA = 'other_va';

        // E-Wallets
    case GOPAY = 'gopay';
    case OVO = 'ovo';
    case DANA = 'dana';
    case SHOPEEPAY = 'shopeepay';
    case LINKAJA = 'linkaja';

        // QR Code
    case QRIS = 'qris';

        // Convenience Stores
    case ALFAMART = 'alfamart';
    case INDOMARET = 'indomaret';

        // Bank Transfer
    case BANK_TRANSFER = 'bank_transfer';

        // Midtrans Specific
    case MIDTRANS_SNAP = 'Midtrans Snap';

    /**
     * Get all method values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get method label for display
     */
    public function label(): string
    {
        return match ($this) {
            self::CREDIT_CARD => 'Kartu Kredit/Debit',
            self::BCA_VA => 'BCA Virtual Account',
            self::BNI_VA => 'BNI Virtual Account',
            self::BRI_VA => 'BRI Virtual Account',
            self::MANDIRI_VA => 'Mandiri Virtual Account',
            self::PERMATA_VA => 'Permata Virtual Account',
            self::OTHER_VA => 'Virtual Account Lainnya',
            self::GOPAY => 'GoPay',
            self::OVO => 'OVO',
            self::DANA => 'DANA',
            self::SHOPEEPAY => 'ShopeePay',
            self::LINKAJA => 'LinkAja',
            self::QRIS => 'QRIS',
            self::ALFAMART => 'Alfamart',
            self::INDOMARET => 'Indomaret',
            self::BANK_TRANSFER => 'Transfer Bank',
            self::MIDTRANS_SNAP => 'Midtrans Snap',
        };
    }

    /**
     * Get method category
     */
    public function category(): string
    {
        return match ($this) {
            self::CREDIT_CARD => 'card',
            self::BCA_VA, self::BNI_VA, self::BRI_VA, self::MANDIRI_VA, self::PERMATA_VA, self::OTHER_VA => 'virtual_account',
            self::GOPAY, self::OVO, self::DANA, self::SHOPEEPAY, self::LINKAJA => 'ewallet',
            self::QRIS => 'qr_code',
            self::ALFAMART, self::INDOMARET => 'convenience_store',
            self::BANK_TRANSFER => 'bank_transfer',
            self::MIDTRANS_SNAP => 'payment_gateway',
        };
    }

    /**
     * Get method icon
     */
    public function icon(): string
    {
        return match ($this) {
            self::CREDIT_CARD => 'heroicon-o-credit-card',
            self::BCA_VA, self::BNI_VA, self::BRI_VA, self::MANDIRI_VA, self::PERMATA_VA, self::OTHER_VA => 'heroicon-o-building-library',
            self::GOPAY, self::OVO, self::DANA, self::SHOPEEPAY, self::LINKAJA => 'heroicon-o-device-phone-mobile',
            self::QRIS => 'heroicon-o-qr-code',
            self::ALFAMART, self::INDOMARET => 'heroicon-o-building-storefront',
            self::BANK_TRANSFER => 'heroicon-o-banknotes',
            self::MIDTRANS_SNAP => 'heroicon-o-globe-alt',
        };
    }

    /**
     * Get method color for UI
     */
    public function color(): string
    {
        return match ($this) {
            self::CREDIT_CARD => 'blue',
            self::BCA_VA => 'blue',
            self::BNI_VA => 'orange',
            self::BRI_VA => 'blue',
            self::MANDIRI_VA => 'yellow',
            self::PERMATA_VA => 'green',
            self::OTHER_VA => 'gray',
            self::GOPAY => 'green',
            self::OVO => 'purple',
            self::DANA => 'blue',
            self::SHOPEEPAY => 'orange',
            self::LINKAJA => 'red',
            self::QRIS => 'indigo',
            self::ALFAMART => 'red',
            self::INDOMARET => 'yellow',
            self::BANK_TRANSFER => 'gray',
            self::MIDTRANS_SNAP => 'blue',
        };
    }

    /**
     * Check if method requires real-time payment
     */
    public function isRealTime(): bool
    {
        return in_array($this, [
            self::CREDIT_CARD,
            self::GOPAY,
            self::OVO,
            self::DANA,
            self::SHOPEEPAY,
            self::LINKAJA,
            self::QRIS,
        ]);
    }

    /**
     * Check if method is virtual account
     */
    public function isVirtualAccount(): bool
    {
        return $this->category() === 'virtual_account';
    }

    /**
     * Check if method is e-wallet
     */
    public function isEwallet(): bool
    {
        return $this->category() === 'ewallet';
    }

    /**
     * Get processing time description
     */
    public function processingTime(): string
    {
        return match ($this) {
            self::CREDIT_CARD, self::GOPAY, self::OVO, self::DANA, self::SHOPEEPAY, self::LINKAJA, self::QRIS => 'Instan',
            self::BCA_VA, self::BNI_VA, self::BRI_VA, self::MANDIRI_VA, self::PERMATA_VA, self::OTHER_VA => 'Real-time',
            self::ALFAMART, self::INDOMARET => '15-30 menit',
            self::BANK_TRANSFER => '1-3 jam kerja',
            self::MIDTRANS_SNAP => 'Bervariasi',
        };
    }
}
