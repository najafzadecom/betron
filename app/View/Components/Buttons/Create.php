<?php

namespace App\View\Components\Buttons;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Create extends Component
{
    public function __construct(
        public string  $url = '#',
        public ?string $permission = null,
        public string  $title = 'Create',
        public string  $icon = 'ph-plus',
        public string  $color = 'success',
    ) {
    }

    public function render(): View|Closure|string
    {
        return view('components.buttons.create');
    }
}
