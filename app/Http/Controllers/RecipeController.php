<?php

namespace App\Http\Controllers;

use App\Helpers\ImageTransformation;
use App\Http\Requests\Recipe\RecipeAdminIndexRequest;
use App\Http\Requests\Recipe\RecipeExplorationRequest;
use App\Mail\RefusedRecipe;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeIngredients;
use App\Models\RecipeOpinion;
use App\Models\RecipeStep;
use App\Models\RecipeType;
use App\Models\Unit;
use App\Models\User;
use App\Repositories\RecipeRepository;
use App\Rules\DietExists;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Support\Str;
use Illuminate\View\View;

use function imageavif;

class RecipeController extends Controller
{

    private RecipeRepository $recipeRepository;

    public function __construct(RecipeRepository $recipeRepository)
    {
        $this->recipeRepository = $recipeRepository;
    }

    /**
     * @details Page d'accueil
     */
    public function welcomeIndex(): View
    {
        $response = $this->recipeRepository->getWelcomeIndex();

        return view('welcome', $response);
    }


    /**
     * @details Listes des recettes d'exploration
     */
    public function explorationIndex(RecipeExplorationRequest $request): View
    {
        $response = $this->recipeRepository->getExplorationIndex($request);


        return view('exploration', $response);
    }




    /**
     * Page d'accueil
     */
    public function adminIndex(int $type, RecipeAdminIndexRequest $request): View|RedirectResponse
    {
        $response = $this->recipeRepository->getAdminIndex($request, $type);

        return view('adminrecipeslist', $response);
    }

