<?php

declare(strict_types=1);

namespace Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Paulhibbert\Settings\Console\AddSettingCommand;
use Paulhibbert\Settings\Facades\Setting;
use Paulhibbert\Settings\SettingsModel;

class CommandsTest extends TestCase
{
    public function test_add_setting_command_creates_setting(): void
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);
        $this->artisan('settings:add TestSetting --enabled=1 --value=TestValue')
            ->expectsTable(
                ['id', 'name', 'is_enabled', 'value', 'created_at', 'updated_at'],
                [
                    ['1', 'TestSetting', '1', 'TestValue', $now->toDateTimeString(), $now->toDateTimeString()],
                ],
            )
            ->assertExitCode(0);

        $setting = Setting::find('TestSetting');
        $this->assertInstanceOf(SettingsModel::class, $setting);
        $this->assertSame('TestValue', $setting->value);
        $this->assertTrue($setting->is_enabled);
    }

    public function test_add_setting_command_with_invalid_enabled_option_shows_error(): void
    {
        $this->artisan('settings:add InvalidSetting --enabled=invalid')
            ->expectsOutput('Validation error: The enabled option must be 0 or 1.')
            ->assertExitCode(0);

        $this->assertDatabaseEmpty('settings');
    }

    public function test_add_setting_command_with_invalid_name_shows_error(): void
    {
        $this->artisan('settings:add " " --enabled=1')
            ->expectsOutput('Validation error: The name field is required.')
            ->assertExitCode(0);

        $this->assertDatabaseEmpty('settings');
    }

    public function test_add_setting_command_with_long_name_shows_error(): void
    {
        $this->artisan(AddSettingCommand::class, ['name' => str_repeat('a', 256)])
            ->expectsOutput('Validation error: The name may not be greater than 255 characters.')
            ->assertExitCode(0);

        $this->assertDatabaseEmpty('settings');
    }

    public function test_add_setting_command_with_duplicate_name_shows_error(): void
    {
        SettingsModel::query()->create([
            'name' => 'DuplicateSetting',
            'is_enabled' => 0,
            'value' => 'SomeValue',
        ]);

        $this->artisan('settings:add DuplicateSetting --enabled=1 --value=Value2')
            ->expectsOutput('Error adding setting: Setting with name DuplicateSetting already exists')
            ->assertExitCode(0);
    }

    public function test_remove_setting_command_deletes_setting(): void
    {
        SettingsModel::query()->create([
            'name' => 'SettingToRemove',
            'is_enabled' => 1,
            'value' => 'SomeValue',
        ]);

        $this->artisan('settings:remove SettingToRemove')
            ->assertExitCode(0);

        $this->assertDatabaseEmpty('settings');
    }

    public function test_remove_setting_command_deletes_setting_and_forgets_cache_if_set(): void
    {
        SettingsModel::query()->create([
            'name' => 'SettingToRemove',
            'is_enabled' => 1,
            'value' => 'SomeValue',
        ]);
        Cache::put('settings_cache_SettingToRemove', 'arbitrary_value');
        $this->assertTrue(Cache::has('settings_cache_SettingToRemove'));

        $this->artisan('settings:remove SettingToRemove')
            ->assertExitCode(0);

        $this->assertDatabaseEmpty('settings');
        $this->assertFalse(Cache::has('settings_cache_SettingToRemove'));
    }

    public function test_remove_setting_command_with_nonexistent_setting_shows_error(): void
    {
        $this->artisan('settings:remove NonExistentSetting')
            ->expectsOutput('Error removing setting: No setting found for NonExistentSetting')
            ->assertExitCode(0);
    }

    public function test_remove_setting_command_with_empty_name_shows_error(): void
    {
        $this->artisan('settings:remove "  "')
            ->expectsOutput('The name argument must be a non-empty string.')
            ->assertExitCode(0);
    }

    public function test_list_settings_command_shows_no_settings_message(): void
    {
        $this->artisan('settings:list')
            ->expectsOutput('No settings found.')
            ->assertExitCode(0);
    }

    public function test_list_settings_command_shows_settings(): void
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);
        SettingsModel::query()->create([
            'name' => 'FirstSetting',
            'is_enabled' => 1,
            'value' => 'Value1',
        ]);

        $this->artisan('settings:list')
            ->expectsTable(
                ['id', 'name', 'is_enabled', 'value', 'created_at', 'updated_at'],
                [
                    ['1', 'FirstSetting', '1', 'Value1', $now->toDateTimeString(), $now->toDateTimeString()],
                ],
            )
            ->assertExitCode(0);
    }

    public function test_update_setting_command_updates_existing_setting(): void
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);
        SettingsModel::query()->create([
            'name' => 'SettingToUpdate',
            'is_enabled' => 0,
            'value' => 'OldValue',
        ]);

        $this->artisan('settings:update SettingToUpdate --enabled=1 --value=NewValue')
            ->expectsTable(
                ['id', 'name', 'is_enabled', 'value', 'created_at', 'updated_at'],
                [
                    ['1', 'SettingToUpdate', '1', 'NewValue', $now->toDateTimeString(), $now->toDateTimeString()],
                ],
            )
            ->assertExitCode(0);

        $setting = Setting::find('SettingToUpdate');
        $this->assertInstanceOf(SettingsModel::class, $setting);
        $this->assertSame('NewValue', $setting->value);
        $this->assertTrue($setting->is_enabled);
    }

    public function test_update_setting_command_creates_setting_if_not_exists(): void
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);
        $this->artisan('settings:update NewSetting --enabled=1 --value=NewValue')
            ->expectsTable(
                ['id', 'name', 'is_enabled', 'value', 'created_at', 'updated_at'],
                [
                    ['1', 'NewSetting', '1', 'NewValue', $now->toDateTimeString(), $now->toDateTimeString()],
                ],
            )
            ->assertExitCode(0);

        $setting = Setting::find('NewSetting');
        $this->assertInstanceOf(SettingsModel::class, $setting);
        $this->assertSame('NewValue', $setting->value);
        $this->assertTrue($setting->is_enabled);
    }

    public function test_update_setting_command_forgets_cache_if_set(): void
    {
        SettingsModel::query()->create([
            'name' => 'SettingToUpdate',
            'is_enabled' => 0,
            'value' => 'OldValue',
        ]);
        Cache::put('settings_cache_SettingToUpdate', 'arbitrary_value');
        $this->assertTrue(Cache::has('settings_cache_SettingToUpdate'));

        $this->artisan('settings:update SettingToUpdate --enabled=1 --value=NewValue')
            ->assertExitCode(0);

        $this->assertFalse(Cache::has('settings_cache_SettingToUpdate'));
    }

    public function test_update_setting_command_with_invalid_enabled_option_shows_error(): void
    {
        $this->artisan('settings:update InvalidSetting --enabled=invalid')
            ->expectsOutput('Validation error: The enabled option must be 0 or 1.')
            ->assertExitCode(0);

        $this->assertDatabaseEmpty('settings');
    }
}
