<?php

namespace Paulhibbert\Settings\Traits;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Paulhibbert\Settings\Data\SettingsDataObject;

trait ValidatesSettingsInput
{
    /**
     * @throws InvalidArgumentException
     */
    protected function validateArgumentsAndOptions(): SettingsDataObject
    {
        $name = $this->argument('name');
        $isEnabled = $this->option('enabled');
        $value = $this->option('value');

        /**
         * @var array{
         *     name: string,
         *     is_enabled: string,
         *     value: string|null
         * }
         */
        $validated = Validator::make(
            [
                'name' => $name,
                'is_enabled' => $isEnabled,
                'value' => $value,
            ],
            [
                'name' => ['required', 'string', 'max:255'],
                'is_enabled' => ['in:0,1'],
                'value' => ['nullable', 'string'],
            ],
            [
                'name.string' => 'The name must be a string.',
                'name.max' => 'The name may not be greater than 255 characters.',
                'is_enabled.in' => 'The enabled option must be 0 or 1.',
                'value.string' => 'The value must be a string.',
            ]
        )->whenFails(
            function ($validator) {
                throw new InvalidArgumentException($validator->errors()->first());
            },
            function ($validator) {
                return $validator->validated();
            }
        );

        return new SettingsDataObject(
            name: $validated['name'],
            isEnabled: (bool) $validated['is_enabled'],
            value: $validated['value'],
        );
    }
}
