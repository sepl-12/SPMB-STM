<?php

namespace App\Console\Commands;

use App\Mail\ApplicantRegistered;
use App\Mail\ExamCardReady;
use App\Mail\PaymentConfirmed;
use App\Models\Applicant;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email? : Email address to send test emails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send test emails to verify email templates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'test@example.com';
        
        $this->info('🧪 Testing Email Templates...');
        $this->newLine();

        // Get test data
        $applicant = Applicant::with(['wave'])->first();
        $payment = Payment::with(['applicant.wave'])->first();

        if (!$applicant) {
            $this->error('❌ No applicant data found. Run seeders first!');
            return Command::FAILURE;
        }

        if (!$payment) {
            $this->error('❌ No payment data found. Run seeders first!');
            return Command::FAILURE;
        }

        // Test 1: Registration Email
        $this->info('1️⃣  Sending Registration Email...');
        try {
            Mail::to($email)->send(new ApplicantRegistered($applicant));
            $this->line('   ✅ Registration email sent successfully');
        } catch (\Exception $e) {
            $this->error('   ❌ Failed: ' . $e->getMessage());
        }
        $this->newLine();

        // Test 2: Payment Confirmation Email
        $this->info('2️⃣  Sending Payment Confirmation Email...');
        try {
            Mail::to($email)->send(new PaymentConfirmed($payment));
            $this->line('   ✅ Payment confirmation email sent successfully');
        } catch (\Exception $e) {
            $this->error('   ❌ Failed: ' . $e->getMessage());
        }
        $this->newLine();

        // Test 3: Exam Card Email
        $this->info('3️⃣  Sending Exam Card Email...');
        try {
            Mail::to($email)->send(new ExamCardReady($applicant));
            $this->line('   ✅ Exam card email sent successfully');
        } catch (\Exception $e) {
            $this->error('   ❌ Failed: ' . $e->getMessage());
        }
        $this->newLine();

        $this->info('✨ All tests complete!');
        $this->line('📧 Check your email at: ' . $email);
        
        if (config('mail.mailer') === 'smtp' && str_contains(config('mail.host'), 'mailtrap')) {
            $this->line('📬 Or check Mailtrap: https://mailtrap.io');
        }

        return Command::SUCCESS;
    }
}
