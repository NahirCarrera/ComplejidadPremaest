<?php

namespace App\Http\Controllers\Concerns;

use App\Models\RegistrationPeriod;

trait HasCurrentPeriod
{
    protected function getCurrentPeriod()
    {
        return RegistrationPeriod::latest('start_date')->first();
    }
}
