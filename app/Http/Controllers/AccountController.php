<?php

namespace App\Http\Controllers;

use App\Models\OpinionReport;
use App\Models\Recipe;
use App\Models\RecipeIngredients;
use App\Models\RecipeOpinion;
use App\Models\User;
use App\Rules\CheckCurrentPassword;
use App\Rules\PasswordRepetition;
use App\Rules\UserMailExists;
use App\Rules\UserNameExists;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    /**
     * Affichage des informations de compte
     *
     * @return void
     */
    public function show()
    {
        $response = [];
        // Authentification de l'utilisateur
        $user = Auth::user();
        if (!$user) {
            Auth::logout();
            return redirect('/');
        }
        $response['informations'] = User::select('name', 'email')->where('id', $user->id)->firstOrFail();

        return view('myaccount', $response);
    }

    /**
     * Edition des informations de l'utilisateur
     *
     * @param Request $request
     * @return void
     */
    public function edit(Request $request)
    {

        // Authentification de l'utilisateur
        $user = Auth::user();
        if (!$user) {
            Auth::logout();
            return redirect('/');
        }
        // Validation du formulaire avec les différentes règles
        $request->validate([
            'email' => ['email', new UserMailExists],
            'name' => ['string', new UserNameExists],
            'current-password' => ['string', 'nullable', new CheckCurrentPassword],
            'password' => ['string', 'nullable', new PasswordRepetition],
            'confirmation' => ['string', 'nullable']
        ]);

        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            // Récupération de l'utilisateur
            $userUpdate = User::findOrFail($user->id);
            // Si modification du mail
            if ($request->email !== $userUpdate->email) {
                $userUpdate->email = $request->email;
            }
            // Si modification du pseudo
            if ($request->name !== $userUpdate->name) {
                $userUpdate->name = $request->name;
            }
            // Modification du mot de passe
            if (!empty($request->password)) {
                $userUpdate->password = Hash::make($request->password);
            }
            $userUpdate->save();

            // Validation de la transaction
            DB::commit();
        }
        // Si erreur dans la transaction
        catch (QueryException $e) {
            DB::rollback();
            return redirect('/')->with('userUpdateError', 'Erreur dans la mise à jour du compte');
        }

        return redirect()->back()->with('userUpdateSuccess', 'Vos informations on été mises à jour!');
    }

    /**
     * Suppression du compte de l'utilisateur
     *
     * @param Request $request
     * @return void
     */
    public function delete(Request $request)
    {
        // Authentification de l'utilisateur
        $user = Auth::user();
        if (!$user) {
            Auth::logout();
            return redirect('/');
        }
        // Validation du formulaire avec les différentes règles
        $request->validate([
            'delete-account-password' => ['string', 'nullable', new CheckCurrentPassword],
        ]);

        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            // Récupération de l'utilisateur
            $userDelete = User::findOrFail($user->id);
            // Récupération des recettes de l'utilisateur, avec pour chacune son compte d'opinion en favori
            $recipesWithFavoriteCount = Recipe::whereBelongsTo($userDelete)
                ->withCount(['opinions' => function (Builder $query) {
                    $query->where('is_favorite', '=', true);
                }])
                ->get();
            // Filtre des recettes qui ne sont pas en favori
            $recipesWithoutFavorite = $recipesWithFavoriteCount->filter(function ($value) {
                return $value->opinions_count === 0;
            });
            // Suppression des recettes qui ne sont jamais en favori
            $recipesWithoutFavorite = Recipe::destroy($recipesWithoutFavorite);

            // Filtre des recettes qui ont des favoris
            $recipesWithFavorite = $recipesWithFavoriteCount->filter(function ($value) {
                return $value->opinions_count > 0;
            });
            // Si l'utilisateur a des recettes chez d'autres en favoris
            if ($recipesWithFavorite->isNotEmpty()) {
                // Mise à jour de toutes les recettes de l'utilisateur vers le compte d'archives
                $recipesWithFavorite->toQuery()->update(array('user_id' => 1));
            }
            // Suppression de l'utilisateur
            $userDestroy =  User::destroy($userDelete);
            // Validation de la transaction
            DB::commit();
        }
        // Si erreur dans la transaction
        catch (QueryException $e) {
            DB::rollback();
            return redirect('/')->with('transactionError', 'Erreur dans la suppression du compte');
        }
        // Déconnexion de l'utilisateur
        Auth::logout();
        return redirect('/')->with('userDeletionSuccess', 'Votre compte a bien été supprimé!');
    }

    public function list(int $type, Request $request)
    {

        $response = [];

        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();
        // Si pas d'utilisateur
        if (!$user || $user->role_id !== 1) {
            // Déconnexion de l'utilisateur
            Auth::logout();
            return redirect("/");
        }


        // Champ de recherche
        $request->validate([
            'search' => ['string', 'nullable']
        ]);
        
        $response['search'] = $request->search ?? null;

        switch ($type) {
                // Les utilisateurs ayant des commentaires signalés (au moins un)
            case 0:
                // Tableau des id de commentaires ayant des signalements
                $reportedOpinions = RecipeOpinion::having('reports_count', '>', 0)
                    ->with('reports')
                    ->withCount('reports')->pluck('id');

                $users = User::having('reported_opinions_by_other_count', '>', 0)
                    ->where('is_banned', 0)
                    ->with(['opinions' => function ($query) use ($reportedOpinions) {
                        $query->whereIn('id', $reportedOpinions);
                    }])
                    ->withCount('reportedOpinionsByOther');
                // Si recherche
                if (!empty($request->search)) {
                    $users->where('name', 'like', "%{$request->search}%");
                }
                $response['users'] =  $users->paginate(20);
                break;
                // Tous les utilisateurs
            case 1:
                // Tableau des id de commentaires ayant des signalements
                $reportedOpinions = RecipeOpinion::having('reports_count', '>', 0)
                    ->with('reports')
                    ->withCount('reports')->pluck('id');

                $users = User::where('is_banned', 0)
                    ->with(['opinions' => function ($query) use ($reportedOpinions) {
                        $query->whereIn('id', $reportedOpinions);
                    }])
                    ->withCount('reportedOpinionsByOther');
                // Si recherche
                if (!empty($request->search)) {
                    $users->where('name', 'like', "%{$request->search}%");
                }
                $response['users'] =  $users->paginate(20);
                break;
            default:
        }
        $response['typeList'] = (int)$type;
        // dd( $response['recipes']);

        return view('adminuserslist', $response);
    }


    public function ban(Request $request)
    {
        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();
        // Si pas d'utilisateur
        if (!$user || $user->role_id !== 1) {
            // Déconnexion de l'utilisateur
            Auth::logout();
            return redirect("/");
        }

        // Validation du formulaire
        $request->validate([
            'typelist' => ["integer", "required"],
            'userid' => ["integer", "required"]
        ]);

        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            // Récupération de l'utilisateur
            $userDelete = User::findOrFail($request->userid);

            // Si l'utilisateur est admin, erreur
            if ($userDelete->role_id == 1) {
                return redirect("/admin/users/list/$request->typelist")
                    ->with('deletionError', "Vous ne pouvez pas bannir un administrateur");
            }

            // Récupération des recettes de l'utilisateur, avec pour chacune son compte d'opinion en favori
            $recipesWithFavoriteCount = Recipe::whereBelongsTo($userDelete)
                ->withCount(['opinions' => function (Builder $query) {
                    $query->where('is_favorite', '=', true);
                }])
                ->get();
            // Filtre des recettes qui ne sont pas en favori
            $recipesWithoutFavorite = $recipesWithFavoriteCount->filter(function ($value) {
                return $value->opinions_count === 0;
            });
            // Suppression des recettes qui ne sont jamais en favori
            $recipesWithoutFavorite = Recipe::destroy($recipesWithoutFavorite);

            // Filtre des recettes qui ont des favoris
            $recipesWithFavorite = $recipesWithFavoriteCount->filter(function ($value) {
                return $value->opinions_count > 0;
            });
            // dd($recipesWithFavorite);
            // Si l'utilisateur a des recettes chez d'autres en favoris
            if ($recipesWithFavorite->isNotEmpty()) {
                // Mise à jour de toutes les recettes de l'utilisateur vers le compte d'archives
                $recipesWithFavorite->toQuery()->update(array('user_id' => 1));
            }
            // Banissement de l'utilisateur
            $userDelete->is_banned = true;
            $userDelete->save();

            // Validation de la transaction
            DB::commit();

            return redirect("/admin/users/list/$request->typelist")
                ->with('deletionSuccess', "Bannissement de l'utilisateur effectué");
        }

        // Si erreur dans la transaction
        catch (QueryException $e) {
            DB::rollback();
            return back()->with('deletionError', "Erreur dans le bannissement de l'utilisateur");
        }
    }

    public function moderate(Request $request)
    {

        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();
        // Si pas d'utilisateur
        if (!$user || $user->role_id !== 1) {
            // Déconnexion de l'utilisateur
            Auth::logout();
            return redirect("/");
        }

        // Validation du champ
        $request->validate([
            'opinionid' => ['integer', 'required'],
            'destroy' => ['boolean', 'required'],
            'typelist' => ['integer', 'required'],
        ]);
        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            // Si destruction du commentaire
            if ($request->destroy == true) {
                // Destruction du commentaire
                RecipeOpinion::destroy($request->opinionid);
                $successMessage = "Le commentaire a été supprimé";
            }
            // Si suppression des signalements liés à ce commentaire
            else {
                $test = OpinionReport::where('opinion_id', $request->opinionid)->delete();
                $successMessage = "Les signalements du commentaire on été supprimés";
            }

            // Validation de la transaction
            DB::commit();
            return redirect("/admin/users/list/$request->typelist")
                ->with('deletionSuccess', $successMessage);
        }
        // Si erreur dans la transaction
        catch (QueryException $e) {
            DB::rollback();
            return back()->with('deletionError', 'Erreur dans la modération du commentaire');
        }
    }
}
