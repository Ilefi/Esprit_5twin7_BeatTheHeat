<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Smoke test for every GET page of the UI template (parameterized routes use DemoData slugs).
 */
class TemplatePagesTest extends TestCase
{
    use RefreshDatabase;

    public static function publicPages(): array
    {
        return [
            'home' => ['/'],
            'about' => ['/a-propos'],
            'how' => ['/comment-ca-marche'],
            'faq' => ['/faq'],
            'contact' => ['/contact'],
            'login' => ['/login'],
            'register' => ['/register'],
            'forgot password' => ['/forgot-password'],
        ];
    }

    #[DataProvider('publicPages')]
    public function test_public_pages_render_for_guests(string $uri): void
    {
        $this->get($uri)->assertOk();
    }
}
