<?php

declare(strict_types=1);

namespace Paulhibbert\Settings\Facades;

use Illuminate\Support\Facades\Facade;
use Paulhibbert\Settings\Data\SettingsDataObject;
use Paulhibbert\Settings\SettingsModel;

/**
 * @method static SettingsModel create(SettingsDataObject $data)
 * @method static SettingsModel find(string $name)
 * @method static \Illuminate\Database\Eloquent\Collection<int, SettingsModel> list()
 * @method static SettingsModel update(SettingsDataObject $data)
 * @method static void delete(string $name)
 * @method static bool isEnabled(string $name)
 * @method static mixed value(string $name)
 * @method static ?int integerValue(string $name)
 * @method static ?float floatValue(string $name)
 */
class Setting extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'setting';
    }
}
