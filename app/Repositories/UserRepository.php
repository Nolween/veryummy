<?php

namespace App\Repositories;

use App\Http\Requests\User\UserDestroyRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Models\Recipe;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRepository
{


    public function updateUser(UserUpdateRequest $request): bool
    {
        $user = Auth::user();
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


}
