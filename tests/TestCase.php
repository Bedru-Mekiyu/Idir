<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Environment variables that must always win inside tests, regardless of
     * values exported into the OS environment on developer machines.
     *
     * PHPUnit's `<env force="true">` only calls putenv(), while Laravel reads
     * $_SERVER first — so inherited values (e.g. `.env` values leaked into the
     * process environment) would otherwise override the deterministic test
     * configuration declared in phpunit.xml. Setting them here, before the
     * application boots, guarantees a consistent environment everywhere.
     *
     * @var array<string, string>
     */
    protected static array $forcedTestEnvironment = [
        'APP_ENV' => 'testing',
        'SESSION_DRIVER' => 'array',
        'QUEUE_CONNECTION' => 'sync',
        'CACHE_STORE' => 'array',
        'BROADCAST_CONNECTION' => 'null',
        'DB_CONNECTION' => 'sqlite',
        'DB_DATABASE' => ':memory:',
        'MAIL_MAILER' => 'array',
        'BCRYPT_ROUNDS' => '4',
    ];

    public function createApplication()
    {
        foreach (static::$forcedTestEnvironment as $key => $value) {
            $_SERVER[$key] = $value;
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }

        return parent::createApplication();
    }
}
