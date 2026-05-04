<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class PublicoLayout extends Component
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
    ) {}

    /**
     * Vista base de la web publica.
     */
    public function render(): View
    {
        return view('layouts.publico');
    }
}
