<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'two_factor.enabled' => false,
            'two_factor.required' => false,
            'two_factor.api_enabled' => false,
        ]);
    }
}
