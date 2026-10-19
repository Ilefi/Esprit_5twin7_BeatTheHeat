<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    /**
     * Seed the demo dataset (Database\Seeders\DatabaseSeeder) when RefreshDatabase migrates the test database.
     * The in-memory database is migrated once per run, and each test is rolled back in a transaction.
     */
    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        // No test reaches a real API (Open Food Facts, Gemini…): fake the calls with Http::fake().
        Http::preventStrayRequests();
    }
}
