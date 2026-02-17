<?php

declare(strict_types=1);

namespace Paulhibbert\Settings\Console;

use Illuminate\Console\Command;
use InvalidArgumentException;
use Paulhibbert\Settings\Facades\Setting;
use Paulhibbert\Settings\Traits\DisplaySettingsInConsole;
use Paulhibbert\Settings\Traits\ValidatesSettingsInput;
use Throwable;

final class AddSettingCommand extends Command
{
    use DisplaySettingsInConsole;
    use ValidatesSettingsInput;

    protected $signature = 'settings:add {name} {--enabled=1 : 0=false 1=true} {--value=  : optional string value}';

    protected $description = 'Add a new setting, name must be unique.';

    public function handle(): void
    {
        try {
            $settingsData = $this->validateArgumentsAndOptions();
        } catch (InvalidArgumentException $e) {
            $this->error('Validation error: '.$e->getMessage());

            return;
        }

        try {
            Setting::create($settingsData);
            $this->displaySetting($settingsData->name);
        } catch (Throwable $e) {
            $this->error('Error adding setting: '.$e->getMessage());
        }
    }
}
