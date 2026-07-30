<?php

namespace App\Listeners;

use App\Events\OurExampleEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OurExampleListner
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OurExampleEvent $event): void
    {
        //
    }
}
