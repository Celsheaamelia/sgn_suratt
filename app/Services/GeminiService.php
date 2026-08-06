<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    protected string $apiKey;
    protected string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
        $this->apiUrl = config('services.gemini.url');
    }

    public function generateText(string $prompt): string
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl . '?key=' . $this->apiKey, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
        ]);

        if ($response->failed()) {
            throw new \Exception('Gemini API error: ' . $response->body());
        }

        return $response->json('candidates.0.content.parts.0.text') ?? '';
    }

    /**
     * Kirim gambar + prompt ke Gemini (multimodal), balikin teks jawabannya.
     * Dipakai buat "membaca" isi gambar: OCR, ekstrak field form, dsb.
     */
    public function generateFromImage(string $imagePath, string $prompt): string
    {
        if (!file_exists($imagePath)) {
            throw new \Exception("File gambar tidak ditemukan: {$imagePath}");
        }

        $mimeType = mime_content_type($imagePath) ?: 'image/jpeg';
        $base64   = base64_encode(file_get_contents($imagePath));

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(60)->post($this->apiUrl . '?key=' . $this->apiKey, [
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data'      => $base64,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        if ($response->failed()) {
            throw new \Exception('Gemini API error: ' . $response->body());
        }

        return $response->json('candidates.0.content.parts.0.text') ?? '';
    }

    /**
     * Kirim gambar + prompt ke Gemini, DENGAN JSON schema (structured output).
     * Gemini akan dipaksa membalas JSON valid sesuai $schema -- tidak perlu lagi
     * parsing manual / jaga-jaga markdown fence seperti generateFromImage().
     *
     * $schema pakai format OpenAPI subset yang didukung Gemini, contoh:
     * [
     *   'type' => 'object',
     *   'properties' => [
     *       'nama' => ['type' => 'string'],
     *       'total' => ['type' => 'number'],
     *   ],
     *   'required' => ['nama', 'total'],
     * ]
     *
     * Return: array hasil decode JSON (bukan string mentah).
     */
    public function generateStructuredFromImage(string $imagePath, string $prompt, array $schema): array
    {
        if (!file_exists($imagePath)) {
            throw new \Exception("File gambar tidak ditemukan: {$imagePath}");
        }

        $mimeType = mime_content_type($imagePath) ?: 'image/jpeg';
        $base64   = base64_encode(file_get_contents($imagePath));

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(60)->post($this->apiUrl . '?key=' . $this->apiKey, [
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data'      => $base64,
                            ],
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'responseSchema'   => $schema,
            ],
        ]);

        if ($response->failed()) {
            throw new \Exception('Gemini API error: ' . $response->body());
        }

        $text = $response->json('candidates.0.content.parts.0.text') ?? '';
        $decoded = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            throw new \Exception('Gemini mengembalikan JSON tidak valid: ' . $text);
        }

        return $decoded;
    }
}