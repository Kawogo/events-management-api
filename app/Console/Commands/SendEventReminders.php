<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Notifications\EventReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SendEventReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-event-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification to user about the event start time before 24hrs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $events = Event::with('attendees.user')->whereBetween('start_time', [now(), now()->addDay()])->take(2)->get();
        $eventCount = $events->count();
        $label = Str::plural('event', $eventCount);

        $this->info("Reminding {$eventCount} {$label}");

        $events->each(fn($event) => $event->attendees->take(2)->each(
            fn($attendee) => $attendee->user->notify(new EventReminderNotification($event)))
        );
        
    }
}
