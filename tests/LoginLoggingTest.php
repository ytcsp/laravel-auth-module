<?php

namespace Strichpunkt\LaravelAuthModule\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Strichpunkt\LaravelAuthModule\AuthModuleServiceProvider;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginLoggingTest extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [AuthModuleServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Create users table for testing
        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // Run package migrations
        Artisan::call('migrate');

        // Set user model
        $app['config']->set('auth-module.user_model', User::class);
    }

    public function test_successful_login_is_logged()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('login_logs', [
            'email' => 'test@example.com',
            'success' => true,
        ]);
    }
}

class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password'];
}
