<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log; // Ajout pour les logs

class AuthController extends Controller
{
    public function register(Request $request) {
        Log::info('Tentative d\'inscription', [
            'name' => $request->name,
            'email' => $request->email,
            'ip' => $request->ip()
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            Log::info('Utilisateur enregistré avec succès', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return $user;

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'inscription', [
                'error' => $e->getMessage(),
                'email' => $request->email
            ]);
            return response(['message' => 'Erreur lors de l\'inscription'], 500);
        }
    }

    public function login(Request $request) {
        Log::info('Tentative de connexion', [
            'email' => $request->email,
            'ip' => $request->ip()
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            Log::warning('Tentative de connexion avec email inconnu', [
                'email' => $request->email
            ]);
            return response(['message' => 'Invalid credentials'], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            Log::warning('Tentative de connexion avec mot de passe incorrect', [
                'email' => $request->email,
                'user_id' => $user->id
            ]);
            return response(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        Log::info('Connexion réussie', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);

    }
}