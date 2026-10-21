<?php

namespace App\Services;

use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\StrayRequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Minimal Google Gemini client (generateContent) returning structured JSON.
 * Without a key, or when the API fails, it returns null so callers show a message instead of an error.
 */
class Gemini
{
    public function configured(): bool
    {
        return filled(config('services.gemini.key'));
    }

    /**
     * Asks the model for a JSON object matching $schema (OpenAPI subset, see "structured output" in the Gemini docs).
     *
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>|null
     */
    public function json(string $instructions, string $prompt, array $schema): ?array
    {
        if (! $this->configured()) {
            return null;
        }

        try {
            $response = Http::baseUrl(rtrim(config('services.gemini.url'), '/'))
                ->withHeaders(['x-goog-api-key' => config('services.gemini.key')])
                ->timeout(config('services.gemini.timeout'))
                ->acceptJson()
                ->post('/models/'.config('services.gemini.model').':generateContent', [
                    'systemInstruction' => ['parts' => [['text' => $instructions]]],
                    'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
                    'generationConfig' => [
                        'temperature' => 0.4,
                        'responseMimeType' => 'application/json',
                        'responseSchema' => $schema,
                    ],
                ]);

            if ($response->failed()) {
                // Wrong key, unknown model, quota…: visible in the logs (`php artisan pail`) without breaking the page.
                Log::warning('Gemini: HTTP '.$response->status(), ['message' => $response->json('error.message')]);

                return null;
            }

            $text = $response->json('candidates.0.content.parts.0.text');
            $data = is_string($text) ? json_decode($text, true) : null;

            return is_array($data) ? $data : null;
        } catch (HttpClientException|StrayRequestException) {
            // Timeout, network error, or a test without Http::fake(): expected, the caller shows a message.
            return null;
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }
}
