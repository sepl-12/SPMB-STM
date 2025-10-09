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
    protected $signature = 'midtrans:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Midtrans connection and configuration';

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
        $this->info('Testing Midtrans Configuration...');
        $this->newLine();

        // Check config
        $serverKey = config('midtrans.server_key');
        $clientKey = config('midtrans.client_key');
        $isProduction = config('midtrans.is_production');

        $this->info('Configuration:');
        $this->line('Server Key: ' . substr($serverKey, 0, 15) . '...');
        $this->line('Client Key: ' . substr($clientKey, 0, 15) . '...');
        $this->line('Environment: ' . ($isProduction ? 'Production' : 'Sandbox'));
        $this->newLine();

        // Check if there's an applicant to test
        $applicant = Applicant::with('wave')->first();

        if (!$applicant) {
            $this->error('No applicant found in database. Please create an applicant first.');
            return 1;
        }

        $this->info('Testing with applicant:');
        $this->line('Registration Number: ' . $applicant->registration_number);
        $this->line('Name: ' . $applicant->applicant_full_name);
        $this->line('Amount: Rp ' . number_format($applicant->wave->registration_fee_amount, 0, ',', '.'));
        $this->newLine();

        // Try to create a test transaction
        $this->info('Creating test transaction...');
        
        try {
            $result = $this->midtransService->createTransaction($applicant);

            if ($result['success']) {
                $this->info('✓ Transaction created successfully!');
                $this->line('Order ID: ' . $result['order_id']);
                $this->line('Snap Token: ' . substr($result['snap_token'], 0, 20) . '...');
                $this->line('Payment ID: ' . $result['payment_id']);
                $this->newLine();
                $this->info('✓ Midtrans integration is working correctly!');
                $this->newLine();
                $this->line('You can access the payment page at:');
                $this->line(route('payment.show', $applicant->registration_number));
                
                return 0;
            } else {
                $this->error('✗ Failed to create transaction');
                $this->error('Error: ' . $result['error']);
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('✗ Exception occurred');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            $this->line('Please check:');
            $this->line('1. Midtrans credentials in .env file');
            $this->line('2. Internet connection');
            $this->line('3. Midtrans API status');
            return 1;
        }
    }
}
