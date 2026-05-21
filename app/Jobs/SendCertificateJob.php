<?php

namespace App\Jobs;

use App\Models\Purchase;
use App\Models\User;
use App\Services\CertificateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCertificateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public User $user;
    public Purchase $purchase;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, Purchase $purchase)
    {
        $this->user = $user;
        $this->purchase = $purchase;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            CertificateService::generateAndSend($this->user, $this->purchase);
        } catch (\Throwable $e) {
            \Log::error('SendCertificateJob failed: '.$e->getMessage());
        }
    }
}
