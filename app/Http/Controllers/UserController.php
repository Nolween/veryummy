<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UserDestroyRequest;
use App\Http\Requests\User\UserIndexRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Models\OpinionReport;
use App\Models\Recipe;
use App\Models\RecipeOpinion;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Rules\CheckCurrentPassword;
use App\Rules\PasswordRepetition;
use App\Rules\UserMailExists;
use App\Rules\UserNameExists;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    /**
     * @details Affichage des informations de compte
     */
    public function edit(): View|RedirectResponse
    {
        $response = [];

        $response['informations'] = User::select('name', 'email')->where('id', Auth::user()->id)->firstOrFail();

        return view('myaccount', $response);
    }

    /**
     * @details Edition des informations de l'utilisateur
     */
    public function update(UserUpdateRequest $request): RedirectResponse
    {
        if ($this->userRepository->updateUser($request)) {
            return redirect()->back()->with('userUpdateSuccess', 'Vos informations on été mises à jour!');
        } else {
            return redirect('/')->withErrors(['userUpdateError' => 'Erreur dans la mise à jour du compte']);
        }
    }

    /**
     * @details Suppression du compte de l'utilisateur
     */
    public function destroy(UserDestroyRequest $request): RedirectResponse
    {
        if ($this->userRepository->destroyUser($request)) {
            // Déconnexion de l'utilisateur
            Auth::logout();
            return redirect('/')->with(['userDeletionSuccess', 'Votre compte a bien été supprimé!']);
        } else {
            return redirect('/')->withErrors(['transactionError' => 'Erreur dans la suppression du compte']);
        }
    }

    /**
     * @details Affichage de la liste des utilisateurs
     */
    public function index(int $type, UserIndexRequest $request): View|RedirectResponse
    {
        $response = [
            'typeList' => $type,
            'users'    => $this->userRepository->getUsers($type, $request),
            'search'   => $request->search ?? null,
        ];

        return view('adminuserslist', $response);
    }

    /**
     * Bannir un utilisateur
     */
    public function ban(Request $request): RedirectResponse
    {
        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();
        // Si pas d'utilisateur
        if (!$user || $user->role !== 'admin' || $user->is_banned == true) {
            // Déconnexion de l'utilisateur
            Auth::logout();

            return redirect('/')->withErrors(['badUser' => 'Utilisateur introuvable']);
        }

        // Validation du formulaire
        $request->validate([
            'typelist' => ['integer', 'required', 'min:0', 'max:1'],
            'userid'   => ['integer', 'required', 'exists:users,id'],
        ]);

        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            // Récupération de l'utilisateur
            $userDelete = User::findOrFail($request->userid);

            /** @var User $userDelete */
            // Si l'utilisateur est admin, erreur
            if ($userDelete->role === 'admin') {
                return redirect("/admin/users/list/$request->typelist")
                    ->withErrors(['deletionError' => 'Vous ne pouvez pas bannir un administrateur']);
            }

            // Récupération des recettes de l'utilisateur, avec pour chacune son compte d'opinion en favori
            $recipesWithFavoriteCount = Recipe::whereBelongsTo($userDelete)
                                              ->withCount([
                                                  'opinions' => function (Builder $query) {
                                                      $query->where('is_favorite', '=', true);
                                                  },
                                              ])
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
                $recipesWithFavorite->toQuery()->update(['user_id' => 1]);
            }
            // Banissement de l'utilisateur
            $userDelete->is_banned = true;
            $userDelete->save();

            // Validation de la transaction
            DB::commit();

            return redirect("/admin/users/list/$request->typelist")
                ->with('deletionSuccess', "Bannissement de l'utilisateur effectué");
        } // Si erreur dans la transaction
        catch (QueryException $e) {
            DB::rollback();

            return back()->withErrors(['deletionError' => "Erreur dans le bannissement de l'utilisateur"]);
        }
    }

    /**
     * Modérer un utilisateur
     */
    public function moderate(Request $request): RedirectResponse
    {
        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();
        // Si pas d'utilisateur
        if (!$user || $user->role !== 'admin' || $user->is_banned == true) {
            // Déconnexion de l'utilisateur
            Auth::logout();

            return redirect('/')->withErrors(['badUser' => 'Utilisateur introuvable']);
        }

        // Validation du champ
        $request->validate([
            'opinionid' => ['integer', 'required', 'exists:recipe_opinions,id'],
            'destroy'   => ['boolean', 'required'],
            'typelist'  => ['integer', 'required', 'min:0', 'max:1'],
        ]);
        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            // Si destruction du commentaire
            if ($request->destroy == true) {
                // Destruction du commentaire
                RecipeOpinion::destroy($request->opinionid);
                $successMessage = 'Le commentaire a été supprimé';
            } // Si suppression des signalements liés à ce commentaire
            else {
                $test = OpinionReport::where('opinion_id', $request->opinionid)->delete();
                $successMessage = 'Les signalements du commentaire on été supprimés';
            }

            // Validation de la transaction
            DB::commit();

            return redirect("/admin/users/list/$request->typelist")
                ->with('deletionSuccess', $successMessage);
        } // Si erreur dans la transaction
        catch (QueryException $e) {
            DB::rollback();

            return back()->withErrors(['deletionError' => 'Erreur dans la modération du commentaire']);
        }
    }
}
