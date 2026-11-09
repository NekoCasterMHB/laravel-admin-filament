<?php

namespace Encore\Admin\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class InstallCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'admin:install';

    /**
     * The console command description.
     *
     * @var string
     *
     * Port note: the app skeleton ships with the port (app/Admin, config,
     * migration), so install only runs the migrations + seed and prints
     * the default credentials.
     */
    protected $description = 'Install the laravel-admin database and print default credentials';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $usersTable = config('admin.database.users_table');

        if (DB::table($usersTable)->count() > 0) {
            $this->error('laravel-admin is already installed (admin tables are not empty).');

            return static::FAILURE;
        }

        $this->info('Running migrations...');
        Artisan::call('migrate', ['--force' => true]);

        $this->info('Seeding admin tables...');
        Artisan::call('db:seed', ['--class' => \Encore\Admin\Auth\Database\AdminTablesSeeder::class, '--force' => true]);

        $this->table([ 'Setting', 'Value' ], [
            ['URL', admin_url('auth/login')],
            ['Username', 'admin'],
            ['Password', 'admin'],
        ]);

        $this->warn('Change the default password after the first login.');

        return static::SUCCESS;
    }
}
