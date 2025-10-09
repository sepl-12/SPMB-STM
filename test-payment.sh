#!/bin/bash

# Payment Gateway Testing Script
# This script helps test the Midtrans payment gateway integration

echo "================================================"
echo "🚀 PPDB Payment Gateway - Testing Script"
echo "================================================"
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if Laravel is running
echo "📋 Step 1: Checking Laravel application..."
if pgrep -f "php artisan serve" > /dev/null; then
    echo -e "${GREEN}✓${NC} Laravel is running"
else
    echo -e "${YELLOW}⚠${NC} Laravel is not running. Starting Laravel..."
    php artisan serve &
    sleep 3
    echo -e "${GREEN}✓${NC} Laravel started"
fi
echo ""

# Test Midtrans connection
echo "📋 Step 2: Testing Midtrans connection..."
php artisan midtrans:test
MIDTRANS_TEST_RESULT=$?
echo ""

if [ $MIDTRANS_TEST_RESULT -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Midtrans connection successful!"
else
    echo -e "${RED}✗${NC} Midtrans connection failed. Please check your configuration."
    exit 1
fi
echo ""

# Check database
echo "📋 Step 3: Checking database..."
php artisan db:show 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Database connection OK"
else
    echo -e "${YELLOW}⚠${NC} Could not verify database"
fi
echo ""

# List available routes
echo "📋 Step 4: Payment routes available:"
php artisan route:list --path=pembayaran 2>/dev/null || php artisan route:list | grep pembayaran
echo ""

# Check if there are applicants
echo "📋 Step 5: Checking for applicants..."
APPLICANT_COUNT=$(php artisan tinker --execute="echo App\Models\Applicant::count();" 2>/dev/null)
echo "Found $APPLICANT_COUNT applicant(s) in database"
echo ""

if [ "$APPLICANT_COUNT" -gt 0 ]; then
    # Get first applicant
    echo "📋 Step 6: Getting test applicant data..."
    php artisan tinker --execute="
        \$applicant = App\Models\Applicant::with('wave')->first();
        if (\$applicant) {
            echo 'Registration Number: ' . \$applicant->registration_number . PHP_EOL;
            echo 'Name: ' . \$applicant->applicant_full_name . PHP_EOL;
            echo 'Fee: Rp ' . number_format(\$applicant->wave->registration_fee_amount, 0, ',', '.') . PHP_EOL;
            echo 'Payment URL: ' . route('payment.show', \$applicant->registration_number) . PHP_EOL;
        }
    " 2>/dev/null
    echo ""
else
    echo -e "${YELLOW}⚠${NC} No applicants found. Please create an applicant first."
    echo "Run: php artisan db:seed --class=ApplicantSeeder"
    echo ""
fi

# Summary
echo "================================================"
echo "📊 Testing Summary"
echo "================================================"
echo ""
echo "✅ Available endpoints:"
echo "   - Registration: http://localhost:8000/daftar"
echo "   - Payment: http://localhost:8000/pembayaran/{registration_number}"
echo "   - Status: http://localhost:8000/pembayaran/status/{registration_number}"
echo ""
echo "🔧 Testing tools:"
echo "   - Test Midtrans: php artisan midtrans:test"
echo "   - View logs: tail -f storage/logs/laravel.log"
echo "   - Clear cache: php artisan optimize:clear"
echo ""
echo "📚 Documentation:"
echo "   - Complete guide: docs/PAYMENT_GATEWAY_MIDTRANS.md"
echo "   - Quick guide: docs/PAYMENT_QUICK_GUIDE.md"
echo "   - Flow diagram: docs/PAYMENT_FLOW_DIAGRAM.md"
echo ""
echo "💳 Test credentials (Sandbox):"
echo "   - Card: 4811 1111 1111 1114"
echo "   - CVV: 123"
echo "   - Exp: 01/25"
echo "   - OTP: 112233"
echo ""
echo "================================================"
echo "🎉 Ready to test!"
echo "================================================"
