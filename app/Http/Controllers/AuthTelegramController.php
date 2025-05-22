<?php

namespace App\Http\Controllers;

use App\Models\TelegramAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AuthTelegramController extends Controller
{
    public function showPhoneForm()
    {
        Log::info('[Telegram] Affichage du formulaire de numéro de téléphone');
        return view('telegram.login');
    }

    public function sendCode(Request $request)
    {
        $phone = $request->input('phone');
        Log::info('[Telegram] Envoi du code demandé', ['phone' => $phone]);

        $script = base_path('app/Scripts/send_code.py');
        $command = ['/home/abdalbensaid/venv/bin/python3', $script, $phone];
        Log::debug('[Telegram] Commande exécutée : ' . implode(' ', $command));

        $process = new Process($command);

        try {
            $process->mustRun();

            $hash = trim($process->getOutput());
            Log::info('[Telegram] Code envoyé avec succès', ['hash' => $hash]);

            session([
                'phone' => $phone,
                'phone_code_hash' => $hash
            ]);

            return redirect('/telegram/verify');
        } catch (ProcessFailedException $e) {
            Log::error('[Telegram] Échec envoi du code', [
                'message' => $e->getMessage(),
                'stderr' => $process->getErrorOutput()
            ]);
            return back()->with('error', 'Échec de l’envoi du code.');
        }
    }

    public function showCodeForm()
    {
        $phone = session('phone');
        Log::info('[Telegram] Affichage du formulaire de vérification', ['phone' => $phone]);

        return view('telegram.verify', compact('phone'));
    }

    public function verifyCode(Request $request)
    {
        $phone = session('phone');
        $code = $request->input('code');
        $hash = session('phone_code_hash');

        $script = base_path('app/Scripts/verify_code.py');

        // Récupération de l'utilisateur connecté
        $user = Auth::user();

        if (!$user) {
            Log::warning('[Telegram] Aucun utilisateur connecté lors de la vérification');
            return back()->with('error', 'Aucun utilisateur connecté.');
        }

        Log::info('[Telegram] Vérification du code reçu', [
            'phone' => $phone,
            'code' => $code,
            'hash' => $hash,
            'user_id' => $user->id
        ]);

        $process = new Process([
            '/home/abdalbensaid/venv/bin/python3',
            $script,
            $phone,
            $code,
            $hash
        ]);

        Log::debug('[Telegram] Commande exécutée pour vérification : ' . $process->getCommandLine());

        try {
            $process->mustRun();
            $output = $process->getOutput();

            Log::info('[Telegram] Code vérifié avec succès', ['output' => trim($output)]);

            // Nom du fichier de session Telegram (ex: 2250151995872.session)
            $sessionFile = str_replace(['+', ' '], '', $phone) . '.session';

            // Log du contenu avant enregistrement
            Log::debug('[Telegram] Données à enregistrer dans la base telegram_accounts', [
                'phone' => $phone,
                'session_file' => $sessionFile,
                'authorized' => true,
                'user_id' => $user->id
            ]);

            // Insertion/MAJ du compte dans la base
            TelegramAccount::updateOrCreate(
                [
                    'phone' => $phone,
                    'user_id' => $user->id
                ],
                [
                    'session_file' => $sessionFile,
                    'authorized' => true,
                    'user_id' => $user->id // ← essentiel pour éviter l’erreur SQL
                ]
            );

            return redirect('/scraper-form')->with('success', 'Connexion réussie.');

        } catch (ProcessFailedException $e) {
            Log::error('[Telegram] Échec vérification code', [
                'message' => $e->getMessage(),
                'output' => $process->getOutput(),
                'error_output' => $process->getErrorOutput()
            ]);

            return back()->with('error', 'Échec de la connexion.');
        }
    }


    public function listAccounts()
    {
        $user = Auth::user();
        if (!$user) {
            Log::warning('[Telegram] Tentative d\'accès à la liste de sessions sans authentification');
            return redirect('/login');
        }

        Log::info('[Telegram] Récupération des comptes Telegram de l\'utilisateur', ['user_id' => $user->id]);

        $accounts = $user->telegramAccounts()->where('authorized', true)->get();
        return view('telegram.sessions', compact('accounts'));
    }
}
