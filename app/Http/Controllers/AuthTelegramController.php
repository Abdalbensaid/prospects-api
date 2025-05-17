<?php

namespace App\Http\Controllers;
use App\Models\TelegramAccount;
use Illuminate\Http\Request; // Pour gérer les données entrantes depuis les formulaires
use Illuminate\Support\Facades\Log; // Pour écrire dans les logs Laravel
use Symfony\Component\Process\Process; // Pour exécuter un script externe (ici Python)
use Symfony\Component\Process\Exception\ProcessFailedException; // Pour gérer les erreurs si le script échoue

class AuthTelegramController extends Controller
{
    // Affiche le formulaire de saisie du numéro de téléphone (étape 1)
    public function showPhoneForm()
    {
        return view('telegram.login'); // Va chercher resources/views/telegram/login.blade.php
    }

    // Envoie le code Telegram au numéro saisi (via script Python send_code.py)
    public function sendCode(Request $request)
    {
        $phone = $request->input('phone'); // Récupère le numéro depuis le formulaire

        $script = base_path('app/Scripts/send_code.py'); // Chemin complet vers le script Python

        // Crée un processus pour exécuter le script avec l'interpréteur Python + le numéro
        $process = new Process([
            '/home/abdalbensaid/venv/bin/python3', // Python depuis ton venv
            $script,
            $phone
        ]);

        try {
            $process->mustRun(); // Exécute le script et lance une exception si ça échoue
            $output = $process->getOutput(); // Récupère la sortie du script
            Log::debug('Code envoyé : ' . $output); // Écrit dans les logs Laravel pour debug

            // Redirige vers le formulaire de vérification avec le numéro conservé en session
            return redirect('/telegram/verify')->with('phone', $phone);

        } catch (ProcessFailedException $e) {
            Log::error('Erreur envoi code : ' . $e->getMessage()); // Log l'erreur exacte
            return back()->with('error', 'Échec de l’envoi du code.'); // Retourne à la page précédente avec message
        }
    }

    // Affiche le formulaire pour entrer le code reçu (étape 2)
    public function showCodeForm()
    {
        $phone = session('phone'); // Récupère le numéro de la session (envoyé depuis sendCode)
        return view('telegram.verify', compact('phone')); // Affiche la vue avec le numéro
    }

    // Vérifie le code entré par l’utilisateur (via script Python verify_code.py)
    public function verifyCode(Request $request)
    {
        $phone = $request->input('phone'); // Numéro saisi (masqué) dans le formulaire
        $code = $request->input('code');   // Code reçu par Telegram et saisi par l’utilisateur

        $script = base_path('app/Scripts/verify_code.py'); // Chemin vers le script Python

        // Crée un processus pour exécuter le script Python avec le numéro + code
        $process = new Process([
            '/home/abdalbensaid/venv/bin/python3',
            $script,
            $phone,
            $code
        ]);

        try {
            $process->mustRun(); // Lance le script et vérifie que tout se passe bien
            // Génère le nom du fichier de session
            $sessionFile = str_replace(['+', ' '], '', $phone) . '.session';

            // Enregistre ou met à jour le compte dans la base
            TelegramAccount::updateOrCreate(
                ['phone' => $phone],
                [
                    'session_file' => $sessionFile,
                    'authorized' => true
                ]
            );

            $output = $process->getOutput(); // Récupère le message de succès
            Log::debug('Vérification code : ' . $output); // Écrit dans les logs pour trace

            // Redirige vers la page de scraping après succès
            return redirect('/scraper-form')->with('success', 'Connexion réussie.');

        } catch (ProcessFailedException $e) {
            Log::error('Échec vérification code : ' . $e->getMessage()); // Log l’erreur
            return back()->with('error', 'Échec de la connexion.'); // Retourne à la saisie du code
        }
    }

    public function listAccounts()
    {
        $accounts = TelegramAccount::where('authorized', true)->get();
        return view('telegram.sessions', compact('accounts'));
    }
    public function scrapeFromSession(Request $request)
{
    $phone = $request->input('phone');
    $account = TelegramAccount::where('phone', $phone)->first();

    if (!$account || !$account->authorized) {
        return back()->with('error', 'Compte non trouvé ou non autorisé.');
    }

    $groupLink = 'https://t.me/codingtuto'; // Par défaut, à rendre dynamique plus tard

    $scriptPath = base_path('app/Scripts/scraper.py');

    $process = new Process([
        '/home/abdalbensaid/venv/bin/python3',
        $scriptPath,
        $groupLink,
        $account->session_file // On passe le fichier session
    ]);

    try {
        $process->mustRun();
        $output = $process->getOutput();
        $json = json_decode($output, true);

        return view('scraper.form', [
            'members' => $json['members'] ?? [],
            'groupLink' => $groupLink
        ]);

    } catch (ProcessFailedException $e) {
        return back()->with('error', 'Erreur lors du scraping.');
    }
}

}
