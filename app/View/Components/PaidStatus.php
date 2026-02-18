<?php

namespace App\View\Components;

use App\Enums\PaidStatus as PaidStatusEnum;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PaidStatus extends Component
{
    public bool $paid;

    public function __construct(
        PaidStatusEnum|bool|null $paid = null
    ) {
        // Convert enum to boolean or use provided boolean value
        if ($paid instanceof PaidStatusEnum) {
            $this->paid = $paid === PaidStatusEnum::Paid;
        } else {
            // Convert null to false, otherwise use the provided value
            $this->paid = $paid ?? false;
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.paid-status');
    }
}
