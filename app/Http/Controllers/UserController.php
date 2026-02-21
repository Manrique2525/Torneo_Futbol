<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display the user's profile form.
     */
    // UsersController.php
    public function index(Request $request)
    {
        return Inertia::render('Users/Index', [
            'users' => User::query()
                ->when($request->search, function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->when($request->perfil && $request->perfil !== 'todos', function ($query, $perfil) {
                    $query->where('perfil', $perfil);
                })
                ->latest()
                ->paginate(4)
                ->withQueryString(), // mantiene los parámetros en los links de paginación
            'roles' => config('constants'),
            'filters' => $request->only(['search', 'perfil']) // Devolvemos los filtros para que no se borren del input
        ]);
    }


    public function create()
    {
        return Inertia::render('Users/Form', [
            'roles' => config('constants'),
            'isEditing' => false
        ]);
    }

    public function edit(User $user)
    {
        return Inertia::render('Users/Form', [
            'user' => $user,
            'roles' => config('constants'),
            'isEditing' => true
        ]);
    }


    /**
     * Guardar nuevo usuario
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'perfil' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'perfil' => $validated['perfil'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado correctamente');
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'perfil' => ['required', 'string'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'perfil' => $validated['perfil'],
            'password' => $validated['password']
                ? Hash::make($validated['password'])
                : $user->password,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado correctamente');
    }

    /**
     * Eliminar usuario (opcional)
     */
    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuario eliminado correctamente');
    }
}
