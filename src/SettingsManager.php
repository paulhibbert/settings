<?php

declare(strict_types=1);

namespace Paulhibbert\Settings;

use Illuminate\Database\Eloquent\Collection;
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
        return SettingsModel::query()->updateOrCreate(
            ['name' => $data->name],
            [
                'is_enabled' => (int) $data->isEnabled,
                'value' => $data->value,
            ]
        );
    }

    public function delete(string $name): void
    {
        $deleted = SettingsModel::query()->where('name', $name)->delete();
        if ($deleted === 0) {
            throw new InvalidArgumentException("No setting found for {$name}");
        }
    }

    public function isEnabled(string $name): bool
    {
        $setting = SettingsModel::query()->where('name', $name)->first();

        return $setting instanceof SettingsModel && $setting->is_enabled;
    }

    public function value(string $name): mixed
    {
        if ($this->isEnabled($name) === false) {
            return null;
        }

        $value = SettingsModel::query()->where('name', $name)->first()?->value;
        if (is_numeric($value)) {
            return match (intval($value) == $value) {
                true => (int) $value,
                false => (float) $value
            };
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
}
