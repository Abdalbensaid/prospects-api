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
        $start = microtime(true); // pour mesurer la durée

        Log::info('📩 [Register] Début de l\'inscription', ['email' => $request->email]);

        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
            ]);
            Log::debug('✅ [Register] Données validées');

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);
            Log::info('👤 [Register] Utilisateur créé', ['user_id' => $user->id]);

            $token = $user->createToken('auth_token')->plainTextToken;
            Log::debug('🔐 [Register] Token généré', ['user_id' => $user->id]);

            $duration = round((microtime(true) - $start) * 1000, 2);
            Log::info('✅ [Register] Succès en '.$duration.'ms');

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ [Register] Erreur validation', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            throw $e;

        } catch (\Exception $e) {
            Log::error('❌ [Register] Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Une erreur est survenue lors de l\'inscription'], 500);
        }
    }

    public function login(Request $request)
    {
        $start = microtime(true);

        Log::info('🔐 [Login] Tentative de connexion', ['email' => $request->email]);

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                Log::warning('⚠️ [Login] Email inconnu', ['email' => $request->email]);
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            if (!Hash::check($request->password, $user->password)) {
                Log::warning('Mot de passe incorrect');
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            // ✅ Ajout important ici
            auth()->login($user);

            $token = $user->createToken('auth_token')->plainTextToken;

            Log::debug('🔐 [Login] Token généré', ['user_id' => $user->id]);

            $duration = round((microtime(true) - $start) * 1000, 2);
            Log::info('✅ [Login] Connexion terminée en '.$duration.'ms', ['user_id' => $user->id]);

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ]);

        } catch (\Exception $e) {
            Log::error('❌ [Login] Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Une erreur est survenue lors de la connexion'], 500);
        }
    }
}
