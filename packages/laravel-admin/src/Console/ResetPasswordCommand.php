<?php

namespace Encore\Admin\Console;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetPasswordCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'admin:reset-password {username?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset password for a specific admin user';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $username = $this->argument('username') ?: $this->ask('Enter the username');

        $user = Administrator::query()->where('username', $username)->first();

        if (!$user) {
            $this->error("User [{$username}] does not exist.");

            return static::FAILURE;
        }

        $password = $this->secret('Enter the new password (>= 6 characters)');

        if (strlen($password) < 6) {
            $this->error('Password must be at least 6 characters.');

            return static::FAILURE;
        }

        $user->password = Hash::make($password);
        $user->save();

        $this->info("Password for [{$username}] reset successfully.");

        return static::SUCCESS;
    }
}