    /**
     * Page d'accueil
     */
    public function allow(Request $request): RedirectResponse
    {
        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();
        // Si pas d'utilisateur
        if (!$user || $user->role !== 'admin' || $user->is_banned == true) {
            // Déconnexion de l'utilisateur
            Auth::logout();

            return redirect('/')->withErrors(['badUser' => 'Utilisateur non reconnu']);
        }
        $response = [];
        // Validation du formulaire avec les différentes règles
        $request->validate([
            'recipeid' => ['integer', 'required', 'exists:recipes,id'],
            'allow'    => ['boolean', 'required'],
            'typeList' => ['integer', 'required'],
        ]);

        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            // Récupération de la recette par son Id
            $recipe = Recipe::where('id', (int)$request->recipeid)->with('user')->first();
            // Si pas de recette trouvée, erreur
            if (!$recipe) {
                return back()->withErrors(['recipeAllowError' => 'Aucun ingrédient trouvé']);
            }

            // Si on ignore les signalements
            if ($request->allow == true) {
                RecipeOpinion::where('recipe_id', (int)$request->recipeid)->where('is_reported', true)->update(
                    ['is_reported' => false]
                );
            } // Si on supprime la recette
            elseif ($request->allow == false) {
                Recipe::destroy($recipe->id);
            }

            // Envoi de mail de désactivation à la personne ayant proposé la recette
            $informations = ['recipe' => $recipe->name, 'url' => URL::to('/')];
            // dd($informations);
            if ($request->allow == false) {
                Mail::to($user->email)->send(new RefusedRecipe($informations));
            }

            // Validation de la transaction
            DB::commit();

            return redirect("/admin/recipes/list/$request->typeList")->with(
                'recipeAllowSuccess',
                'La recette a été modérée'
            );
        } // Si erreur dans la transaction
        catch (QueryException $e) {
            DB::rollback();

            return back()->withErrors(['recipeAllowError' => 'Erreur dans la modération de la recette']);
        }
    }

    /**
     * Page d'accueil
     */
    public function status(Request $request): RedirectResponse
    {
        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();
        // Si pas d'utilisateur
        if (!$user || $user->is_banned == true) {
            // Déconnexion de l'utilisateur
            Auth::logout();

            return redirect('/')->withErrors(['badUser' => 'Utilisateur non reconnu']);
        }
        // Validation du formulaire
        $request->validate([
            'is_favorite' => ['boolean', 'nullable'],
            'is_reported' => ['boolean', 'nullable'],
            'recipeid'    => ['integer', 'required', 'exists:recipes,id'],
        ]);
        // return dd($test);

        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            // La recette existe t-elle?
            $recipe = Recipe::findOrFail($request->recipeid);

            RecipeOpinion::updateOrCreate(
                ['user_id' => $user->id, 'recipe_id' => $request->recipeid],
                ['is_favorite' => $request->is_favorite, 'is_reported' => $request->is_reported]
            );

            // Définition du message de retour
            if ($request->is_reported == null) {
                $message = $request->is_favorite == 1 ? 'La recette a été ajoutée à vos favoris' : 'La recette a été retirée de vos favoris';
            } elseif ($request->is_favorite == null) {
                $message = $request->is_reported == 1 ? 'La recette a été signalée' : 'La recette a été retirée des signalements';
            } else {
                $message = 'Erreur inconnue';
            }

            // Validation de la transaction
            DB::commit();

            return back()->with('statusSuccess', $message);
        } // Si erreur dans la transaction
        catch (ItemNotFoundException $e) {
            DB::rollback();

            return back()->withErrors(['statusError' => 'Recette introuvable']);
        } // Si erreur dans la transaction
        catch (Exception $e) {
            DB::rollback();

            return back()->withErrors(['statusError' => 'Erreur dans la mise à jour du statut']);
        }
    }

    /**
     * Page de nouvelle recette
     */
    public function new(): View|RedirectResponse
    {
        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();

        // Si pas d'utilisateur
        if (!$user || $user->is_banned == true) {
            // Déconnexion de l'utilisateur
            Auth::logout();

            return redirect('/')->withErrors(['badUser' => 'Utilisateur non reconnu']);
        }

        $response = [];

        // Récupération de tous les ingrédients
        $response['ingredients'] = Ingredient::pluck('name', 'id');
        // Récupération des unités de mesures
        $response['units'] = Unit::all();
        // Récupération des différents types de recette
        $response['types'] = RecipeType::all();

        return view('recipenew', $response);
    }

    /**
     * Création de nouvelle recette
     */
    public function create(Request $request): RedirectResponse
    {
        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();

        // Si pas d'utilisateur
        if (!$user || $user->is_banned == true) {
            // Déconnexion de l'utilisateur
            Auth::logout();

            return redirect('/')->withErrors(['badUser' => 'Utilisateur non reconnu']);
        }

        // Validation du formulaire
        $request->validate([
            'nom'                  => ['string', 'required', 'min:2'],
            'photoInput'           => 'nullable|mimes:jpg,png,jpeg,gif,svg,avif,webp',
            'preparation'          => ['integer', 'required', 'min:0', 'max:1000'],
            'cuisson'              => ['integer', 'nullable', 'min:0', 'max:1000'],
            'parts'                => ['integer', 'required', 'min:0', 'max:1000'],
            'stepCount'            => ['integer', 'nullable'],
            'type'                 => ['integer', 'exists:recipe_types,id', 'required'],
            'ingredientCount'      => ['integer', 'nullable'],
            '*.ingredientId'       => ['integer', 'exists:ingredients,id', 'nullable'],
            '*.ingredientName'     => ['string', 'nullable'],
            '*.ingredientUnit'     => ['numeric', 'exists:units,id', 'nullable'],
            '*.ingredientQuantity' => ['numeric', 'nullable'],
            '*.stepDescription'    => ['string', 'nullable'],
        ]);

        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            $newRecipe = new Recipe;
            $newRecipe->name = $request->nom;
            $newRecipe->cooking_time = $request->cuisson;
            $newRecipe->making_time = $request->preparation;
            $newRecipe->servings = $request->parts;
            $newRecipe->is_accepted = true;
            $newRecipe->recipe_type_id = $request->type;
            $newRecipe->user_id = $user->id;
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
                    $unit = Unit::where('id', $ingredient['ingredientUnit'])->first();
                    // Si pas d'unité de mesure trouvé, erreur
                    if (!$unit) {
                        return back()->withErrors(
                            ['unitError' => 'Unité de mesure non trouvé: ' . $ingredient['ingredientUnit']]
                        );
                    }
                    $newRecipeIngredient->unit_id = $ingredient['ingredientUnit'];
                    $ingr = Ingredient::where('id', $ingredient['ingredientId'])->first();
                    // Si pas d'ingrédient  trouvé, erreur
                    if (!$ingr) {
                        return back()->withErrors(['ingredientError' => 'Ingrédient non trouvé']);
                    }
                    $newRecipeIngredient->ingredient_id = $ingredient['ingredientId'];
                    $newRecipeIngredient->quantity = $ingredient['ingredientQuantity'];
                    $newRecipeIngredient->save();
                }
            }
            //? Définition des différentes catégories de la recette
            // Tableau des compatibilités de la recette
            $compatible = [
                'vegan_compatible'       => 0,
                'vegetarian_compatible'  => 0,
                'gluten_free_compatible' => 0,
                'halal_compatible'       => 0,
                'kosher_compatible'      => 0,
            ];
            // Parcours des ingrédients ajoutés
            foreach ($request->ingredients as $ingredient) {
                if (!empty($ingredient['ingredientId'])) {
                    // Récupération de l'ingrédient
                    $ingredientCompatible = Ingredient::where('id', $ingredient['ingredientId'])->firstOrFail();
                    // Si l'ingrédient est compatible avec le régime
                    $compatible['vegan_compatible'] = $ingredientCompatible->vegan_compatible == true ? $compatible['vegan_compatible'] : $compatible['vegan_compatible'] + 1;
                    $compatible['vegetarian_compatible'] = $ingredientCompatible->vegetarian_compatible == true ? $compatible['vegetarian_compatible'] : $compatible['vegetarian_compatible'] + 1;
                    $compatible['gluten_free_compatible'] = $ingredientCompatible->gluten_free_compatible == true ? $compatible['gluten_free_compatible'] : $compatible['gluten_free_compatible'] + 1;
                    $compatible['halal_compatible'] = $ingredientCompatible->halal_compatible == true ? $compatible['halal_compatible'] : $compatible['halal_compatible'] + 1;
                    $compatible['kosher_compatible'] = $ingredientCompatible->kosher_compatible == true ? $compatible['kosher_compatible'] : $compatible['kosher_compatible'] + 1;
                }
            }
            // Parcours des résultats de compatibilité
            $newRecipe->vegan_compatible = $compatible['vegan_compatible'] == 0 ? true : false;
            $newRecipe->vegetarian_compatible = $compatible['vegetarian_compatible'] == 0 ? true : false;
            $newRecipe->gluten_free_compatible = $compatible['gluten_free_compatible'] == 0 ? true : false;
            $newRecipe->halal_compatible = $compatible['halal_compatible'] == 0 ? true : false;
            $newRecipe->kosher_compatible = $compatible['kosher_compatible'] == 0 ? true : false;

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

            return redirect('/my-recipes')->with('newSuccess', 'Recette crée avec succès!');
        } // Si erreur dans la transaction
        catch (Exception $e) {
            DB::rollback();

            return redirect('/recipe/new')->withErrors(['newError' => $e->getMessage()]);
        }
    }

    /**
     * Page de modification de recette
     */
    public function edit(int $id): View|RedirectResponse
    {
        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();

        // Si pas d'utilisateur
        if (!$user || $user->is_banned == true) {
            // Déconnexion de l'utilisateur
            Auth::logout();

            return redirect('/')->withErrors(['badUser' => 'Utilisateur non reconnu']);
        }

        $response = [];

        // Récupération de tous les ingrédients
        $response['ingredientsList'] = Ingredient::pluck('name', 'id');
        // Récupération des unités de mesures
        $response['units'] = Unit::all();
        // Récupération des différents types de recette
        $response['types'] = RecipeType::all();

        // Récupération de la recette
        $recipe = Recipe::where('id', $id)->with('ingredients')->with('steps')->first();
        // L'utilisateur est-il propriétaire de la recette ou administrateur?
        if (!$recipe || ($recipe->user_id !== $user->id && $user->role !== 'admin')) {
            return redirect('/')->withErrors(['statusError' => 'Recette non trouvée']);
        }

        $response['recipe'] = $recipe;

        return view('recipeedit', $response);
    }

    /**
     * Modification de recette
     */
    public function update(Request $request): RedirectResponse
    {
        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();

        // Si pas d'utilisateur
        if (!$user || $user->is_banned == true) {
            // Déconnexion de l'utilisateur
            Auth::logout();

            return redirect('/')->withErrors(['badUser' => 'Utilisateur non reconnu']);
        }

        // Validation du formulaire
        $request->validate([
            'recipeid'             => ['integer', 'required', 'exists:recipes,id'],
            'nom'                  => ['string', 'required', 'min:2'],
            'photoInput'           => 'nullable|mimes:jpg,png,jpeg,gif,svg,avif,webp',
            'preparation'          => ['integer', 'required', 'min:0', 'max:1000'],
            'cuisson'              => ['integer', 'nullable', 'min:0', 'max:1000'],
            'parts'                => ['integer', 'required', 'min:0', 'max:1000'],
            'stepCount'            => ['integer', 'nullable'],
            'type'                 => ['integer', 'exists:recipe_types,id', 'required'],
            'ingredientCount'      => ['integer', 'nullable'],
            '*.ingredientId'       => ['integer', 'exists:ingredients,id', 'nullable'],
            '*.ingredientName'     => ['string', 'nullable'],
            '*.ingredientUnit'     => ['numeric', 'exists:units,id', 'nullable'],
            '*.ingredientQuantity' => ['numeric', 'nullable'],
            '*.stepDescription'    => ['string', 'nullable'],
        ]);

        // La recette existe t-elle et appartient-elle à l'utilisateur?
        $recipe = Recipe::where('id', $request->recipeid)->first();
        if (!$recipe || $recipe->user_id !== $user->id) {
            return redirect('/recipe/edit/' . $request->recipeid)->withErrors(['editError' => 'Recette introuvable']);
        }

        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            $newName = $recipe->name !== $request->nom;
            $oldImageName = $recipe->image;
            $recipe->name = $request->nom;
            $recipe->recipe_type_id = $request->type;
            $recipe->cooking_time = $request->cuisson;
            $recipe->making_time = $request->preparation;
            $recipe->servings = $request->parts;
            $recipe->recipe_type_id = $request->type;
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
                    $unit = Unit::where('id', $ingredient['ingredientUnit'])->firstOrFail();
                    $newRecipeIngredient->unit_id = $ingredient['ingredientUnit'];
                    $ingr = Ingredient::where('id', $ingredient['ingredientId'])->first();
                    // Si pas d'ingrédient  trouvé, erreur
                    if (!$ingr) {
                        return back()->withErrors(['ingredientError' => 'Ingrédient non trouvé']);
                    }
                    $newRecipeIngredient->quantity = $ingredient['ingredientQuantity'];
                    $newRecipeIngredient->ingredient_id = $ingredient['ingredientId'];
                    $newRecipeIngredient->save();
                }
            }

            //? Définition des différentes catégories de la recette
            // Tableau des compatibilités de la recette
            $compatible = [
                'vegan_compatible'       => 0,
                'vegetarian_compatible'  => 0,
                'gluten_free_compatible' => 0,
                'halal_compatible'       => 0,
                'kosher_compatible'      => 0,
            ];
            // Parcours des ingrédients ajoutés
            foreach ($request->ingredients as $ingredient) {
                if (!empty($ingredient['ingredientId'])) {
                    // Récupération de l'ingrédient
                    $ingredientCompatible = Ingredient::where('id', $ingredient['ingredientId'])->firstOrFail();
                    // Si l'ingrédient est compatible avec le régime
                    $compatible['vegan_compatible'] = $ingredientCompatible->vegan_compatible == true ? $compatible['vegan_compatible'] : $compatible['vegan_compatible'] + 1;
                    $compatible['vegetarian_compatible'] = $ingredientCompatible->vegetarian_compatible == true ? $compatible['vegetarian_compatible'] : $compatible['vegetarian_compatible'] + 1;
                    $compatible['gluten_free_compatible'] = $ingredientCompatible->gluten_free_compatible == true ? $compatible['gluten_free_compatible'] : $compatible['gluten_free_compatible'] + 1;
                    $compatible['halal_compatible'] = $ingredientCompatible->halal_compatible == true ? $compatible['halal_compatible'] : $compatible['halal_compatible'] + 1;
                    $compatible['kosher_compatible'] = $ingredientCompatible->kosher_compatible == true ? $compatible['kosher_compatible'] : $compatible['kosher_compatible'] + 1;
                }
            }
            // Parcours des résultats de compatibilité
            $recipe->vegan_compatible = $compatible['vegan_compatible'] == 0 ? true : false;
            $recipe->vegetarian_compatible = $compatible['vegetarian_compatible'] == 0 ? true : false;
            $recipe->gluten_free_compatible = $compatible['gluten_free_compatible'] == 0 ? true : false;
            $recipe->halal_compatible = $compatible['halal_compatible'] == 0 ? true : false;
            $recipe->kosher_compatible = $compatible['kosher_compatible'] == 0 ? true : false;

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

            return redirect('/my-recipes')->with('updateSuccess', 'Recette mise à jour avec succès!');
        } // Si erreur dans la transaction
        catch (QueryException $e) {
            DB::rollback();

            return redirect('/recipe/new')->withErrors(['updaterror' => 'Erreur dans la mise à jour de la recette']);
        }
    }
}
