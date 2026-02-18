<?php

namespace App\View\Components\Buttons;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Edit extends Component
{
    public function __construct(
        public string  $url = '#',
        public ?string $permission = null,
        public string  $title = 'Edit',
        public string  $icon = 'ti ti-pencil',
    ) {
    }

    public function render(): View|Closure|string
    {
        return view('components.buttons.action');
    }
}
