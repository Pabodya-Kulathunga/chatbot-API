<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\Message;

class WhatsAppWebhookController extends Controller
{
    
    public function verifyWebhook(Request $request)
    {
        $verify_token = 'my_secure_verify_token_1234';
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        if ($mode === 'subscribe' && $token === $verify_token) {
            return response($challenge, 200);
        }
        return response('Forbidden2', 403);
    }

    //  Receive customer messages from WhatsApp
    public function receiveWebhook(Request $request)
    {
        $data = $request->all();

        $value = $data['entry'][0]['changes'][0]['value'] ?? null;

        if ($value && !empty($value['messages'])) {
            $message = $value['messages'][0];

            $phone_number = $message['from'] ?? '';
            $text = $message['text']['body'] ?? '';
            $timestamp = isset($message['timestamp']) ? (int)$message['timestamp'] : now()->timestamp;
            $message_id = $message['id'] ?? '';

            
            Message::create([
                'from' => $phone_number,
                'message' => $text,
                'message_id' => $message_id,
                'timestamp' => $timestamp,
                'status' => 'received',
            ]);

            

            return response()->json(['status' => 'received'], 200);
        }

        return response()->json(['status' => 'no_message'], 200);
    }

    //  Send agent replies back to WhatsApp
    public function sendAgentReply(Request $request)
    {
        $phone_number = $request->input('to');
        $text = $request->input('message');

        $accessToken = 'EAA4A1Pp7ykgBO6pO63p6kklzug0rKIwP8sd4gXlveu11E51qIWsqE1Xf0tMbkp3z67m0LtZALZCYq7X7068OGP64Nq4QWZBWcBa3iqKcPPreFFfOBjjrKfd6xPbqagsheLZAQq9WljlG5PXENfZBYEcpmYwhhxvNgzE22DrItucuS7ZC1NfXaZBj4crogl4maTZATnuPA7abhq4IZAZBPHfw6lB6aQUovxcg2GuAb6hz6s';
        $url = 'https://graph.facebook.com/v14.0/627733203747438/messages';

        $response = Http::withToken($accessToken)->post($url, [
            'messaging_product' => 'whatsapp',
            'to' => $phone_number,
            'text' => ['body' => $text],
        ]);

        //  Store agent reply in MongoDB
        Message::create([
            'to' => $phone_number,
            'message' => $text,
            'timestamp' => now()->timestamp, // Server time for sent messages
            'status' => $response->successful() ? 'sent' : 'failed',
        ]);

        return response()->json([
            'status' => $response->successful() ? 'sent' : 'failed',
        ], $response->status());
    }
    
}
