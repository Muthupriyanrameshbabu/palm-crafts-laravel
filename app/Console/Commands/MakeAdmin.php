<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    protected $signature = 'app:make-admin {email}';

    protected $description = 'Grants Filament admin panel access to a user by email.';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error("No user found with email {$this->argument('email')}. Register an account first, then run this command.");

            return self::FAILURE;
        }

        $user->update(['is_admin' => true]);
        $this->info("{$user->email} can now access /admin.");

        return self::SUCCESS;
    }
}
