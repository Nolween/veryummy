<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\CheckCurrentPassword;
use App\Rules\PasswordRepetition;
use App\Rules\UserMailExists;
use App\Rules\UserNameExists;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function edit(Request $request)
    {

        $response = [];

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


        return redirect()->back()->with('userUpdateSuccess', 'Vos informations on été mises à jour!');
    }
}
