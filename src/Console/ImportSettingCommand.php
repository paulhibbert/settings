<?php

declare(strict_types=1);

namespace Paulhibbert\Settings\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Paulhibbert\Settings\Facades\Setting;
use Paulhibbert\Settings\Traits\DisplaySettingsInConsole;
use Paulhibbert\Settings\Traits\ValidatesSettingsInput;
use Throwable;

final class ImportSettingCommand extends Command
{
    use DisplaySettingsInConsole;
    use ValidatesSettingsInput;

    protected $signature = 'settings:import {name} {--enabled=1 : 0=false 1=true} {--value=  : url to json file containing the value}';

    protected $description = 'Import a setting from a url, must return json. If the setting already exists it will be updated.';

    public function handle(): void
    {
        try {
            $settingsData = $this->validateArgumentsAndOptions();
        } catch (InvalidArgumentException $e) {
            $this->error('Validation error: '.$e->getMessage());

            return;
        }
        if (empty($settingsData->value)) {
            $this->error('Validation error: The value option is required for import.');

            return;
        }
        if (! filter_var($settingsData->value, FILTER_VALIDATE_URL)) {
            $this->error('Validation error: The value option must be a valid url.');

            return;
        }

        try {
            $jsonContent = Http::timeout(5)->get($settingsData->value)->json() ?? throw new InvalidArgumentException('The url was unreachable or did not return valid JSON.');
            $settingsData->value = (string) json_encode($jsonContent, JSON_PRETTY_PRINT);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return;
        }

        Setting::update($settingsData);
        $this->displaySetting($settingsData->name);
    }
}
