<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WhatsAppController extends Controller
{

    public function handleIncomingMessage(Request $request)
    {
        $incomingMessage = $request->input('messages.0.text.body');
        $customerPhone = $request->input('messages.0.from');

        
        $isFirstMessage = $this->isFirstMessage($customerPhone);

        if ($isFirstMessage) {
            return $this->sendLanguageSelection($customerPhone);
        }

        $customer = Customer::where('phone_number', $customerPhone)->first();

        if ($customer && !$customer->language) {
            return $this->processLanguageSelection($incomingMessage, $customerPhone);
        }

        // Check if customer has selected a skill
        if ($customer && !$customer->skill) {
            return $this->processSkillSelection($incomingMessage, $customerPhone);
        }

        return $this->sendMessageToWhatsApp($customerPhone, "How can we assist you?");
    }

    private function isFirstMessage($customerPhone)
    {
        return !Customer::where('phone_number', $customerPhone)->exists();
    }

    private function sendLanguageSelection($customerPhone)
    {
        $message = [
            'messaging_product' => 'whatsapp',
            'to' => $customerPhone,
            'interactive' => [
                'type' => 'button',
                'body' => [
                    'text' => "👋 Welcome! Please select your preferred language: \n\n" .
                        "1️⃣ සිංහල\n" .
                        "2️⃣ தமிழ்\n" .
                        "3️⃣ English"
                ],
                'action' => [
                    'buttons' => [
                        [
                            'type' => 'reply',
                            'reply' => [
                                'id' => 'sinhalese',
                                'title' => 'සිංහල'
                            ]
                        ],
                        [
                            'type' => 'reply',
                            'reply' => [
                                'id' => 'tamil',
                                'title' => 'தமிழ்'
                            ]
                        ],
                        [
                            'type' => 'reply',
                            'reply' => [
                                'id' => 'english',
                                'title' => 'English'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $this->sendMessageToWhatsApp($customerPhone, $message);
    }

    private function processLanguageSelection($message, $customerPhone)
    {
        $languages = [
            'sinhalese' => 'Sinhala',
            'tamil' => 'Tamil',
            'english' => 'English'
        ];

        if (!isset($languages[$message])) {
            return $this->sendMessageToWhatsApp($customerPhone, "Invalid selection. Please choose a valid language.");
        }

        Customer::updateOrCreate(
            ['phone_number' => $customerPhone],
            ['language' => $languages[$message]]
        );

        return $this->sendSkillSelection($customerPhone);
    }

    private function sendSkillSelection($customerPhone)
    {
        $message = [
            'messaging_product' => 'whatsapp',
            'to' => $customerPhone,
            'interactive' => [
                'type' => 'button',
                'body' => [
                    'text' => "Please select the service you need help with: \n\n" .
                        "1️⃣ PEO TV\n" .
                        "2️⃣ Voice\n" .
                        "3️⃣ Broadband"
                ],
                'action' => [
                    'buttons' => [
                        [
                            'type' => 'reply',
                            'reply' => [
                                'id' => 'peo_tv',
                                'title' => 'PEO TV'
                            ]
                        ],
                        [
                            'type' => 'reply',
                            'reply' => [
                                'id' => 'voice',
                                'title' => 'Voice'
                            ]
                        ],
                        [
                            'type' => 'reply',
                            'reply' => [
                                'id' => 'broadband',
                                'title' => 'Broadband'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $this->sendMessageToWhatsApp($customerPhone, $message);
    }

    private function processSkillSelection($message, $customerPhone)
    {
        $skills = [
            'peo_tv' => 'PEO TV',
            'voice' => 'Voice',
            'broadband' => 'Broadband'
        ];

        if (!isset($skills[$message])) {
            return $this->sendMessageToWhatsApp($customerPhone, "Invalid selection. Please choose a valid service.");
        }

        Customer::where('phone_number', $customerPhone)->update(['skill' => $skills[$message]]);

        return $this->sendMessageToWhatsApp($customerPhone, "You have selected {$skills[$message]}. How can we assist you?");
    }

    private function sendMessageToWhatsApp($customerPhone, $message)
    {
        $apiUrl = 'https://graph.facebook.com/v14.0/627733203747438/messages';
        $accessToken = 'EAA4A1Pp7ykgBO6pO63p6kklzug0rKIwP8sd4gXlveu11E51qIWsqE1Xf0tMbkp3z67m0LtZALZCYq7X7068OGP64Nq4QWZBWcBa3iqKcPPreFFfOBjjrKfd6xPbqagsheLZAQq9WljlG5PXENfZBYEcpmYwhhxvNgzE22DrItucuS7ZC1NfXaZBj4crogl4maTZATnuPA7abhq4IZAZBPHfw6lB6aQUovxcg2GuAb6hz6s';  // Replace with your actual access token

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ];

        $response = Http::withHeaders($headers)->post($apiUrl, $message);

        if ($response->successful()) {
            return response()->json(['message' => 'Message sent successfully!']);
        } else {
            return response()->json(['message' => 'Failed to send message.'], 500);
        }
    }

}
