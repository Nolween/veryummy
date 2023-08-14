<?php

namespace App\Console\Commands;

use App\Mail\PendingModeration as MailPendingModeration;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class PendingModeration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moderation:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Récupère les modérations en attente de modération';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        // Récupération des ingrédients en attente de modération
        $informations['ingredients'] = Ingredient::where('is_accepted', null)->with('user')->get();

        // Récupération des recettes en non ignorées avec des signalements
        $informations['recettes'] = Recipe::having('opinions_count', '>', 0)
            ->with('user')
            ->withCount(['opinions' => function (Builder $query) {
                $query->where('is_reported', '=', true);
            }])->get();

        $informations['url'] = URL::to('/');

        // Si on a au moins une chose en attente de modération
        if (count($informations['ingredients']) > 0 || count($informations['recettes']) > 0) {
            // Envoi du mail avec toutes les informations
            Mail::to('nolween.lopez@gmail.com')->send(new MailPendingModeration($informations));
        }

        return true;
    }
}
