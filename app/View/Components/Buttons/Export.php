<?php

namespace App\View\Components\Buttons;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Export extends Component
{
    public function __construct(
        public string  $url = '#',
        public ?string $permission = null,
        public string  $title = 'Export',
        public string  $icon = 'ph-file-xls',
        public string  $color = 'warning',
    ) {
    }

    public function render(): View|Closure|string
    {
        return view('components.buttons.export');
    }
}
