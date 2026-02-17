<?php

declare(strict_types=1);

namespace Paulhibbert\Settings\Console;

use Illuminate\Console\Command;
use InvalidArgumentException;
use Paulhibbert\Settings\Facades\Setting;
use Paulhibbert\Settings\Traits\DisplaySettingsInConsole;
use Paulhibbert\Settings\Traits\ValidatesSettingsInput;

final class UpdateSettingCommand extends Command
{
    use DisplaySettingsInConsole;
    use ValidatesSettingsInput;

    protected $signature = 'settings:update {name} {--enabled= : 0=false 1=true} {--value=  : optional string value}';

    protected $description = 'Update a setting, if the setting does not exist it will be created.';

    public function handle(): void
    {
        try {
            $settingsData = $this->validateArgumentsAndOptions();
        } catch (InvalidArgumentException $e) {
            $this->error('Validation error: '.$e->getMessage());

            return;
        }

        Setting::update($settingsData);
        $this->displaySetting($settingsData->name);
    }
}
