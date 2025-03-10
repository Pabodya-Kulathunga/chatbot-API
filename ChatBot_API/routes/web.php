<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\WhatsAppWebhookController;

Route::get('/', [ChatController::class, 'index']);
Route::post('/send-message', [ChatController::class, 'sendMessages']);
Route::get('/messages/{phoneNumber}/{agentEmpId}', [ChatController::class, 'getMessages']);
Route::post('/match-agent', [ChatController::class, 'matchAgent']);
Route::get('/document/{documentId}', [ChatController::class, 'download'])->name('document.download');
Route::get('/document/view/{documentId}', [ChatController::class, 'view'])->name('document.view');
Route::get('/deactivate-chats', [ChatController::class, 'deactivateInactiveChats']);

Route::get('/webhook', [WhatsAppWebhookController::class, 'verifyWebhook']);
Route::post('/webhook', [WhatsAppWebhookController::class, 'receiveWebhook']);
Route::post('/send-reply', [WhatsAppWebhookController::class, 'sendAgentReply']);


Route::post('/whatsapp/incoming', [WhatsAppController::class, 'handleIncomingMessage']);
Route::get('/whatsapp/send-language-selection/{phone}', [WhatsAppController::class, 'sendLanguageSelection']);