AI API Key Secure Setup

Purpose
- Explain how to securely store the Google AI Studio API key and avoid exposing it in client-side code.

Steps
1. Add the API key to your environment configuration

- Open your project `.env` file and add the following line (do NOT commit `.env` to source control):

  GOOGLE_AI_API_KEY=your_real_api_key_here

2. Remove any client-side API keys

- Search for any occurrences of API keys in your repository (especially in Blade views or public JS files) and remove them. For example, remove or replace any `apiKey: 'YOUR_API_KEY_HERE'` occurrences in `resources/views/ai-scheduler.blade.php`.

3. Use a server-side proxy (recommended)

- Implement a small server-side endpoint that reads `GOOGLE_AI_API_KEY` from the environment and forwards requests to Google AI Studio. This keeps the key private and prevents exposure in browser network traces.

Example (Laravel skeleton)

- .env
  ```env
  GOOGLE_AI_API_KEY=your_real_api_key_here
  ```

- routes/web.php (example route)
  ```php
  use App\Http\Controllers\AiProxyController;

  Route::post('/api/ai/generate', [AiProxyController::class, 'generate'])->middleware('auth');
  ```

- app/Http/Controllers/AiProxyController.php (skeleton)
  ```php
  <?php

  namespace App\Http\Controllers;

  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Http;

  class AiProxyController extends Controller
  {
      public function generate(Request $request)
      {
          $apiKey = env('GOOGLE_AI_API_KEY');

          $response = Http::withHeaders([
              'Authorization' => 'Bearer ' . $apiKey,
              'Content-Type' => 'application/json',
          ])->post('https://api.generative.google/v1beta/models/gemini-2.5-flash:generate', $request->all());

          return response()->json($response->json(), $response->status());
      }
  }
  ```

Notes and best practices
- Never commit your `.env` file or API keys to Git.
- Rotate the API key immediately if it was exposed publicly (for example, the key you posted in chat).
- Use appropriate authentication and rate-limiting on the proxy endpoint.
- Limit which users can call the proxy (middleware `auth` or role checks).

Testing locally
- After adding the key to `.env`, restart your local server and test the proxy with an authenticated request (Postman or curl).

Questions
- If you want, I can implement the proxy endpoint now and update `ai-scheduler.blade.php` to call it instead of the client-side SDK.