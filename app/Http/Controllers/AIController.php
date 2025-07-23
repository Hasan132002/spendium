<?php

namespace App\Http\Controllers;
use App\Models\AIChat;
use App\Models\AIEmbedding;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Auth;


class AIController extends Controller
{
    public function chatView()
{
    $pastChats = AIChat::where('user_id', Auth::id())
                ->latest()
                ->take(10)
                ->get();

    return view('dashboard.assistant', compact('pastChats'));
}
    // public function askAI(Request $request)
    // {
    //     $request->validate([
    //         'question' => 'required|string'
    //     ]);

    //     $question = $request->input('question');

    //     $chat = AIChat::create([
    //         'user_id' => Auth::id(),
    //         'question' => $question,
    //     ]);

    //     $matchResponse = Http::post('http://127.0.0.1:8001/match-multi', [
    //         'query' => $question
    //     ]);

    //     if (!$matchResponse->ok()) {
    //         return response()->json(['error' => 'Matching failed'], 500);
    //     }

    //     $topMatches = $matchResponse->json()['top_matches'] ?? [];
    //     $context = collect($topMatches)->pluck('content')->implode("\n\n");

    //     $genResponse = Http::post('http://127.0.0.1:8001/generate', [
    //         'prompt' => $question,
    //         'context' => $context
    //     ]);

    //     if (!$genResponse->ok()) {
    //         return response()->json(['error' => 'AI failed'], 500);
    //     }

    //     $answer = $genResponse->json()['response'] ?? 'AI kuch nahi keh saka.';
    //     $chat->update(['answer' => $answer]);

    //     return response()->json([
    //         'answer' => $answer
    //     ]);
    // }

    public function askAI(Request $request)
{
    $request->validate([
        'question' => 'required|string',
        'table_name' => 'required|string',
    ]);

    $user = Auth::user();
    $question = $request->input('question');
    $table = $request->input('table_name');

    // 🧠 Step 1: Save chat question
    $chat = AIChat::create([
        'user_id' => $user->id,
        'question' => $question,
    ]);
    // dd($question, $table);

    $matchResponse = Http::post('http://127.0.0.1:8001/match', [
        'query' => $question,
        'user_id' => $user->id,
        'table_name' => $table,
    ]);

    if (!$matchResponse->ok()) {
        return response()->json(['error' => 'Matching failed'], 500);
    }

    $topMatches = $matchResponse->json()['top_matches'] ?? [];
    $context = collect($topMatches)->pluck('content')->implode("\n\n");

    // 🕓 Step 3: Get last 3 chats of user for history
    $history = AIChat::where('user_id', $user->id)
        ->latest()
        ->take(3)
        ->get()
        ->reverse()
        ->map(fn($c) => "Q: {$c->question}\nA: {$c->answer}")
        ->implode("\n\n");

    // 🧠 Step 4: AI generation call
    $genResponse = Http::post('http://127.0.0.1:8001/generate', [
        'prompt' => $question,
        'context' => $context,
        'history' => $history,
    ]);

    if (!$genResponse->ok()) {
        return response()->json(['error' => 'AI failed'], 500);
    }

    $answer = $genResponse->json()['response'] ?? 'AI kuch nahi keh saka.';
    $chat->update(['answer' => $answer]);

    return response()->json([
        'answer' => $answer,
        'context_used' => $context,
    ]);
}

public function storeEmbedding(Request $request)
{
    $validated = $request->validate([
        'user_id' => 'required|integer',
        'table_name' => 'required|string',
        'record_id' => 'required|integer',
        'text' => 'required|string',
        'embedding' => 'required|array',
    ]);

    AIEmbedding::updateOrCreate(
        [
            'user_id' => $validated['user_id'],
            'table_name' => $validated['table_name'],
            'record_id' => $validated['record_id'],
        ],
        [
            'text' => $validated['text'],
            'embedding' => $validated['embedding'],
        ]
    );

    return response()->json(['success' => true]);
}



// public function askAI(Request $request)
// {
//     $request->validate([
//         'question' => 'required|string'
//     ]);

//     $question = $request->input('question');
//   $chat = AIChat::create([
//         'user_id' => Auth::id(),
//         'question' => $question,
//     ]);
//     // Get Top 3 Matches
//     $matchResponse = Http::post('http://127.0.0.1:8001/match-multi', [
//         'query' => $question
//     ]);

//     if (!$matchResponse->ok()) {
//         return back()->with('error', 'Failed to get similar content');
//     }

//     $topMatches = $matchResponse->json()['top_matches'] ?? [];
//     $context = collect($topMatches)->pluck('content')->implode("\n\n");

//     $genResponse = Http::post('http://127.0.0.1:8001/generate', [
//         'prompt' => $question,
//         'context' => $context
//     ]);

//     if (!$genResponse->ok()) {
//         return back()->with('error', 'Gemini AI response failed.');
//     }

//     $answer = $genResponse->json()['response'] ?? 'AI kuch nahi keh saka.';
//      $chat->update(['answer' => $answer]);

//     $pastChats = AIChat::where('user_id', Auth::id())
//                 ->latest()
//                 ->take(10)
//                 ->get();
//     return view('dashboard.assistant', compact('question', 'answer', 'pastChats'));
// }

}
