<?php

namespace App\Console\Commands;

use App\Mail\RemainingSlotsAvailable;
use App\Models\User;
use App\Services\PackageSlotService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendRemainingSlotEmails extends Command
{
    protected $signature = 'slots:send-availability-emails';

    protected $description = 'Send current package slot availability to all users.';

    public function handle(PackageSlotService $slotService): int
    {
        $packages = array_values($slotService->getPackageSlotDetails());
        $sent = 0;

        User::query()
            ->whereNotNull('email')
            ->orderBy('id')
            ->chunkById(100, function ($users) use ($packages, &$sent): void {
                foreach ($users as $user) {
                    Mail::to($user->email)->send(new RemainingSlotsAvailable($user, $packages));
                    $sent++;
                }
            });

        $this->info("Sent remaining slot emails to {$sent} user(s).");

        return self::SUCCESS;
    }
}
