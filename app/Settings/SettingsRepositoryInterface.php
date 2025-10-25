<?php

namespace App\Settings;

interface SettingsRepositoryInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value): void;

    public function forget(string $key): void;

    public function getGroup(string $prefix): array;

    public function clearGroup(string $prefix): void;
}
