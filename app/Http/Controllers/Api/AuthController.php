<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $storageService;

    public function __construct(\App\Services\FirebaseStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:150|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:owner,customer',
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $this->formatValidationErrors($validator)
            ], 422);
        }

        $profileImage = null;
        if ($request->hasFile('image')) {
            $profileImage = $this->storageService->upload($request->file('image'), 'profiles');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'profile_image' => $profileImage,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        $restaurant = $user->restaurants()->first();
        $user->restaurant_id = $restaurant ? $restaurant->id : null;

        return response()->json([
            'status' => 'success',
            'message' => 'Usuario registrado exitosamente',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $this->formatValidationErrors($validator)
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $restaurant = $user->restaurants()->first();
        $user->restaurant_id = $restaurant ? $restaurant->id : null;

        return response()->json([
            'status' => 'success',
            'message' => 'Inicio de sesión exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'user' => $request->user()
        ]);
    }

    public function stats(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'status'          => 'success',
            'orders_count'    => $user->orders()->count(),
            'favorites_count' => $user->follows()->count(),
            'reviews_count'   => $user->ratings()->count(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:100',
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'profile_image' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $this->formatValidationErrors($validator)
            ], 422);
        }

        $data = $request->only(['name', 'phone', 'profile_image']);

        if ($request->hasFile('image')) {
            $data['profile_image'] = $this->storageService->upload(
                $request->file('image'), 
                'profiles', 
                $user->profile_image
            );
        }

        $user->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Perfil actualizado correctamente',
            'user' => $user
        ]);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error de validación',
                'errors'  => $this->formatValidationErrors($validator),
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'La contraseña actual es incorrecta.',
            ], 422);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['status' => 'success', 'message' => 'Contraseña actualizada correctamente.']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

    protected function formatValidationErrors($validator)
    {
        $errors = $validator->errors()->all();
        return $errors[0] ?? 'Error de validación';
    }
}
