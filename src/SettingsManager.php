<?php

declare(strict_types=1);

namespace Paulhibbert\Settings;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Paulhibbert\Settings\Data\SettingsDataObject;

final class SettingsManager
{
    public function create(SettingsDataObject $data): SettingsModel
    {
        $setting = SettingsModel::query()->where('name', $data->name)->first();
        if ($setting instanceof SettingsModel) {
            throw new InvalidArgumentException("Setting with name {$data->name} already exists");
        }

        return SettingsModel::query()->create([
            'name' => $data->name,
            'is_enabled' => (int) $data->isEnabled,
            'value' => $data->value,
        ]);
    }

    public function find(string $name): SettingsModel
    {
        $setting = SettingsModel::query()->where('name', $name)->first();
        if (! $setting instanceof SettingsModel) {
            throw new InvalidArgumentException("No setting found for {$name}");
        }

        return $setting;
    }

    /**
     * @return Collection<int, SettingsModel>
     */
    public function list(): Collection
    {
        return SettingsModel::query()->get();
    }

    public function update(SettingsDataObject $data): SettingsModel
    {
        $setting = SettingsModel::query()->updateOrCreate(
            ['name' => $data->name],
            [
                'is_enabled' => (int) $data->isEnabled,
                'value' => $data->value,
            ]
        );
        $this->maybeClearCacheForSetting($data->name);

        return $setting;
    }

    public function delete(string $name): void
    {
        $deleted = SettingsModel::query()->where('name', $name)->delete();
        if ($deleted === 0) {
            throw new InvalidArgumentException("No setting found for {$name}");
        }
        $this->maybeClearCacheForSetting($name);
    }

    /**
     * Note: cached forever on first retrieval if caching is enabled.
     * Use the update and delete methods to ensure cache is cleared when settings are modified.
     */
    public function isEnabled(string $name): bool
    {
        $setting = $this->retrieveSetting($name);

        return $setting instanceof SettingsDataObject && $setting->isEnabled;
    }

    /**
     * Note: cached forever on first retrieval if caching is enabled.
     * Use the update and delete methods to ensure cache is cleared when settings are modified.
     * 
     * Returns null if the setting does not exist or is disabled.
     * Attempts to cast numeric values to int or float, and JSON strings to arrays.
     * Otherwise returns the raw value as string.
     */
    public function value(string $name): mixed
    {
        $setting = $this->retrieveSetting($name);
        if (! $setting instanceof SettingsDataObject) {
            return null;
        }
        if (! $setting->isEnabled) {
            return null;
        }

        $value = $setting->value;
        if (is_numeric($value)) {
            return match (intval($value) == $value) {
                true => (int) $value,
                false => (float) $value
            };
        }

        if (json_validate($value)) {
            return json_decode($value, true);
        }

        return $value;
    }

    public function integerValue(string $name): ?int
    {
        $value = $this->value($name);

        return is_int($value) ? $value : null;
    }

    public function floatValue(string $name): ?float
    {
        $value = $this->value($name);

        return is_float($value) ? $value : null;
    }

    /**
     * Note: cached forever on first retrieval if caching is enabled.
     * Use the update and delete methods to ensure cache is cleared when settings are modified.
     *
     * Cache::remember returns mixed, but we know it will be either SettingsDataObject or null based on the callback and cache usage.
     */
    public function retrieveSetting(string $name): mixed
    {
        if (config('settings.cache_enabled')) {
            $cacheKey = 'settings_cache_'.$name;

            return Cache::remember($cacheKey, null, function () use ($name) {
                return $this->fetchSettingDataObject($name);
            });
        }

        return $this->fetchSettingDataObject($name);
    }

    protected function fetchSettingDataObject(string $name): ?SettingsDataObject
    {
        $setting = SettingsModel::query()->where('name', $name)->first();
        if (! $setting instanceof SettingsModel) {
            return null;
        }

        return new SettingsDataObject(
            name: $setting->name,
            isEnabled: (bool) $setting->is_enabled,
            value: $setting->value,
        );
    }

    protected function maybeClearCacheForSetting(string $name): void
    {
        if (Cache::has("settings_cache_{$name}")) {
            Cache::forget("settings_cache_{$name}");
        }
    }
}
