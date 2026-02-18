<?php

namespace App\Observers;

use App\Models\Blacklist;

class BlacklistObserver
{
    /**
     * Handle the Blacklist "created" event.
     */
    public function created(Blacklist $blacklist): void
    {
        //
    }

    /**
     * Handle the Blacklist "updated" event.
     */
    public function updated(Blacklist $blacklist): void
    {
        //
    }

    /**
     * Handle the Blacklist "deleted" event.
     */
    public function deleted(Blacklist $blacklist): void
    {
        //
    }

    /**
     * Handle the Blacklist "restored" event.
     */
    public function restored(Blacklist $blacklist): void
    {
        //
    }

    /**
     * Handle the Blacklist "force deleted" event.
     */
    public function forceDeleted(Blacklist $blacklist): void
    {
        //
    }
}
