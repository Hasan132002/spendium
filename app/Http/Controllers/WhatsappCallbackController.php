<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsappCallbackController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('WhatsApp Callback Received: ', $request->all());

        $buttonId = $request->input('button_reply.id');
        $chatId = $request->input('chatId');

        if ($buttonId && $chatId) {
            if (str_contains($buttonId, 'yes_approved_')) {
                $docNum = str_replace('yes_approved_', '', $buttonId);
                Log::info("DocNum $docNum APPROVED by $chatId");
             } elseif (str_contains($buttonId, 'no_rejected_')) {
                $docNum = str_replace('no_rejected_', '', $buttonId);
                 Log::info("DocNum $docNum REJECTED by $chatId");
             }
        }

        return response()->json(['status' => 'success']);
    }
}
