<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MakeSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:superadmin {email} {--password=}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Create a new super admin user or promote an existing user to super admin';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->option('password');

        // Verifica se o usuário já existe
        $user = User::where('email', $email)->first();

        if ($user) {
            // Promove usuário existente
            $user->update([
                'user_type' => 'super_admin',
                'role' => 'super_admin',
            ]);
            $this->info("✓ Super admin role granted to existing user: {$email}");
            $this->info("  User ID: {$user->id}");
            return 0;
        }

        // Cria novo usuário super admin
        if (!$password) {
            $password = $this->secret('Enter password for the new super admin user');
        }

        if (!$password || strlen($password) < 8) {
            $this->error('✗ Password must be at least 8 characters long');
            return 1;
        }

        $name = $this->ask('Enter the name for this super admin user', 'Super Admin');

        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'user_type' => 'super_admin',
                'role' => 'super_admin',
                'email_verified_at' => now(),
            ]);

            $this->info('✓ Super admin user created successfully!');
            $this->line('');
            $this->line('User Details:');
            $this->line("  ID: {$user->id}");
            $this->line("  Name: {$user->name}");
            $this->line("  Email: {$user->email}");
            $this->line("  Type: {$user->user_type}");
            $this->line("  Role: {$user->role}");
            $this->line('');
            $this->line('The user can now log in with these credentials.');

            return 0;
        } catch (\Exception $e) {
            $this->error('✗ Error creating super admin user: ' . $e->getMessage());
            return 1;
        }
    }
}
