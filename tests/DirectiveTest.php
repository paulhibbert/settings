<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Support\Facades\Blade;
use Paulhibbert\Settings\SettingsModel;

class DirectiveTest extends TestCase
{
    public function test_blade_directive_renders_content_if_setting_is_enabled(): void
    {
        $settingName = 'EnabledSetting';
        SettingsModel::query()->create([
            'name' => $settingName,
            'is_enabled' => 1,
            'value' => null,
        ]);
        $viewString = "
            @setting('EnabledSetting')
                <p>This setting is enabled</p>
            @endsetting
        ";

        $compiled = Blade::compileString($viewString);

        $expected = "
            <?php if (\Illuminate\Support\Facades\Blade::check('setting', 'EnabledSetting')): ?>
                <p>This setting is enabled</p>
            <?php endif; ?>
        ";
        $this->assertSame($expected, $compiled);

        $rendered = Blade::render($viewString);

        $expected = '<p>This setting is enabled</p>';
        $this->assertSame($expected, trim($rendered));
    }

    public function test_blade_directive_does_not_render_content_if_setting_is_disabled(): void
    {
        $settingName = 'DisabledSetting';
        SettingsModel::query()->create([
            'name' => $settingName,
            'is_enabled' => 0,
            'value' => null,
        ]);
        $viewString = "
            @setting('DisabledSetting')
                <p>Display if enabled</p>
            @endsetting
        ";

        $rendered = Blade::render($viewString);

        $this->assertEmpty(trim($rendered));
    }

    public function test_blade_directive_does_not_render_content_if_setting_does_not_exist(): void
    {
        $viewString = "
            @setting('NonExistentSetting')
                <p>Display if enabled</p>
            @endsetting
        ";

        $rendered = Blade::render($viewString);

        $this->assertEmpty(trim($rendered));
    }
}
