<?php

declare(strict_types=1);

namespace Paulhibbert\Settings;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Paulhibbert\Settings\Console\AddSettingCommand;
use Paulhibbert\Settings\Console\ImportSettingCommand;
use Paulhibbert\Settings\Console\ListSettingsCommand;
use Paulhibbert\Settings\Console\RemoveSettingCommand;
use Paulhibbert\Settings\Console\UpdateSettingCommand;
use Paulhibbert\Settings\Facades\Setting;

final class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('setting', function () {
            return new SettingsManager;
        });

        $this->commands([
            AddSettingCommand::class,
            RemoveSettingCommand::class,
            UpdateSettingCommand::class,
            ListSettingsCommand::class,
            ImportSettingCommand::class,
        ]);
    }

    public function boot(): void
    {
        Blade::if('setting', function (string $settingName): bool {
            return Setting::isEnabled($settingName);
        });

        $this->loadMigrationsFrom([
            __DIR__.'/../database/migrations',
        ]);

        AboutCommand::add('Settings', fn () => ['Version' => 'v0.0.1']);
    }
}
