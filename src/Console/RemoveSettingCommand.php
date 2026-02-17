<?php

declare(strict_types=1);

namespace Paulhibbert\Settings\Console;

use Illuminate\Console\Command;
use Paulhibbert\Settings\Facades\Setting;
use Paulhibbert\Settings\Traits\DisplaySettingsInConsole;
use Throwable;

final class RemoveSettingCommand extends Command
{
    use DisplaySettingsInConsole;

    protected $signature = 'settings:remove {name}';

    protected $description = 'Remove a setting.';

    public function handle(): void
    {
        $name = $this->argument('name');

        if (! is_string($name) || empty(trim($name))) {
            $this->error('The name argument must be a non-empty string.');

            return;
        }

        try {
            Setting::delete($name);
            $this->info("Setting '{$name}' removed successfully.");
            $this->displaySettings(Setting::list());
        } catch (Throwable $e) {
            $this->error('Error removing setting: '.$e->getMessage());
        }
    }
}
