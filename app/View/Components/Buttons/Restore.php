<?php

namespace App\View\Components\Buttons;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Restore extends Component
{
    public function __construct(
        public string  $url = '#',
        public ?string $permission = null,
        public string  $title = 'Restore',
        public string  $icon = 'ti ti-reload',
    ) {
    }

    public function render(): View|Closure|string
    {
        return view('components.buttons.action');
    }
}
