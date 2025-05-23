<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Models\TelegramAccount;
use App\Models\Prospect;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;



class ScraperController extends Controller
{
    public function run(Request $request)
    {
        $user = Auth::user();

        $groupLink = $request->query('group');

        if (!$groupLink) {
            Log::warning('Lien de groupe manquant dans la requête API.');
            return response()->json([
                'status' => 'error',
                'message' => 'Aucun lien de groupe fourni'
            ], 400);
        }

        $scriptPath = base_path('app/Scripts/scraper.py');

        $command = [
            '/home/abdalbensaid/venv/bin/python3',
            $scriptPath,
            $groupLink
        ];

        Log::debug('Commande Python API : ', $command);

        if (!File::exists(base_path("app/Scripts/{$sessionFile}"))) {
            Log::error('Fichier de session introuvable : ' . $sessionFile);
            return back()->with('error', 'Fichier de session introuvable.');
        }

        $process = new Process($command);
        $process->setTimeout(300);

        try {
            $process->mustRun();

            $output = $process->getOutput();
            Log::debug('Sortie brute du script API : ' . $output);

            $json = json_decode($output, true);

            if ($json === null) {
                Log::error('Sortie non-JSON retournée par le script Python API', ['output' => $output]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'La sortie du script Python n\'est pas un JSON valide',
                    'raw_output' => $output
                ], 500);
            }

            return response()->json($json);

        } catch (ProcessFailedException $e) {
            Log::error('Échec du script Python API', [
                'message' => $e->getMessage(),
                'output' => $process->getOutput(),
                'error_output' => $process->getErrorOutput()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Le script Python a échoué',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function showForm()
    {
        $accounts = TelegramAccount::where('user_id', Auth::id())->get();

        return view('scraper.form', compact('accounts'));
    }


    public function submitForm(Request $request)
    {
        $groupLink = $request->input('group');


        if (!$groupLink) {
            Log::warning('Lien de groupe manquant dans le formulaire.');
            return back()->with('error', 'Veuillez entrer un lien de groupe Telegram.');
        }

        // Récupère le dernier compte connecté (tu peux filtrer autrement si nécessaire)
        $accountId = $request->input('account_id');
        $account = TelegramAccount::where('user_id', Auth::id())
                ->where('id', $accountId)
                ->first();


        if (!$account) {
            Log::error('Aucun compte Telegram connecté trouvé.');
            return back()->with('error', 'Aucun compte Telegram connecté disponible.');
        }

        $sessionFile = $account->session_file; // ex: 22501010101.session
        $scriptPath = base_path('app/Scripts/scraper.py');

        $command = [
            '/home/abdalbensaid/venv/bin/python3',
            $scriptPath,
            $groupLink,
            $sessionFile
        ];

        Log::debug('Commande Python via formulaire : ', $command);

        $process = new Process($command);
        $process->setTimeout(300);

        try {
            $process->mustRun();

            $output = $process->getOutput();
            Log::debug('Sortie brute du script via formulaire : ' . $output);

            $json = json_decode($output, true);

            if (isset($json['members']) && is_array($json['members'])) {
                foreach ($json['members'] as $member) {
                    Prospect::updateOrCreate(
                        [
                            'username' => $member['username'],
                            'user_id' => Auth::id() // pour éviter conflits entre utilisateurs
                        ],
                        [
                            'first_name' => $member['first_name'] ?? '',
                            'last_name' => $member['last_name'] ?? '',
                            'username' => $member['username'] ?? '',
                            'phone' => null,
                            'tags' => 'telegram',
                            'activity' => 'scraped',
                            'user_id' => Auth::id()
                        ]
                    );

                }
            }


            if (!isset($json['members'])) {
                Log::error('Sortie sans membres ou erreur via formulaire', ['output' => $output]);
                return back()->with('error', $json['error'] ?? 'Erreur inconnue.');
            }

            return view('scraper.form', [
                'members' => $json['members'],
                'groupLink' => $groupLink
            ]);

        } catch (ProcessFailedException $e) {
            Log::error('Échec du script Python via formulaire', [
                'message' => $e->getMessage(),
                'output' => $process->getOutput(),
                'error_output' => $process->getErrorOutput()
            ]);

            return back()->with('error', 'Erreur lors de l\'exécution du script.');
        }
    }

    public function sendMessages(Request $request)
    {
        $message = $request->input('message');
        $groupLink = $request->input('groupLink');

        if (!$message || !$groupLink) {
            return back()->with('error', 'Message ou groupe manquant.');
        }

        $account = TelegramAccount::where('user_id', Auth::id())
        ->where('authorized', true)
        ->latest()
        ->first();

        if (!$account) {
            return back()->with('error', 'Aucun compte Telegram disponible.');
        }

        $sessionFile = $account->session_file;

        $scriptPath = base_path('app/Scripts/send_message.py');

        $command = [
            '/home/abdalbensaid/venv/bin/python3',
            $scriptPath,
            $message,
            $sessionFile
        ];

        Log::debug('Commande d\'envoi de message : ', $command);

        $members = Prospect::whereNotNull('username')->pluck('username')->toArray();
        $usernamesFile = base_path('app/Scripts/usernames.json');

        File::put($usernamesFile, json_encode($members, JSON_PRETTY_PRINT));

        $process = new Process($command);
        $process->setTimeout(300);

        try {
            $process->mustRun();
            $output = $process->getOutput();

            Log::debug('Envoi terminé : ' . $output);
            return back()->with('success', 'Messages envoyés.');

        } catch (ProcessFailedException $e) {
            Log::error('Échec de l\'envoi des messages', [
                'message' => $e->getMessage(),
                'output' => $process->getOutput()
            ]);

            return back()->with('error', 'Erreur lors de l\'envoi.');
        }
    }


}
