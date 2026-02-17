<?php

namespace Paulhibbert\Settings\Data;

class SettingsDataObject
{
    public function __construct(
        public string $name,
        public bool $isEnabled,
        public ?string $value = null,
    ) {}
}
