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
    public $reports;
    /**
     * Create a new component instance.
     *
     * @param array  $reports
     * @return void
     */
    public function __construct($reports)
    {
        $this->reports = $reports;
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
