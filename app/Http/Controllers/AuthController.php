<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            Log::info('Début du processus d\'inscription', ['email' => $request->email]);
            
            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
            ]);

            Log::debug('Validation des données d\'inscription réussie');

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            Log::info('Utilisateur créé avec succès', ['user_id' => $user->id]);

            $token = $user->createToken('auth_token')->plainTextToken;

            Log::debug('Token d\'authentification généré pour l\'utilisateur', ['user_id' => $user->id]);

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erreur de validation lors de l\'inscription', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            throw $e;
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'inscription', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Une erreur est survenue lors de l\'inscription'], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            Log::info('Tentative de connexion', ['email' => $request->email]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                Log::warning('Tentative de connexion avec email inconnu', ['email' => $request->email]);
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            if (!Hash::check($request->password, $user->password)) {
                Log::warning('Tentative de connexion avec mot de passe incorrect', ['email' => $request->email, 'user_id' => $user->id]);
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            Log::debug('Authentification réussie', ['user_id' => $user->id]);

            $token = $user->createToken('auth_token')->plainTextToken;

            Log::debug('Token de connexion généré', ['user_id' => $user->id]);

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la connexion', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Une erreur est survenue lors de la connexion'], 500);
        }
    }
}