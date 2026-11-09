<?php

namespace Encore\Admin\Console;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Auth\Database\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Validator;

class CreateUserCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'admin:create-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a admin user';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $username = $this->ask('Enter username', 'admin');
        $password = $this->secret('Enter password (>= 6 characters)');
        $name = $this->ask('Enter name', $username);

        if (strlen($password) < 6) {
            $this->error('Password must be at least 6 characters.');

            return static::FAILURE;
        }

        if (Administrator::query()->where('username', $username)->exists()) {
            $this->error("User [{$username}] already exists.");

            return static::FAILURE;
        }

        $user = Administrator::create([
            'username' => $username,
            'password' => Hash::make($password),
            'name'     => $name,
        ]);

        // Attach the administrator role when it exists.
        $role = Role::query()->where('slug', 'administrator')->first();

        if ($role) {
            $user->roles()->save($role);
        }

        $this->info("User [$username] created successfully.");

        return static::SUCCESS;
    }
}
