<?php

namespace App\View\Components;

use Illuminate\View\Component;

class FlashMessage extends Component
{
    public function render()
    {
        return view('components.flash-message');
    }
}
