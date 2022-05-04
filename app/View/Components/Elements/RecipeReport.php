<?php

namespace App\View\Components\Elements;

use Illuminate\View\Component;

class RecipeReport extends Component
{
    /**
     * Les personnes ayant fait le report de la recette.
     *
     * @var array
     */
    public $reports;
    /**
     * Create a new component instance.
     *
     * @param  array  $reports
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
        return view('components.elements.recipe-report');
    }
}
