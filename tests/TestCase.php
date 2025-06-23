<?php

namespace Strichpunkt\LaravelAuthModule\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Strichpunkt\LaravelAuthModule\AuthModuleServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            AuthModuleServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set JWT secret for testing
        $app['config']->set('jwt.secret', 'test-secret-key');
    }
} 