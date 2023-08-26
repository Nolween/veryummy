<?php

namespace App\Repositories;

use App\Http\Requests\User\UserUpdateRequest;
use App\Models\User;
use Exception;
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

}
