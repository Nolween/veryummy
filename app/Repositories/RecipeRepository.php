<?php

namespace App\Repositories;

use App\Enums\Diets;
use App\Enums\RecipeTypes;
use App\Helpers\ImageTransformation;
use App\Http\Requests\Recipe\RecipeAdminIndexRequest;
use App\Http\Requests\Recipe\RecipeAllowRequest;
use App\Http\Requests\Recipe\RecipeCommentRequest;
use App\Http\Requests\Recipe\RecipeExplorationRequest;
use App\Http\Requests\Recipe\RecipeNoteBookIndexRequest;
use App\Http\Requests\Recipe\RecipeStatusRequest;
use App\Http\Requests\Recipe\RecipeStoreRequest;
use App\Http\Requests\Recipe\RecipeUpdateRequest;
use App\Http\Requests\Recipe\RecipeUserIndexRequest;
use App\Mail\RefusedRecipe;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeIngredients;
use App\Models\RecipeOpinion;
use App\Models\RecipeStep;
use App\Models\RecipeType;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class RecipeRepository
{
    /**
     * @details Récupération des recettes de la page d'accueil
     *
     * @return array<string, mixed>
     */
    public function getWelcomeIndex()
    {
        $response = [];
        // Récupération de 4 recettes au hasard avec plus de 4 en note
        $response['popularRecipes'] = Recipe::select(
            'id',
            'name',
            'cooking_time as cookingTime',
            'making_time as makingTime',
            'image as photo',
            'score'
        )
                                            ->withCount('steps') // Nombre d'étapes possède la recette
                                            ->withCount('ingredients') // Nombre d'ingrédients dans la recette
                                            ->where('score', '>', 4) // Avec une note supérieure à 4
                                            ->inRandomOrder() // Recette au hasard
                                            ->take(4) // 4 recettes
                                            ->get();

        // Récupération des 4 dernières recettes créées par les utilisateurs
        $response['recentRecipes'] = Recipe::select(
            'id',
            'name',
            'cooking_time as cookingTime',
            'making_time as makingTime',
            'image as photo',
            'score'
        )
                                           ->withCount('steps') // Nombre d'étapes possède la recette
                                           ->withCount('ingredients') // Nombre d'ingrédients dans la recette
                                           ->orderBy('created_at', 'DESC') // Classées par ordre de création décroissant
                                           ->take(4) // 4 recettes
                                           ->get();
        // Compteur des informations
        $response['counts'] = [
            'totalRecipes' => Recipe::where('is_accepted', true)->count(),
            'totalIngredients' => Ingredient::count(),
            'totalUsers' => User::where('is_banned', false)->count(),
        ];

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    public function getExplorationIndex(RecipeExplorationRequest $request): array
    {
        $userId = Auth::user()->id ?? null;

        $response = [];

        $recipes = Recipe::select('id', 'name', 'score', 'making_time', 'cooking_time', 'image')
                         ->where('name', 'like', "%{$request->name}%")
                         ->withCount('ingredients')
                         ->withCount('steps');
        // Si utilisateur connecté, on n'oublie pas ses recettes
        if ($userId) {
            $recipes->where('user_id', '!=', $userId)->with('user')
                    ->with('opinion');
        }

        // Si on a un type de plat (entrée, plat, dessert,...)
        if ($request->type && in_array( $request->type, RecipeTypes::allValues())) {
            $recipes = $recipes->where('recipe_type', $request->type);
        }

        // Si on a un filtre sur le type de régime
        if ($request->diet && $request->diet > 0) {
            switch ((int)$request->diet) {
                case 1: // Végétarien
                    $recipesCount = $recipes = $recipes->whereJsonContains('diets', Diets::VEGETARIAN->value);
                    break;
                case 2: // Vegan
                    $recipesCount = $recipes = $recipes->whereJsonContains('diets', Diets::VEGAN->value);
                    break;
                case 3: // Sans gluten
                    $recipesCount = $recipes = $recipes->whereJsonContains('diets', Diets::GLUTEN_FREE->value);
                    break;
                case 4: // Halal
                    $recipesCount = $recipes = $recipes->whereJsonContains('diets', Diets::HALAL->value);
                    break;
                case 5: // casher
                    $recipesCount = $recipes = $recipes->whereJsonContains('diets', Diets::KOSHER->value);
                    break;
                default:
                    $recipesCount = $recipes;
                    break;
            }
            $response['total'] = $recipesCount->count();
        } // Si pas de filtre de type
        else {
            $response['total'] = $recipes->count();
        }

        // Pagination des recettes
        $response['recipes'] = $recipes->paginate(20);
        // Création d'un type temporaire tous
        $allTypes = RecipeTypes::allValues();
        $allTypes = array_merge(['all'], $allTypes);
        // Récupération de tous les types de plat auquel on ajoute le type tous
        $response['types'] = $allTypes;
        // dd($response['types']);
        // Renvoi des données de filtres de recherche
        $response['search'] = $request->name ?? null;
        $response['diet'] = $request->diet ?? null;
        $response['type'] = $request->type ?? null;

        return $response;
    }

    /**
     * @details Affichage de la page de la recette
     *
     * @return array<string, mixed>
     */
    public function getAdminIndex(RecipeAdminIndexRequest $request, int $type): array
    {
        $response = [];

        switch ($type) {
            case 0:
                // Récupération des ingrédients
                $recipes = Recipe::having('opinions_count', '>', 0)
                                 ->with('user')
                                 ->with([
                                            'opinions' => function ($query) {
                                                $query->where('is_reported', '=', true);
                                            },
                                        ])
                                 ->withCount([
                                                 'opinions' => function (Builder $query) {
                                                     $query->where('is_reported', '=', true);
                                                 },
                                             ]);

                // Si recherche
                if (!empty($request->search)) {
                    $recipes->where('name', 'like', "%{$request->search}%");
                }
                $response['recipes'] = $recipes->paginate(20);
                break;
            case 1:
                // Récupération des ingrédients
                $recipes = Recipe::having('opinions_count', '=', 0)
                                 ->with('user')
                                 ->withCount([
                                                 'opinions' => function (Builder $query) {
                                                     $query->where('is_reported', '=', true);
                                                 },
                                             ]);

                // Si recherche
                if (!empty($request->search)) {
                    $recipes->where('name', 'like', "%{$request->search}%");
                }

                $response['recipes'] = $recipes->paginate(20);
                break;
            default:
                $type = null;
                $response['recipes'] = [];
                break;
        }

        $response['typeList'] = (int)$type;
        $response['search'] = $request->search;

        return $response;
    }

    public function moderateRecipe(RecipeAllowRequest $request): bool
    {
        $user = Auth::user();
        if ($user === null) {
            return false;
        }

        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            // Récupération de la recette par son Id
            $recipe = Recipe::where('id', $request->recipeid)->with('user')->firstOrFail();

            // Si on ignore les signalements
            if ($request->allow == true) {
                RecipeOpinion::where('recipe_id', $request->recipeid)->where('is_reported', true)->update(
                    ['is_reported' => false]
                );
            } // Si on supprime la recette
            elseif ($request->allow == false) {
                Recipe::destroy($recipe->id);
                // Envoi de mail de désactivation à la personne ayant proposé la recette
                $informations = ['recipe' => $recipe->name, 'url' => URL::to('/')];
                Mail::to($user->email)->send(new RefusedRecipe($informations));
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

    public function updateStatus(RecipeStatusRequest $request): bool
    {
        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();
        if ($user === null) {
            return false;
        }

        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            // La recette existe t-elle?
            $recipe = Recipe::findOrFail($request->recipeid);

            RecipeOpinion::updateOrCreate(
                ['user_id' => $user->id, 'recipe_id' => $request->recipeid],
                ['is_favorite' => $request->is_favorite, 'is_reported' => $request->is_reported]
            );

            // Validation de la transaction
            DB::commit();

            return true;
        } // Si erreur dans la transaction
        catch (Exception $e) {
            DB::rollback();

            return false;
        }
    }

    public function storeRecipe(RecipeStoreRequest $request): bool
    {
        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            $newRecipe = new Recipe;
            $newRecipe->name = $request->nom;
            $newRecipe->cooking_time = $request->cuisson;
            $newRecipe->making_time = $request->preparation;
            $newRecipe->servings = $request->parts;
            $newRecipe->is_accepted = true;
            $newRecipe->recipe_type = $request->type;
            $newRecipe->user_id = Auth::user()->id;
            // Sauvegarde de la recette
            $newRecipe->save();
            //? Création des étapes pour la recette
            $stepOrder = 0;
            foreach ($request->steps as $step) {
                if (!empty($step['stepDescription'])) {
                    // Augmentation de l'ordre de l'étape
                    $stepOrder++;
                    // Construction de l'étape
                    $newStep = new RecipeStep;
                    $newStep->order = $stepOrder;
                    $newStep->description = $step['stepDescription'];
                    $newStep->recipe_id = $newRecipe->id;
                    $newStep->save();
                }
            }
            //? Création des ingrédients pour la recette
            $ingredientOrder = 0;
            foreach ($request->ingredients as $ingredient) {
                if (!empty($ingredient['ingredientId'])) {
                    $ingredientOrder++;
                    // Construction de relation ingrédient-recette
                    $newRecipeIngredient = new RecipeIngredients;
                    $newRecipeIngredient->recipe_id = $newRecipe->id;
                    $newRecipeIngredient->order = $ingredientOrder;
                    $unit = $ingredient['ingredientUnit'];

                    $newRecipeIngredient->unit = $ingredient['ingredientUnit'];
                    $ingr = Ingredient::where('id', $ingredient['ingredientId'])->firstOrFail();

                    $newRecipeIngredient->ingredient_id = $ingredient['ingredientId'];
                    $newRecipeIngredient->quantity = $ingredient['ingredientQuantity'];
                    $newRecipeIngredient->save();
                }
            }
            //? Définition des différentes catégories de la recette
            // Tableau des compatibilités de la recette
            $diets = Diets::allValues();
            // Parcours des ingrédients ajoutés
            foreach ($request->ingredients as $ingredient) {
                if (!empty($ingredient['ingredientId'])) {
                    // Récupération de l'ingrédient
                    $ingredientCompatible = Ingredient::findOrFail($ingredient['ingredientId']);
                    // @phpstan-ignore-next-line
                    foreach ($ingredientCompatible->diets as $diet) {
                        //    Si la diet n'est pas présent dans l'ingrédient, on la retire
                        if (!in_array($diet, $diets)) {
                            unset($diets[array_search($diet, $diets)]);
                        }
                    }
                    if (empty($diets)) {
                        break;
                    }
                }
            }
            $newRecipe->diets = array_values($diets);
            //? Création d'un nom pour l'image
            $newRecipe->image = $newRecipe->id . '-' . Str::slug($request->nom, '-') . '.avif';
            //? Si on a une image valide
            if ($request->photoInput && function_exists('imageavif')) {
                switch ($request->photoInput->extension()) {
                    case 'jpg':
                    case 'jpeg':
                        $imgProperties = getimagesize($request->photoInput->path());
                        $gdImage = imagecreatefromjpeg($request->photoInput->path());
                        if ($gdImage) {
                            imageavif($gdImage, storage_path('app/public/img/full/' . $newRecipe->image));
                            $resizeImg = ImageTransformation::image_resize(
                                $gdImage,
                                $imgProperties[0] ?? 0,
                                $imgProperties[1] ?? 0
                            );
                            imageavif($resizeImg, storage_path('app/public/img/thumbnail/' . $newRecipe->image));
                        }
                        // Création d'une miniature
                        break;
                    case 'png':
                        $imgProperties = getimagesize($request->photoInput->path());
                        $gdImage = imagecreatefrompng($request->photoInput->path());
                        if ($gdImage) {
                            imageavif($gdImage, storage_path('app/public/img/full/' . $newRecipe->image));
                            $resizeImg = ImageTransformation::image_resize(
                                $gdImage,
                                $imgProperties[0] ?? 0,
                                $imgProperties[1] ?? 0
                            );
                            imageavif($resizeImg, 'img/thumbnail/' . $newRecipe->image);
                        }
                        break;
                    case 'avif':
                        $gdImage = imagecreatefromavif($request->photoInput->path());
                        if ($gdImage) {
                            imageavif($gdImage, storage_path('app/public/img/full/' . $newRecipe->image));
                            $resizeImg = ImageTransformation::image_resize(
                                $gdImage,
                                imagesx($gdImage),
                                imagesy($gdImage)
                            );
                            imageavif($resizeImg, storage_path('app/public/img/thumbnail/' . $newRecipe->image));
                        }
                        break;
                    default:
                        $imgProperties = getimagesize($request->photoInput->path());
                        $gdImage = imagecreatefromjpeg($request->photoInput->path());
                        if ($gdImage) {
                            imageavif($gdImage, storage_path('app/public/img/full/' . $newRecipe->image));
                            $resizeImg = ImageTransformation::image_resize(
                                $gdImage,
                                $imgProperties[0] ?? 0,
                                $imgProperties[1] ?? 0
                            );
                            imageavif($resizeImg, 'img/thumbnail/' . $newRecipe->image);
                        }
                        break;
                }
                if ($gdImage) {
                    imagedestroy($gdImage);
                }
                if (isset($resizeImg)) {
                    imagedestroy($resizeImg);
                }
            }
            $newRecipe->save();

            DB::commit();

            return true;
        } // Si erreur dans la transaction
        catch (Exception $e) {
            DB::rollback();

            return false;
        }
    }

    public function updateRecipe(RecipeUpdateRequest $request, Recipe $recipe): bool
    {
        $user = Auth::user();

        if ($user === null) {
            return false;
        }
        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            $newName = $recipe->name !== $request->nom;
            $oldImageName = $recipe->image;
            $recipe->name = $request->nom;
            $recipe->recipe_type = $request->type;
            $recipe->cooking_time = $request->cuisson;
            $recipe->making_time = $request->preparation;
            $recipe->servings = $request->parts;
            $recipe->user_id = $user->id;
            // Sauvegarde de la recette
            $recipe->save();

            // On efface les étapes de la recette avant de les refaire
            $stepsDelete = RecipeStep::where('recipe_id', $recipe->id)->delete();

            //? Création des étapes pour la recette
            $stepOrder = 0;
            foreach ($request->steps as $step) {
                if (!empty($step['stepDescription'])) {
                    // Augmentation de l'ordre de l'étape
                    $stepOrder++;
                    // Construction de l'étape
                    $newStep = new RecipeStep;
                    $newStep->order = $stepOrder;
                    $newStep->description = $step['stepDescription'];
                    $newStep->recipe_id = $recipe->id;
                    $newStep->save();
                }
            }

            // On efface les étapes de la recette avant de les refaire
            $ingredientsDelete = RecipeIngredients::where('recipe_id', $recipe->id)->delete();
            //? Création des ingrédients pour la recette
            $ingredientOrder = 0;
            foreach ($request->ingredients as $ingredient) {
                if (!empty($ingredient['ingredientId'])) {
                    $ingredientOrder++;
                    // Construction de relation ingrédient-recette
                    $newRecipeIngredient = new RecipeIngredients;
                    $newRecipeIngredient->recipe_id = $recipe->id;
                    $newRecipeIngredient->order = $ingredientOrder;
                    $unit = $ingredient['ingredientUnit'];
                    $newRecipeIngredient->unit = $ingredient['ingredientUnit'];
                    $ingr = Ingredient::where('id', $ingredient['ingredientId'])->firstOrFail();

                    $newRecipeIngredient->quantity = $ingredient['ingredientQuantity'];
                    $newRecipeIngredient->ingredient_id = $ingredient['ingredientId'];
                    $newRecipeIngredient->save();
                }
            }

            //? Définition des différentes catégories de la recette
            // Tableau des compatibilités de la recette

            $diets = Diets::allValues();
            // Parcours des ingrédients ajoutés
            foreach ($request->ingredients as $ingredient) {
                if (!empty($ingredient['ingredientId'])) {
                    // Récupération de l'ingrédient
                    $ingredientCompatible = Ingredient::findOrFail($ingredient['ingredientId']);
                    // @phpstan-ignore-next-line
                    foreach (json_decode($ingredientCompatible->diets) as $diet) {
                        //    Si la diet n'est pas présent dans l'ingrédient, on la retire
                        if (!in_array($diet, $diets)) {
                            unset($diets[array_search($diet, $diets)]);
                        }
                    }
                    if (empty($diets)) {
                        break;
                    }
                }
            }
            $recipe->diets = $diets;

            //? Création d'un nom pour l'image
            $recipe->image = $recipe->id . '-' . Str::slug($request->nom, '-') . '.avif';
            //? Si on a une image valide
            if ($request->photoInput && function_exists('imageavif')) {
                // Suppression des images existantes
                File::delete(storage_path('app/public/img/full/' . $oldImageName));
                File::delete(storage_path('app/public/img/thumbnail/' . $oldImageName));
                switch ($request->photoInput->extension()) {
                    case 'jpg':
                    case 'jpeg':
                        $imgProperties = getimagesize($request->photoInput->path());
                        $gdImage = imagecreatefromjpeg($request->photoInput->path());
                        if ($gdImage) {
                            imageavif($gdImage, storage_path('app/public/img/full/' . $recipe->image));
                            $resizeImg = ImageTransformation::image_resize(
                                $gdImage,
                                $imgProperties[0] ?? 0,
                                $imgProperties[1] ?? 0
                            );
                            imageavif($resizeImg, storage_path('app/public/img/thumbnail/' . $recipe->image));
                            // Création d'une miniature
                        }
                        break;

                    case 'png':
                        $imgProperties = getimagesize($request->photoInput->path());
                        $gdImage = imagecreatefrompng($request->photoInput->path());
                        if ($gdImage) {
                            imageavif($gdImage, storage_path('app/public/img/full/' . $recipe->image));
                            $resizeImg = ImageTransformation::image_resize(
                                $gdImage,
                                $imgProperties[0] ?? 0,
                                $imgProperties[1] ?? 0
                            );
                            imageavif($resizeImg, storage_path('app/public/img/thumbnail/' . $recipe->image));
                        }
                        break;
                    case 'avif':
                        $gdImage = imagecreatefromavif($request->photoInput->path());
                        if ($gdImage) {
                            imageavif($gdImage, storage_path('app/public/img/full/' . $recipe->image));
                            $resizeImg = ImageTransformation::image_resize(
                                $gdImage,
                                imagesx($gdImage),
                                imagesy($gdImage)
                            );
                            imageavif($resizeImg, storage_path('app/public/img/thumbnail/' . $recipe->image));
                        }
                        break;
                    default:
                        $imgProperties = getimagesize($request->photoInput->path());
                        $gdImage = imagecreatefromjpeg($request->photoInput->path());
                        if ($gdImage) {
                            imageavif($gdImage, storage_path('app/public/img/full/' . $recipe->image));
                            $resizeImg = ImageTransformation::image_resize(
                                $gdImage,
                                $imgProperties[0] ?? 0,
                                $imgProperties[1] ?? 0
                            );
                            imageavif($resizeImg, storage_path('app/public/img/thumbnail/' . $recipe->image));
                        }
                        break;
                }
                if ($gdImage) {
                    imagedestroy($gdImage);
                }
                if (isset($resizeImg)) {
                    imagedestroy($resizeImg);
                }
            } // Si pas de nouvelle image mais nouveau nom
            elseif ($newName) {
                $newName = $recipe->id . '-' . Str::slug($recipe->name, '-') . '.avif';
                // On renomme l'image de la recette
                Storage::move('public/img/full/' . $oldImageName, 'public/img/full/' . $newName);
                Storage::move(
                    'public/img/thumbnail/' . $oldImageName,
                    'public/img/thumbnail/' . $newName
                );
            }
            $recipe->save();

            DB::commit();

            return true;
        } // Si erreur dans la transaction
        catch (Exception $e) {
            DB::rollback();

            return false;
        }
    }

    public function showRecipe(int $id): Recipe
    {
        return Recipe::select(
            'id',
            'user_id',
            'name',
            'servings',
            'cooking_time as cookingTime',
            'making_time as makingTime',
            'image',
            'score',
            'recipe_type',
            'diets'
        )
                     ->withCount('steps') // Nombre d'étapes possède la recette
                     ->withCount('ingredients') // Nombre d'ingrédients dans la recette
                     ->findOrFail($id);
    }

    public function commentRecipe(RecipeCommentRequest $request, Recipe $recipe): bool
    {
        $user = Auth::user();
        if ($user === null) {
            return false;
        }

        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            RecipeOpinion::updateOrCreate(
                ['user_id' => $user->id, 'recipe_id' => $recipe->id],
                ['score' => $request->score, 'comment' => $request->comment]
            );

            $average = RecipeOpinion::whereBelongsTo($recipe)->avg('score');
            $recipe->score = $average;
            $recipe->save();

            // Validation de la transaction
            DB::commit();

            return true;
        }// Si erreur dans la transaction
        catch (Exception $e) {
            DB::rollback();

            return false;
        }
    }

    public function emptyOpinionRecipe(Recipe $recipe): bool
    {
        $user = Auth::user();
        if ($user === null) {
            return false;
        }

        DB::beginTransaction();
        try {
            // Trouver l'opinion de la recette par l'utilisateur
            $recipeOpinion = $recipe->opinions()->where('user_id', $user->id)->firstOrFail();
            // Réinitialisation du commentaire et de la note de l'avis sur la recette
            $recipeOpinion->score = null;
            $recipeOpinion->comment = null;
            $recipeOpinion->save();
            // Définition de la nouvelle moyenne
            $average = RecipeOpinion::whereBelongsTo($recipe)->avg('score');
            $recipe->score = $average;
            $recipe->save();
            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollback();

            return false;
        }
    }

    /**
     * @return Builder<Recipe>
     */
    public function userIndex(RecipeUserIndexRequest $request): Builder
    {
        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();

        // Début de la requête
        $recipesQuery = Recipe::select('id', 'name', 'score', 'making_time', 'cooking_time', 'image')
                              ->where('name', 'like', "%{$request->name}%")
                              ->where('user_id', '=', $user->id)
                              ->withCount('ingredients')
                              ->withCount('steps');

        // Si on a un type de plat (entrée, plat, dessert,...)
        if ($request->type && (int)$request->type > 0) {
            $recipesQuery = $recipesQuery->where('recipe_type', $request->type);
        }

        // Si on a un filtre sur le type de diet
        if ($request->diet && $request->diet > 0) {
            switch ((int)$request->diet) {
                case 1: // Végétarien
                    $recipesQuery = $recipesQuery->whereJsonContains('diets', Diets::VEGETARIAN->value);
                    break;
                case 2: // Vegan
                    $recipesQuery = $recipesQuery->whereJsonContains('diets', Diets::VEGAN->value);
                    break;
                case 3: // Sans gluten
                    $recipesQuery = $recipesQuery->whereJsonContains('diets', Diets::GLUTEN_FREE->value);
                    break;
                case 4: // Halal
                    $recipesQuery = $recipesQuery->whereJsonContains('diets', Diets::HALAL->value);
                    break;
                case 5: // casher
                    $recipesQuery = $recipesQuery->whereJsonContains('diets', Diets::KOSHER->value);
                    break;
                default:
                    break;
            }
        }

        return $recipesQuery;
    }

    /**
     * @return Builder<Recipe>
     */
    public function noteBookIndex(RecipeNoteBookIndexRequest $request): Builder
    {
        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();

        // Début de la requête
        $recipesQuery = Recipe::select('*')->join('recipe_opinions', 'recipe_opinions.recipe_id', '=', 'recipes.id')
                              ->where('recipes.name', 'like', "%{$request->name}%")
                              ->where('recipe_opinions.user_id', '=', $user->id)
                              ->where('recipe_opinions.is_favorite', true)
                              ->withCount('ingredients')
                              ->withCount('steps');

        // Si on a un type de plat (entrée, plat, dessert,...)
        if ($request->type && in_array($request->type, RecipeTypes::allValues())) {
            $recipesQuery = $recipesQuery->where('recipes.recipe_type', $request->type);
        }

        // Si on a un filtre sur le type de régime
        if ($request->diet && $request->diet > 0) {
            switch ((int)$request->diet) {
                case 1: // Végétarien
                    $recipesQuery = $recipesQuery->whereJsonContains('diets', Diets::VEGETARIAN->value);
                    break;
                case 2: // Vegan
                    $recipesQuery = $recipesQuery->whereJsonContains('diets', Diets::VEGAN->value);
                    break;
                case 3: // Sans gluten
                    $recipesQuery = $recipesQuery->whereJsonContains('diets', Diets::GLUTEN_FREE->value);
                    break;
                case 4: // Halal
                    $recipesQuery = $recipesQuery->whereJsonContains('diets', Diets::HALAL->value);
                    break;
                case 5: // casher
                    $recipesQuery = $recipesQuery->whereJsonContains('diets', Diets::KOSHER->value);
                    break;
                default:
                    break;
            }
        }

        return $recipesQuery;
    }
}
