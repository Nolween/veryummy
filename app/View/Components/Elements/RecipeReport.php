<?php

namespace App\View\Components\Elements;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RecipeReport extends Component
{
    /**
     * Les personnes ayant fait le report de la recette.
     *
     * @var array<string>
     */
    public $reports;

    /**
     * Create a new component instance.
     *
     * @param  array<string>  $reports
     * @return void
     */
    public function __construct($reports)
    {
        $this->reports = $reports;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        return view('components.elements.recipe-report');
    }
}
