<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Status extends Component
{
    public bool $status;
    public ?string $title;

    public function __construct(
        ?bool $status = null,
        ?string $title = 'Active'
    ) {
        // Convert null to false, otherwise use the provided value
        $this->status = $status ?? false;
        $this->title = $title;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.status');
    }
}
