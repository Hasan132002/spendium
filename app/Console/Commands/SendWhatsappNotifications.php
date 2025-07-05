<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\SapPayment;

class SendWhatsappNotifications extends Command
{
    protected $signature = 'notify:whatsapp-ovpm';
    protected $description = 'Send WhatsApp notifications for new Outgoing Payments (OVPM)';

    public function handle()
    {
        while (true) {
            $lastDocNum = Cache::get('last_docnum_processed', 0);
            $today = date('Y-m-d');
            $instanceId = env('WAAPI_INSTANCE_ID');
            $token = env('WAAPI_TOKEN');

            // SAP DB se new payments lein
            $payments = DB::connection('sap')->select("
        SELECT CardCode, CardName, DocNum, DocDate, DocTotal, Comments
        FROM OVPM
        WHERE DocNum > ? AND CAST(DocDate AS DATE) = ?
        ORDER BY DocNum ASC
    ", [$lastDocNum, $today]);

            if (empty($payments)) {
                $this->info('No new payments found.');
                return 0;
            }

            foreach ($payments as $payment) {
                // Local MySQL DB me save karna
                $exists = SapPayment::where('DocNum', $payment->DocNum)->exists();

                if (!$exists) {
                    SapPayment::create([
                        'CardCode' => $payment->CardCode,
                        'CardName' => $payment->CardName,
                        'DocNum' => $payment->DocNum,
                        'DocDate' => $payment->DocDate,
                        'DocTotal' => $payment->DocTotal,
                        'Comments' => $payment->Comments,
                    ]);

                    // $whatsappNumber = '923359219977@c.us';
                    $whatsappNumber = '923359219977@c.us';

                    $message = "New Outgoing Payment Created:\n"
                        . "CardCode: {$payment->CardCode}\n"
                        . "CardName: {$payment->CardName}\n"
                        . "DocNum: {$payment->DocNum}\n"
                        . "DocDate: {$payment->DocDate}\n"
                        . "Amount: {$payment->DocTotal}\n"
                        . "Comments: {$payment->Comments}";

                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                        'Content-Type' => 'application/json',
                    ])->post("https://waapi.app/api/v1/instances/{$instanceId}/client/action/send-message", [
                                'chatId' => $whatsappNumber,
                                'message' => $message,
                                'buttons' => [
                                ['id' => 'yes_approved_' . $payment->DocNum, 'text' => '✅ YES'],
                                ['id' => 'no_rejected_' . $payment->DocNum, 'text' => '❌ NO']
                            ]
                            ]);

                    if ($response->successful()) {
                        Log::info("WhatsApp sent to {$whatsappNumber} for DocNum {$payment->DocNum}");
                        $this->info("Message sent to {$whatsappNumber} for DocNum {$payment->DocNum}");
                        Cache::put('last_docnum_processed', $payment->DocNum, now()->addDay());
                    } else {
                        Log::error("Failed WhatsApp message for DocNum {$payment->DocNum}: " . $response->body());
                        $this->error("Failed to send message for DocNum {$payment->DocNum}");
                    }
                }
            }
            sleep(60);
        }

        return 0;
    }

}
