<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class WebPushVapidKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webpush:vapid-keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a fresh VAPID keypair for Web Push notifications';

    /**
     * One keypair is shared by the whole deployment (not per-user), so
     * this is a one-time setup step, not something run automatically.
     */
    public function handle(): int
    {
        $keys = VAPID::createVapidKeys();

        $this->line('Add these to your .env file:');
        $this->newLine();
        $this->line("VAPID_PUBLIC_KEY={$keys['publicKey']}");
        $this->line("VAPID_PRIVATE_KEY={$keys['privateKey']}");
        $this->line('VAPID_SUBJECT='.config('app.url'));

        return self::SUCCESS;
    }
}
