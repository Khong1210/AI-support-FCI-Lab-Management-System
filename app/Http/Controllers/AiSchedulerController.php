<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http; // 用來呼叫 Gemini API
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


class AiSchedulerController extends Controller
{
    /**
     * Display the AI scheduler interface with schedule, software, and equipment data.
     */public function index(): View
    {
        // 🌟 修正：使用 leftJoin 撈取關聯欄位，並用 select 重新命名避免欄位衝突
        $schedules = DB::table('schedules')
            ->leftJoin('laboratories', 'schedules.lab_id', '=', 'laboratories.id')
            ->leftJoin('courses', 'schedules.course_id', '=', 'courses.id')
            ->select(
                'schedules.*',
                'laboratories.lab_name as laboratory_name', // 這樣前端就能拿到實驗室名字
                'courses.course_name as course_title'       // 這樣前端就能拿到課程名字
            )
            ->get();

        $software = DB::table('software')->get();
        $laboratories = DB::table('laboratories')->get();
        $courses = DB::table('courses')->get();
        $lecturers = DB::table('users')->where('user_role', 5)->get();

        return view('ai-scheduler', compact('schedules', 'software', 'laboratories', 'courses', 'lecturers'));
    }

    /**
     * 處理 AI 自動排程的核心邏輯 (包含 Token 扣除與事務)
     */
   public function generateAiSchedule(Request $request)
{
    
    $request->validate([
        'prompt' => 'required|string',
    ]);

    $apiKey = env('GOOGLE_AI_KEY')?:'AIzaSyBZQZrbO6fSfIbcHBxj45sUJ7yR65rO6mQ';
    
    // 🌟 修正一：如果找不到 Key，也要回傳 JSON 格式，前端才不會解析失敗！
    if (!$apiKey) {
        return response()->json([
            'error' => 'AI API key not configured on server. Please check your .env file.'
        ], 500);
    }

    try {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
            'contents' => [
                ['parts' => [['text' => $request->input('prompt')]]]
            ],
            'generationConfig' => [
                'maxOutputTokens' => 3000
            ]
        ]);

        if (!$response->successful()) {
            return response()->json(['error' => 'Gemini API Error: ' . $response->body()], $response->status());
        }

        $aiResponseBody = $response->json();
        $pureText = $aiResponseBody['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (empty($pureText)) {
            return response()->json(['error' => 'AI 回傳了空內容。'], 500);
        }

        // 🌟 修正二：確保這裡回傳的是標準 JSON 物件
        return response()->json([
            'success' => true,
            'text' => $pureText
        ]);

    } catch (\Exception $e) {
        return response()->json(['error' => 'Proxy Error: ' . $e->getMessage()], 500);
    
    }
}
}