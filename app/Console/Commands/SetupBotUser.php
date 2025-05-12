<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SetupBotUser extends Command
{
    protected $signature = 'bot:setup';
    protected $description = 'Créer un utilisateur Bot et générer un token API pour le bot Telegram';

    public function handle()
    {
        $email = 'admin@admin.com';
        $password = 'password';

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->info("🔧 Création de l'utilisateur Bot...");
            $user = User::create([
                'name' => 'Bot',
                'email' => $email,
                'password' => Hash::make($password),
            ]);
        } else {
            $this->info("✅ L'utilisateur Bot existe déjà.");
        }

        $token = $user->createToken('bot')->plainTextToken;
        $this->info("🔑 Token API généré :");
        $this->line($token);

        return Command::SUCCESS;
    }
}
