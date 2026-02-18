<?php

namespace App\View\Components\Buttons;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Trash extends Component
{
    public function __construct(
        public string  $url = '#',
        public ?string $permission = null,
        public string  $title = 'Trash',
        public string  $icon = 'ph-trash',
        public string  $color = 'danger',
    ) {
    }

    public function render(): View|Closure|string
    {
        return view('components.buttons.trash');
    }
}
