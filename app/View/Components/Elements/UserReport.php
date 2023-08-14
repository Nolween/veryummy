<?php

namespace App\View\Components\Elements;

use Illuminate\View\Component;

class UserReport extends Component
{
    /**
     * Les personnes ayant fait le report de l'utilisateur.
     *
     * @var array
     */
    public $opinions;

    /**
     * Create a new component instance.
     *
     * @param  array  $opinions
     * @return void
     */
    public function __construct($opinions)
    {
        $this->opinions = $opinions;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.elements.user-report');
    }
}
