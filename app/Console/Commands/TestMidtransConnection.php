<?php

namespace App\Console\Commands;

use App\Models\Applicant;
use App\Services\MidtransService;
use Illuminate\Console\Command;

class TestMidtransConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'midtrans:test 
                            {--all : Test with all applicants}
                            {--create : Create test applicant}
                            {--registration_number= : Test with specific registration number}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Midtrans connection and create test transactions';

    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        parent::__construct();
        $this->midtransService = $midtransService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Testing Midtrans Configuration...');
        $this->newLine();

        // Check config
        $serverKey = config('payment.midtrans.server_key');
        $clientKey = config('payment.midtrans.client_key');
        $isProduction = config('payment.midtrans.is_production');

        $this->info('📋 Configuration:');
        $this->line('Server Key: ' . substr($serverKey, 0, 15) . '...');
        $this->line('Client Key: ' . substr($clientKey, 0, 15) . '...');
        $this->line('Environment: ' . ($isProduction ? '🔴 Production' : '🟢 Sandbox'));
        $this->newLine();

        // Handle options
        if ($this->option('create')) {
            return $this->createTestApplicant();
        }

        if ($this->option('all')) {
            return $this->testAllApplicants();
        }

        if ($this->option('registration_number')) {
            return $this->testSpecificApplicant($this->option('registration_number'));
        }

        // Default: Select from list or random
        return $this->testRandomApplicant();
    }

    /**
     * Test with random applicant
     */
    protected function testRandomApplicant()
    {
        $applicants = Applicant::with('wave')->get();

        if ($applicants->isEmpty()) {
            $this->error('❌ No applicants found in database.');
            $this->line('💡 Create one with: php artisan midtrans:test --create');
            return 1;
        }

        // Show available applicants
        $this->info('📋 Available Applicants:');
        $choices = [];
        foreach ($applicants as $index => $applicant) {
            $choices[] = ($index + 1) . '. ' . $applicant->registration_number . ' - ' . $applicant->applicant_full_name;
        }
        $choices[] = 'Random';
        $choices[] = 'Cancel';

        $choice = $this->choice(
            'Select applicant to test (or choose Random):',
            $choices,
            count($choices) - 2 // Default to Random
        );

        if ($choice === 'Cancel') {
            $this->info('Cancelled.');
            return 0;
        }

        if ($choice === 'Random') {
            $applicant = $applicants->random();
            $this->line('🎲 Selected random applicant');
        } else {
            $index = (int) substr($choice, 0, strpos($choice, '.')) - 1;
            $applicant = $applicants[$index];
        }

        return $this->testTransaction($applicant);
    }

    /**
     * Test with specific applicant
     */
    protected function testSpecificApplicant($registrationNumber)
    {
        $applicant = Applicant::with('wave')
            ->where('registration_number', $registrationNumber)
            ->first();

        if (!$applicant) {
            $this->error("❌ Applicant with registration number '{$registrationNumber}' not found.");
            return 1;
        }

        return $this->testTransaction($applicant);
    }

    /**
     * Test with all applicants
     */
    protected function testAllApplicants()
    {
        $applicants = Applicant::with('wave')->get();

        if ($applicants->isEmpty()) {
            $this->error('❌ No applicants found in database.');
            return 1;
        }

        $this->info("🔄 Testing with {$applicants->count()} applicants...");
        $this->newLine();

        $successCount = 0;
        $failCount = 0;

        foreach ($applicants as $applicant) {
            $this->line("Testing: {$applicant->registration_number} - {$applicant->applicant_full_name}");
            
            $result = $this->testTransaction($applicant, false);
            
            if ($result === 0) {
                $successCount++;
            } else {
                $failCount++;
            }
            
            $this->newLine();
        }

        $this->info("✅ Success: {$successCount}");
        $this->error("❌ Failed: {$failCount}");

        return 0;
    }

    /**
     * Create test applicant
     */
    protected function createTestApplicant()
    {
        $this->info('🔨 Creating test applicant...');
        
        // Get active wave
        $wave = \App\Models\Wave::where('status', 'active')->first();
        
        if (!$wave) {
            $this->error('❌ No active wave found. Please create a wave first.');
            return 1;
        }

        $name = $this->ask('Applicant name', 'Test User ' . now()->format('YmdHis'));
        $email = $this->ask('Email', 'test' . now()->timestamp . '@test.com');
        $phone = $this->ask('Phone', '0812' . rand(10000000, 99999999));

        // Create applicant
        $applicant = Applicant::create([
            'wave_id' => $wave->id,
            'registration_number' => $this->generateRegistrationNumber(),
            'applicant_full_name' => $name,
            'registered_datetime' => now(),
            // payment_status is computed from Payment relation
        ]);

        // Create submission
        $submission = \App\Models\Submission::create([
            'applicant_id' => $applicant->id,
            'form_version_id' => $wave->forms()->first()?->activeVersion?->id,
            'submitted_datetime' => now(),
            'answers_json' => [
                'email' => $email,
                'phone' => $phone,
                'no_hp' => $phone,
            ],
        ]);

        $this->info('✅ Test applicant created successfully!');
        $this->line('Registration Number: ' . $applicant->registration_number);
        $this->line('Name: ' . $applicant->applicant_full_name);
        $this->newLine();

        // Ask if want to test transaction
        if ($this->confirm('Create test transaction for this applicant?', true)) {
            return $this->testTransaction($applicant);
        }

        return 0;
    }

    /**
     * Test transaction creation
     */
    protected function testTransaction(Applicant $applicant, $verbose = true)
    {
        if ($verbose) {
            $this->info('👤 Testing with applicant:');
            $this->line('Registration Number: ' . $applicant->registration_number);
            $this->line('Name: ' . $applicant->applicant_full_name);
            $this->line('Wave: ' . $applicant->wave->wave_name);
            $this->line('Amount: Rp ' . number_format($applicant->wave->registration_fee_amount, 0, ',', '.'));
            $this->newLine();
        }

        // Try to create a test transaction
        if ($verbose) {
            $this->info('🔄 Creating test transaction...');
        }
        
        try {
            $result = $this->midtransService->createTransaction($applicant);

            if ($result['success']) {
                if ($verbose) {
                    $this->info('✅ Transaction created successfully!');
                    $this->newLine();
                    
                    $this->line('📝 Transaction Details:');
                    $this->line('Order ID: ' . $result['order_id']);
                    $this->line('Snap Token: ' . substr($result['snap_token'], 0, 30) . '...');
                    $this->line('Payment ID: ' . $result['payment_id']);
                    $this->newLine();
                    
                    $this->info('✅ Midtrans integration is working correctly!');
                    $this->newLine();
                    
                    $this->line('🌐 Payment URL:');
                    $this->line(route('payment.show', $applicant->registration_number));
                    $this->newLine();
                    
                    // Show Snap URL for testing
                    $snapUrl = (config('payment.midtrans.is_production') ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com')
                        . '/snap/v3/redirection/' . $result['snap_token'];
                    $this->line('🔗 Direct Snap URL (for testing):');
                    $this->line($snapUrl);
                }
                
                return 0;
            } else {
                if ($verbose) {
                    $this->error('❌ Failed to create transaction');
                    $this->error('Error: ' . $result['error']);
                }
                return 1;
            }
        } catch (\Exception $e) {
            if ($verbose) {
                $this->error('❌ Exception occurred');
                $this->error('Error: ' . $e->getMessage());
                $this->newLine();
                $this->line('Please check:');
                $this->line('1. Midtrans credentials in .env file');
                $this->line('2. Internet connection');
                $this->line('3. Midtrans API status');
            }
            return 1;
        }
    }

    /**
     * Generate unique registration number
     */
    protected function generateRegistrationNumber(): string
    {
        $year = now()->year;
        $prefix = 'PPDB-' . $year . '-';
        
        // Get last registration number for this year
        $lastNumber = Applicant::where('registration_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('registration_number');

        if ($lastNumber) {
            $lastNum = (int) substr($lastNumber, -5);
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }

        return $prefix . str_pad($newNum, 5, '0', STR_PAD_LEFT);
    }
}
