<?php

namespace App\View\Components\Buttons;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Import extends Component
{
    public function __construct(
        public string  $url = '#',
        public ?string $permission = null,
        public string  $title = 'Import',
        public string  $icon = 'ph-upload-simple',
        public string  $color = 'primary',
    ) {
    }

    public function render(): View|Closure|string
    {
        return view('components.buttons.import');
    }
}
