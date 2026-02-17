<?php

declare(strict_types=1);

namespace Paulhibbert\Settings\Console;

use Illuminate\Console\Command;
use Paulhibbert\Settings\Facades\Setting;
use Paulhibbert\Settings\Traits\DisplaySettingsInConsole;

final class ListSettingsCommand extends Command
{
    use DisplaySettingsInConsole;

    protected $signature = 'settings:list';

    protected $description = 'Display all settings in a table format.';

    public function handle(): void
    {
        $settings = Setting::list();
        if ($settings->isEmpty()) {
            $this->info('No settings found.');

            return;
        }
        $this->displaySettings(Setting::list());
    }
}
