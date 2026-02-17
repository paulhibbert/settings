<?php

declare(strict_types=1);

namespace Tests;

use InvalidArgumentException;
use Paulhibbert\Settings\Facades\Setting;
use Paulhibbert\Settings\SettingsModel;

class ValueTest extends TestCase
{
    protected SettingsModel $integerSetting;

    protected SettingsModel $floatSetting;

    protected SettingsModel $stringSetting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integerSetting = SettingsModel::query()->create([
            'name' => 'IntegerSetting',
            'is_enabled' => 1,
            'value' => '42',
        ]);

        $this->floatSetting = SettingsModel::query()->create([
            'name' => 'FloatSetting',
            'is_enabled' => 1,
            'value' => '3.14',
        ]);

        $this->stringSetting = SettingsModel::query()->create([
            'name' => 'StringSetting',
            'is_enabled' => 1,
            'value' => 'Hello, World',
        ]);
    }

    public function test_value_method_returns_integer(): void
    {
        $this->assertSame(42, Setting::value('IntegerSetting'));
    }

    public function test_value_method_returns_float(): void
    {
        $this->assertSame(3.14, Setting::value('FloatSetting'));
    }

    public function test_value_method_returns_string(): void
    {
        $this->assertSame('Hello, World', Setting::value('StringSetting'));
    }

    public function test_value_method_returns_null_if_setting_is_disabled(): void
    {
        $this->integerSetting->update(['is_enabled' => 0]);
        $this->assertNull(Setting::value('IntegerSetting'));
    }

    public function test_value_method_returns_null_if_setting_does_not_exist(): void
    {
        $this->assertNull(Setting::value('NonExistentSetting'));
    }

    public function test_is_enabled_method_returns_true_if_setting_is_enabled(): void
    {
        $this->assertTrue(Setting::isEnabled('IntegerSetting'));
    }

    public function test_is_enabled_method_returns_false_if_setting_is_disabled(): void
    {
        $this->integerSetting->update(['is_enabled' => 0]);
        $this->assertFalse(Setting::isEnabled('IntegerSetting'));
    }

    public function test_is_enabled_method_returns_false_if_setting_does_not_exist(): void
    {
        $this->assertFalse(Setting::isEnabled('NonExistentSetting'));
    }

    public function test_integer_value_method_returns_integer(): void
    {
        $this->assertSame(42, Setting::integerValue('IntegerSetting'));
    }

    public function test_integer_value_method_returns_null_if_value_is_not_integer(): void
    {
        $this->assertNull(Setting::integerValue('FloatSetting'));
        $this->assertNull(Setting::integerValue('StringSetting'));
    }

    public function test_float_value_method_returns_float(): void
    {
        $this->assertSame(3.14, Setting::floatValue('FloatSetting'));
    }

    public function test_float_value_method_returns_null_if_value_is_not_float(): void
    {
        $this->assertNull(Setting::floatValue('IntegerSetting'));
        $this->assertNull(Setting::floatValue('StringSetting'));
    }

    public function test_find_returns_error_if_setting_does_not_exist(): void
    {
        $this->assertThrows(
            fn () => Setting::find('NonExistentSetting'),
            InvalidArgumentException::class,
            'No setting found for NonExistentSetting'
        );
    }
}
