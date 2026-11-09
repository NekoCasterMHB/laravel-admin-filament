<?php

namespace Encore\Admin\Console;

use Encore\Admin\Facades\Admin;
use Illuminate\Console\Command;

class MenuCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'admin:menu';

    /**
     * The console command description.
     *
     * @var string
     *
     * Port note: the original command managed the menu cache / export;
     * the port prints the current menu tree.
     */
    protected $description = 'Show the current admin menu tree';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        foreach (Admin::menuLinks() as $link) {
            $this->line(str_pad($link['title'], 24).' '.admin_url($link['uri']));
        }

        return static::SUCCESS;
    }
}
