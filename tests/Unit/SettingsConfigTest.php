<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SettingsConfigTest extends TestCase
{
    #[Test]
    public function it_defines_all_contact_keys(): void
    {
        $keys = ['address', 'email', 'instagram', 'gmaps_iframe', 'business_hours'];

        foreach ($keys as $key) {
            $this->assertTrue(
                array_key_exists($key, config('settings')),
                "Config key 'settings.{$key}' should be defined"
            );
        }
    }

    #[Test]
    public function it_groups_contact_keys_with_empty_default(): void
    {
        $keys = ['address', 'email', 'instagram', 'gmaps_iframe', 'business_hours'];

        foreach ($keys as $key) {
            $this->assertSame(
                '',
                config("settings.{$key}"),
                "Config key 'settings.{$key}' should default to empty string"
            );
        }
    }
}
