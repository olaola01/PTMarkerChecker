<?php

namespace App\Listeners;

use App\Models\User;
use App\Mail\ApproveRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ApproveRequestListener
{
    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle(object $event)
    {
        Mail::to($event->admin)->send(new ApproveRequest($event->admin));
    }
}
