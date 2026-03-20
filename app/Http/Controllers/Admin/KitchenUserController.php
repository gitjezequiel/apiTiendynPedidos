<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class KitchenUserController extends Controller
{
    public function index()
    {
        $restaurant = auth()->user()->restaurants()->first();

        $kitchenUsers = User::where('role', 'kitchen')
            ->where('restaurant_id', $restaurant->id)
            ->orderBy('name')
            ->get();

        return view('admin.kitchen.index', compact('restaurant', 'kitchenUsers'));
    }

    public function store(Request $request)
    {
        $restaurant = auth()->user()->restaurants()->first();

        $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'name.required'     => 'El nombre es obligatorio.',
            'email.required'    => 'El correo es obligatorio.',
            'email.unique'      => 'Ese correo ya está en uso.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min'      => 'La contraseña debe tener al menos 6 caracteres.',
        ]);

        User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'role'          => 'kitchen',
            'restaurant_id' => $restaurant->id,
        ]);

        return back()->with('success', 'Usuario de cocina creado correctamente.');
    }

    public function destroy(User $kitchenUser)
    {
        $restaurant = auth()->user()->restaurants()->first();

        // Solo puede eliminar usuarios de cocina de su propio restaurante
        if ($kitchenUser->role !== 'kitchen' || $kitchenUser->restaurant_id !== $restaurant->id) {
            abort(403);
        }

        $kitchenUser->delete();

        return back()->with('success', 'Usuario eliminado.');
    }
}
