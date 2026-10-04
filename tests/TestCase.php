<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Seed the demo dataset (Database\Seeders\DatabaseSeeder) when RefreshDatabase migrates the test database.
     * The in-memory database is migrated once per run, and each test is rolled back in a transaction.
     */
    protected $seed = true;
}
