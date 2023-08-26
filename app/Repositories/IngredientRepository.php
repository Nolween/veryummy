<?php

namespace App\Repositories;

use App\Http\Requests\Ingredient\AllowIngredientRequest;
use App\Http\Requests\Ingredient\DenyIngredientRequest;
use App\Http\Requests\Ingredient\StoreIngredientRequest;
use App\Mail\AcceptedIngredient;
use App\Mail\RefusedIngredient;
use App\Models\Ingredient;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class IngredientRepository
{

    /**
     * @details Récupération des ingrédients
     * @param int $type
     * @param string|null $search
     * @return LengthAwarePaginator
     */
    public function getIngredients(?int $type, ?string $search): LengthAwarePaginator
    {
        // Récupération des ingrédients
        $ingredients = Ingredient::select('*');
        // Si on a quelque chose dans la recherche
        if (!empty($search)) {
            $ingredients->where('name', 'like', "%{$search}%");
        }
        $ingredients->where('is_accepted', $type);


        return $ingredients->with('user')->paginate(20);
    }

    /**
     * @details Refuser un ingrédient
     */
    public function denyIngredient(DenyIngredientRequest $request): bool
    {
        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            // Récupération de l'ingrédient par son Id
            $ingredient = Ingredient::where('id', $request->ingredientid)->with('user')->firstOrFail();

            $authorMail = $ingredient->user->email;
            $ingredient->is_accepted = false;
            $ingredient->save();

            if (!empty($authorMail) && $request->typeList == 0) {
                // Envoi de mail à la personne ayant proposé l'ingrédient
                $informations = [
                    'ingredient' => $ingredient->name,
                    'url'        => URL::to('/'),
                    'message'    => $request->denymessage,
                ];
                Mail::to($authorMail)->send(new RefusedIngredient($informations));
            }

            // Validation de la transaction
            DB::commit();

            return true;
        } // Si erreur dans la transaction
        catch (Exception $e) {
            DB::rollback();

            return false;
        }
    }

    public function allowIngredient(AllowIngredientRequest $request): bool
    {
        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            // Récupération de l'ingrédient par son Id
            $ingredient = Ingredient::where('id', $request->ingredientid)->with('user')->firstOrFail();
            $authorMail = null;
            if ($ingredient->user) {
                $authorMail = $ingredient->user->email;
            }
            $ingredient->name = $request->finalname;
            $ingredient->icon = Str::slug($request->finalname, '_');
            $ingredient->is_accepted = $request->allow;
            // Si l'ingrédient est accepté, il passe sur le compte principal, en cas de suppression de compte du demandeur
            $ingredient->user_id = 1;
            // Définition du régime de l'aliment
            $ingredient->vegetarian_compatible = $request->vegetarian ?? false;
            $ingredient->vegan_compatible = $request->vegan ?? false;
            $ingredient->gluten_free_compatible = $request->glutenfree ?? false;
            $ingredient->halal_compatible = $request->halal ?? false;
            $ingredient->kosher_compatible = $request->kosher ?? false;
            $ingredient->save();

            // Envoi de mail à la personne ayant proposé l'ingrédient
            $informations = ['ingredient' => $request->finalname, 'url' => URL::to('/')];
            if ($authorMail && $request->typeList == 0) {
                Mail::to($authorMail)->send(new AcceptedIngredient($informations));
            }

            // Validation de la transaction
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            return false;
        }
    }

    public function storeIngredient(StoreIngredientRequest $request)
    {
        // Création d'un nouvel ingredient
        $newIngredient = new Ingredient;
        $newIngredient->user_id = Auth::user()->id;
        $newIngredient->name = $request->ingredient;
        $newIngredient->icon = null;
        $newIngredient->is_accepted = null;
        return $newIngredient->save();
    }

}
