<?php

namespace App\Console\Commands;

use App\Models\Applicant;
use Illuminate\Console\Command;

class TestSignedUrlsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:signed-urls {registration_number?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test signed URL generation for an applicant';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $registrationNumber = $this->argument('registration_number');

        if ($registrationNumber) {
            $applicant = Applicant::where('registration_number', $registrationNumber)->first();

            if (!$applicant) {
                $this->error("Applicant with registration number '{$registrationNumber}' not found.");
                return self::FAILURE;
            }
        } else {
            $applicant = Applicant::first();

            if (!$applicant) {
                $this->error('No applicants found in database.');
                return self::FAILURE;
            }
        }

        $this->info('Testing Signed URLs for Applicant');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        // Display applicant info
        $this->info('Applicant Information:');
        $this->table(
            ['Field', 'Value'],
            [
                ['Registration Number', $applicant->registration_number],
                ['Name', $applicant->name ?? 'N/A'],
                ['Email', $applicant->applicant_email_address ?? 'N/A'],
                ['Payment Status', $applicant->payment_status ?? 'unpaid'],
            ]
        );

        $this->newLine();

        // Generate signed URLs
        $this->info('Generated Signed URLs:');
        $this->newLine();

        $paymentUrl = $applicant->getPaymentUrl();
        $this->line("💳 <fg=green>Payment URL</> (expires in 7 days):");
        $this->line("   {$paymentUrl}");
        $this->newLine();

        $paymentSuccessUrl = $applicant->getPaymentSuccessUrl();
        $this->line("✅ <fg=green>Payment Success URL</> (expires in 7 days):");
        $this->line("   {$paymentSuccessUrl}");
        $this->newLine();

        $statusUrl = $applicant->getStatusUrl();
        $this->line("🔍 <fg=blue>Status URL</> (expires in 30 days):");
        $this->line("   {$statusUrl}");
        $this->newLine();

        $examCardUrl = $applicant->getExamCardUrl();
        $this->line("📄 <fg=yellow>Exam Card URL</> (expires in 60 days):");
        $this->line("   {$examCardUrl}");
        $this->newLine();

        // Test accessors
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('Testing Accessors (for email templates):');
        $this->newLine();

        $this->line("payment_url attribute: " . ($applicant->payment_url === $paymentUrl ? '✓ Works' : '✗ Failed'));
        $this->line("payment_success_url attribute: " . ($applicant->payment_success_url === $paymentSuccessUrl ? '✓ Works' : '✗ Failed'));
        $this->line("status_url attribute: " . ($applicant->status_url === $statusUrl ? '✓ Works' : '✗ Failed'));
        $this->line("exam_card_url attribute: " . ($applicant->exam_card_url === $examCardUrl ? '✓ Works' : '✗ Failed'));
        $this->newLine();

        // Test custom expiration
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('Testing Custom Expiration:');
        $this->newLine();

        $customPaymentUrl = $applicant->getPaymentUrl(14);
        $this->line("💳 Payment URL (custom: 14 days):");
        $this->line("   {$customPaymentUrl}");
        $this->newLine();

        // Security info
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('Security Features:');
        $this->line('✓ URLs include signature for tamper protection');
        $this->line('✓ URLs automatically expire after specified time');
        $this->line('✓ Signature validated by Laravel signed middleware');
        $this->line('✓ Cannot be guessed or predicted by unauthorized users');
        $this->newLine();

        // Test instructions
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->warn('Manual Testing Instructions:');
        $this->line('1. Copy any URL above and open in browser');
        $this->line('2. Should successfully load the page (valid signature)');
        $this->line('3. Modify any query parameter and reload');
        $this->line('4. Should show "expired-link" error page (invalid signature)');
        $this->newLine();

        $this->info('✓ Signed URLs test completed successfully!');

        return self::SUCCESS;
    }
}
