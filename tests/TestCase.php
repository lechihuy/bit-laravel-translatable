<?php

namespace Bit\Skeleton\Tests;

use Mockery;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Bit\Translatable\TranslatableServiceProvider;

abstract class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    protected function getPackageProviders($app)
    {
        return [
            TranslatableServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        //
    }
}