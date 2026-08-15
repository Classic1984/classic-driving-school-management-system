<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\MessageLog;
use App\Services\TermiiSmsService;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class SendLeadFollowUpReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-lead-follow-up-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Text every open lead (New or Contacted) a follow-up every 4 days until they are marked Converted or Lost';

    /**
     * How often a still-open lead gets a follow-up text, counted from
     * whichever is more recent: when they were first logged, or their last
     * reminder.
     */
    protected const FOLLOW_UP_INTERVAL_DAYS = 4;

    public function __construct(protected TermiiSmsService $sms, protected WhatsAppService $whatsapp)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dueAt = now()->subDays(self::FOLLOW_UP_INTERVAL_DAYS);

        $leads = Lead::whereIn('status', ['new', 'contacted'])
            ->where(function ($query) use ($dueAt) {
                $query->whereNull('last_reminded_at')
                    ->orWhere('last_reminded_at', '<=', $dueAt);
            })
            ->where('created_at', '<=', $dueAt)
            ->get();

        if ($leads->isEmpty()) {
            $this->warn('No leads are due for a follow-up.');

            return self::SUCCESS;
        }

        $sent = $leads->filter(function (Lead $lead) {
            $message = $lead->course_interested
                ? "Hi {$lead->name}, just checking in from Classic Driving School about your interest in {$lead->course_interested}. Reply or call us to get started!"
                : "Hi {$lead->name}, just checking in from Classic Driving School about your inquiry. Reply or call us to get started!";

            $channel = match (true) {
                $this->sms->send($lead->phone, $message) => 'sms',
                $this->whatsapp->send($lead->phone, config('services.twilio.whatsapp_templates.lead_follow_up'), ['1' => $lead->name, '2' => $lead->course_interested ?: 'your inquiry']) => 'whatsapp',
                default => null,
            };

            MessageLog::create([
                'recipient_type' => 'lead',
                'recipient_id' => $lead->id,
                'recipient_name' => $lead->name,
                'recipient_phone' => $lead->phone,
                'purpose' => 'lead_follow_up',
                'channel' => $channel,
                'status' => $channel ? 'sent' : 'failed',
                'message' => $message,
            ]);

            if ($channel !== null) {
                $lead->update(['last_reminded_at' => now()]);
            }

            return $channel !== null;
        })->count();

        $this->info("Lead follow-up sent to {$sent} of {$leads->count()} lead(s).");

        return self::SUCCESS;
    }
}
