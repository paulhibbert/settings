<?php

namespace Paulhibbert\Settings\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Paulhibbert\Settings\SettingsModel;

trait DisplaySettingsInConsole
{
    protected function displaySetting(string $name): void
    {
        $settings = SettingsModel::query()->where('name', $name)->get();
        $this->displaySettings($settings);
    }

    /**
     * @param  Collection<int, SettingsModel>  $settings
     */
    protected function displaySettings(Collection $settings): void
    {
        $this->table(
            headers: Schema::getColumnListing('settings'),
            rows: $settings->toArray()
        );
    }
}
