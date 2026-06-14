<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiProxyController extends Controller
{
    public function generate(Request $request)
    {
        $candidates = [
            env('GOOGLE_AI_KEY'),
            env('GEMINI_API_KEY'),
            env('GEMINI_APIKEY'),
            env('GEMINI_KEY'),
            env('OPENAI_API_KEY'),
            env('AI_API_KEY'),
        ];

        $apiKey = null;
        foreach ($candidates as $candidate) {
            if (!empty($candidate)) {
                $apiKey = trim($candidate, "'\"");
                break;
            }
        }

        if (empty($apiKey)) {
            Log::error('AI proxy: no API key found in environment. Checked keys: GOOGLE_AI_KEY, GEMINI_API_KEY...');
            return response()->json(['error' => 'AI API key not configured on server.'], 500);
        }

        $model = $request->input('model', 'gemini-2.5-flash');
        $prompt = $request->input('prompt', '');
        $maxTokens = 4000;

        $body = [
            'contents' => [[
                'parts' => [[
                    'text' => $prompt,
                ]],
            ]],
            'generationConfig' => [
                'maxOutputTokens' => $maxTokens,
            ],
        ];

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        try {
            $resp = Http::withOptions(['verify' => false])->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($url, $body);

            if (!$resp->successful()) {
                Log::warning('AI proxy non-200 response', ['status' => $resp->status(), 'body' => $resp->body()]);
            }

            $respJson = $resp->json();
            $extracted = null;

            if (is_array($respJson)) {
                if (!empty($respJson['candidates'][0]['content']['parts'][0]['text'] ?? null)) {
                    $extracted = $respJson['candidates'][0]['content']['parts'][0]['text'];
                }

                // 備用路徑提取
                if ($extracted === null && !empty($respJson['output']['text'] ?? null)) {
                    $extracted = $respJson['output']['text'];
                }
            }

            if ($extracted === null) {
                $extracted = $resp->body();
            }

            return response($extracted, 200)->header('Content-Type', 'text/plain');

        } catch (\Exception $e) {
            Log::error('AI proxy request failed', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to contact AI provider.', 'details' => $e->getMessage()], 502);
        }
    }
}