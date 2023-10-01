<?php

namespace App\Repositories;

use App\Http\Requests\User\UserBanRequest;
use App\Http\Requests\User\UserDestroyRequest;
use App\Http\Requests\User\UserIndexRequest;
use App\Http\Requests\User\UserModerateRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Models\OpinionReport;
use App\Models\Recipe;
use App\Models\RecipeOpinion;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRepository
{


    public function updateUser(UserUpdateRequest $request): bool
    {
        $user = Auth::user();
        if ($user === null) {
            return false;
        }
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

            return true;
        } // Si erreur dans la transaction
        catch (Exception $e) {
            DB::rollback();
            return false;
        }
    }

    public function destroyUser(UserDestroyRequest $request): bool
    {
        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            $user = Auth::user();
            if ($user === null) {
                return false;
            }
            // Récupération de l'utilisateur
            $userDelete = User::findOrFail($user->id);
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
            $recipesWithoutFavorite = Recipe::destroy($recipesWithoutFavorite->pluck('id')->all());

            // Filtre des recettes qui ont des favoris
            $recipesWithFavorite = $recipesWithFavoriteCount->filter(function ($value) {
                return $value->opinions_count > 0;
            });
            // Si l'utilisateur a des recettes chez d'autres en favoris
            if ($recipesWithFavorite->isNotEmpty()) {
                // Mise à jour de toutes les recettes de l'utilisateur vers le compte d'archives
                $recipesWithFavorite->toQuery()->update(['user_id' => 1]);
            }
            // Suppression de l'utilisateur
            $userDestroy = User::destroy($userDelete->id);
            // Validation de la transaction
            DB::commit();
        } // Si erreur dans la transaction
        catch (QueryException $e) {
            DB::rollback();

            return false;
        }
        return true;
    }


    /**
     * @param int $type
     * @param UserIndexRequest $request
     * @return LengthAwarePaginator<User>
     */
    public function getUsers(int $type, UserIndexRequest $request): LengthAwarePaginator
    {
        // On ne liste que les utilisateurs non admin
        $users = User::where('is_banned', 0)->where('role', '!=', User::ROLE_ADMIN);

        switch ($type) {
            // Les utilisateurs ayant des commentaires signalés (au moins un)
            case 0:
                // Tableau des id de commentaires ayant des signalements
                $reportedOpinions = RecipeOpinion::having('reports_count', '>', 0)
                                                 ->with('reports')
                                                 ->withCount('reports')->pluck('id');

                $users = $users->having('reported_opinions_by_other_count', '>', 0)
                               ->with([
                                   'opinions' => function ($query) use ($reportedOpinions) {
                                       $query->whereIn('id', $reportedOpinions);
                                   },
                               ])
                               ->withCount('reportedOpinionsByOther');
                // Si recherche
                if (!empty($request->search)) {
                    $users->where('name', 'like', "%{$request->search}%");
                }
                break;
            // Tous les utilisateurs
            case 1:
                // Tableau des id de commentaires ayant des signalements
                $reportedOpinions = RecipeOpinion::having('reports_count', '>', 0)
                                                 ->with('reports')
                                                 ->withCount('reports')->pluck('id');

                $users = $users->where('is_banned', 0)
                               ->with([
                                   'opinions' => function ($query) use ($reportedOpinions) {
                                       $query->whereIn('id', $reportedOpinions);
                                   },
                               ])
                               ->withCount('reportedOpinionsByOther');
                // Si recherche
                if (!empty($request->search)) {
                    $users->where('name', 'like', "%{$request->search}%");
                }
                break;
        }

        return $users->orderBy('name')->paginate(20);
    }

    /**
     * @details Bannir un utilisateur
     */
    public function banUser(UserBanRequest $request): bool
    {
        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            // Récupération de l'utilisateur
            $userDelete = User::findOrFail($request->userid);

            /** @var User $userDelete */
            // Si l'utilisateur est admin, erreur
            if ($userDelete->role === 'admin') {
                return false;
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
            // Si l'utilisateur a des recettes chez d'autres en favoris
            if ($recipesWithFavorite->isNotEmpty()) {
                // Mise à jour de toutes les recettes de l'utilisateur vers le compte d'archives
                $recipesWithFavorite->toQuery()->update(['user_id' => 1]);
            }
            // Bannissement de l'utilisateur
            $userDelete->is_banned = true;
            $userDelete->save();

            // Validation de la transaction
            DB::commit();

            return true;
        } // Si erreur dans la transaction
        catch (Exception $e) {
            DB::rollback();

            return false;
        }
    }

    /**
     * @details Modération d'un utilisateur
     */
    public function moderateUser(UserModerateRequest $request): int
    {
        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            // Si destruction du commentaire
            if ($request->destroy == true) {
                // Destruction du commentaire
                RecipeOpinion::destroy($request->opinionid);
                $returnType = 1;
            } // Si suppression des signalements liés à ce commentaire
            else {
                $test = OpinionReport::where('opinion_id', $request->opinionid)->delete();
                $returnType = 2;
            }

            // Validation de la transaction
            DB::commit();

            return $returnType;
        } // Si erreur dans la transaction
        catch (QueryException $e) {
            DB::rollback();

            return 0;
        }
    }

}
