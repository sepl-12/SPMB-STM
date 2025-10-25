<?php

namespace App\Console\Commands;

use App\Services\Email\EmailServiceInterface;
use App\Services\Email\GmailEmailService;
use App\Services\Email\LaravelEmailService;
use Illuminate\Console\Command;

class EmailHealthCheckCommand extends Command
{
    protected $signature = 'email:health-check';
    protected $description = 'Check the health status of all email services';
    public function handle(): int
    {
        $this->info('🏥 Email Services Health Check');
        $this->line('📧 Primary: Gmail API | Fallback: Laravel Mail');
        $this->newLine();

        $gmailService = app(GmailEmailService::class);
        $laravelService = app(LaravelEmailService::class);
        $activeService = app(EmailServiceInterface::class);

        // Check Gmail API (Primary)
        $gmailHealthy = $gmailService->isHealthy();
        if ($gmailHealthy) {
            $this->line("✅ Gmail API Service (PRIMARY): Healthy");
        } else {
            $this->error("❌ Gmail API Service (PRIMARY): Unhealthy");
        }

        // Check Laravel Mail (Fallback)
        $laravelHealthy = $laravelService->isHealthy();
        if ($laravelHealthy) {
            $this->line("✅ Laravel Mail Service (FALLBACK): Healthy");
        } else {
            $this->error("❌ Laravel Mail Service (FALLBACK): Unhealthy");
        }

        // Show active service
        $activeServiceName = $activeService->getServiceName();
        $this->newLine();
        $this->info("🎯 Currently Active Service: {$activeServiceName}");

        $this->newLine();

        if ($gmailHealthy) {
            $this->info('🎉 Gmail API is healthy - Primary service ready!');
            return Command::SUCCESS;
        } elseif ($laravelHealthy) {
            $this->warn('⚠️  Gmail API unavailable, using Laravel Mail fallback');
            return Command::SUCCESS;
        } else {
            $this->error('❌ Both email services have issues. Check your configuration.');
            return Command::FAILURE;
        }
    }
}
