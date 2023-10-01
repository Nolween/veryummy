<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UserBanRequest;
use App\Http\Requests\User\UserDestroyRequest;
use App\Http\Requests\User\UserIndexRequest;
use App\Http\Requests\User\UserModerateRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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

        if (Auth::user() === null) {
            abort(403);
        }

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
     * @details Bannir un utilisateur
     */
    public function ban(UserBanRequest $request): RedirectResponse
    {
        if ($this->userRepository->banUser($request)) {
            return redirect("/admin/users/index/$request->typelist")
                ->with('deletionSuccess', "Bannissement de l'utilisateur effectué");
        } else {
            return back()->withErrors(['deletionError' => "Erreur dans le bannissement de l'utilisateur"]);
        }
    }

    /**
     * @details Modérer un utilisateur
     */
    public function moderate(UserModerateRequest $request): RedirectResponse
    {
        $moderateUSer = $this->userRepository->moderateUser($request);
        if ($moderateUSer === 1) {
            return redirect("/admin/users/index/$request->typelist")
                ->with('deletionSuccess', 'Le commentaire a été supprimé');
        }
        if ($moderateUSer === 2) {
            return redirect("/admin/users/index/$request->typelist")
                ->with('deletionSuccess', 'Le commentaire a été supprimé');
        } else {
            return back()->withErrors(['deletionError' => 'Erreur dans la modération du commentaire']);
        }
    }
}
