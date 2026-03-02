<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Support\Facades\Config;
use Paulhibbert\Settings\Facades\Setting;
use Paulhibbert\Settings\SettingsModel;

class DefaultTest extends TestCase
{
    public function test_it_returns_a_default_value_if_setting_does_not_exist(): void
    {
        $value = Setting::value('does_not_exist', fn () => 3.14195);
        $this->assertSame(3.14195, $value);
        $this->assertDatabaseEmpty('settings');
    }

    public function test_it_can_return_config_if_no_setting(): void
    {
        $name = 'app.constants.pi';
        Config::set($name, '3.14195');
        $value = Setting::value('app.constants.pi', fn () => config($name));
        $this->assertSame('3.14195', $value);
        $this->assertDatabaseEmpty('settings');
    }

    public function test_it_returns_default_if_setting_is_disabled(): void
    {
        $settingModel = SettingsModel::query()->create([
            'name' => 'greeting',
            'is_enabled' => 0,
            'value' => 'Happy Holidays',
        ]);
        $value = Setting::value('greeting', fn () => 'Hello');
        $this->assertSame('Hello', $value);
    }
}
