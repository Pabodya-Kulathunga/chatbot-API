<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\AgentSkill;
use App\Models\Customer;
use App\Models\CustomerRequest;
use App\Models\User;
use App\Models\Message;
use App\Models\Skill;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MongoDB\Client;
use MongoDB\Client as MongoClient;
use MongoDB\BSON\ObjectId;
use Illuminate\Support\Facades\Response;

class ChatController extends Controller
{


public function index(Request $request)
{
    $customerPhone = '0716520832';
    // $customerPhone  = $request->input('from');
    $requiredSkill = 'peo tv';
    $language = 'Tamil';

    $contacts = [];

    
    $agentEmpId = $request->input('agent_id', null);

    if ($agentEmpId) {
        // Fetch messages between customer and the selected agent
        $messages = Message::where(function ($query) use ($customerPhone, $agentEmpId) {
                $query->where('from', $customerPhone)->where('to', $agentEmpId);
            })
            ->orWhere(function ($query) use ($customerPhone, $agentEmpId) {
                $query->where('from', $agentEmpId)->where('to', $customerPhone);
            })
            ->orderBy('timestamp', 'asc')
            ->get();
    } else {
        $messages = collect();
    }

    return view('chat.index', compact('contacts', 'messages', 'agentEmpId', 'requiredSkill', 'language'));
}



    public function getMessages($phoneNumber, $agentEmpId)
{
   
    Config::set('app.timezone', 'Asia/Colombo');

    
    $messages = Message::where(function ($query) use ($phoneNumber, $agentEmpId) {
            $query->where('from', $phoneNumber)->where('to', $agentEmpId);
        })
        ->orWhere(function ($query) use ($phoneNumber, $agentEmpId) {
            $query->where('from', $agentEmpId)->where('to', $phoneNumber);
        })
        ->orderBy('timestamp', 'asc')
        ->get()
        ->map(function ($message) {
            return [
                'message' => $message->message,
                'from' => $message->from,
                'document_id' => $message->document_id,
                'formatted_time' => Carbon::parse($message->timestamp)
                    ->setTimezone('Asia/Colombo')
                    ->format('h:i A'),
            ];
        });

    return response()->json($messages);
}



public function sendMessages(Request $request)
{
    try {


        $fromPhoneNumber = $request->input('from'); // WhatsApp sends the sender's number as 'from'

        // $fromPhoneNumber = '0716520832'; // Hardcoded for now

        // Retrieve assigned agent for this customer
        $customer = Customer::where('phone_number', $fromPhoneNumber)->first();

        if (!$customer || !$customer->assigned_agent_id) {
            return response()->json(['error' => 'No agent assigned to this customer.'], 404);
        }

        $toEmpId = $customer->assigned_agent_id;

        // Check if the assigned agent is still online
        $agent = Agent::where('emp_id', $toEmpId)
                      ->where('is_online', true)
                      ->first();

        // If assigned agent is offline, find a new available agent
        if (!$agent) {
            
            if (!$customer) {
                return response()->json(['error' => 'No skill information found for reassignment.'], 404);
            }

            $requiredSkill = $customer->required_skill;
            $language =  $customer->language;

            // Find the skill ID
            $skill = Skill::where('name', $requiredSkill)
                          ->where('language', $language)
                          ->first();

            if (!$skill) {
                return response()->json(['error' => 'No skill found for the assigned agent.'], 404);
            }

            // Find an available agent with the required skill
            $newAgent = Agent::whereIn('emp_id', AgentSkill::where('skill_id', $skill->id)->pluck('emp_id'))
                             ->where('is_online', true)
                             ->orderBy('active_chats', 'asc')
                             ->first();

            // If still no online agent is found, return an error
            if (!$newAgent) {
                return response()->json(['error' => 'No available agents at the moment. Please try again later.'], 404);
            }


           $previousAgentId = $customer->assigned_agent_id;

            // **Retrieve old messages before assigning a new agent**
            $oldMessages = Message::where(function ($query) use ($fromPhoneNumber, $previousAgentId) {
                $query->where('from', $fromPhoneNumber)
                      ->where('to', $previousAgentId);
            })->orWhere(function ($query) use ($fromPhoneNumber, $previousAgentId) {
                $query->where('from', $previousAgentId)  // Include messages FROM the previous agent
                      ->where('to', $fromPhoneNumber);
            })->get();

            // Update the customer's assigned agent
            $customer->update(['assigned_agent_id' => (string) $newAgent->emp_id]);

            $toEmpId = (string) $newAgent->emp_id;
            $agent = $newAgent;

            $fromPhoneNumber = (string) $fromPhoneNumber; // Ensure it is a string

            
     foreach ($oldMessages as $oldMessage) {
       if ($oldMessage->from == $fromPhoneNumber) {
        Message::create([
            'from' => $fromPhoneNumber,
            'to' => $toEmpId,
            'message' => "[Previous Chat - Customer] " . $oldMessage->message,
            'timestamp' => $oldMessage->timestamp,
            'is_read' => false,
            'document_id' => $oldMessage->document_id ?? null,
        ]);
    } else {
        // Message originally from the previous agent
        Message::create([
            'from' => $toEmpId,
            'to' => $fromPhoneNumber,
            'message' => "[Previous Chat - Agent] " . $oldMessage->message,
            'timestamp' => $oldMessage->timestamp,
            'is_read' => false,
            'document_id' => $oldMessage->document_id ?? null,
        ]);
    }
 }

 // **Handle New Document Upload**
$messageContent = $request->input('message');
$documentId = null;

if ($request->hasFile('document')) {
    $file = $request->file('document');

    try {
        $client = new Client("mongodb+srv://CNK:CNK811@cluster0.i5gyh.mongodb.net/chatbot_db?retryWrites=true&w=majority&appName=Cluster0");
        $database = $client->chatbot_db;
        $bucket = $database->selectGridFSBucket();

        // Upload document to GridFS
        $stream = fopen($file->getPathname(), 'rb');
        $fileId = $bucket->uploadFromStream($file->getClientOriginalName(), $stream, [
            'metadata' => [
                'mimeType' => $file->getMimeType(),
                'filename' => $file->getClientOriginalName(),
            ]
        ]);
        fclose($stream);

        $documentId = (string) $fileId;
        $messageContent = "[Document] " . $file->getClientOriginalName();
    } catch (\Exception $e) {
        \Log::error('Error uploading document: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to upload the document.'], 500);
    }
}

 }

        // Ensure the customer exists
        $customer = Customer::firstOrCreate(
            ['phone_number' => $fromPhoneNumber],
            ['name' => 'Rasika', 'created_at' => now(), 'updated_at' => now()]
        );


        // Cache key based on customer and agent to track the first message
        $cacheKey = 'summary_message_sent_for_' . $fromPhoneNumber . '_to_' . $toEmpId;

        if (!cache()->has($cacheKey)) {
            $summaryMessage = "This is a summary message for the agent about the first interaction with customer $fromPhoneNumber.";

            // Send summary message
            Message::create([
                'from' => $fromPhoneNumber,
                'to' => $toEmpId,
                'message' => $summaryMessage,
                'timestamp' => now(),
                'is_read' => false,
            ]);

            // Store in cache for 1 day
            cache([$cacheKey => true], now()->addDay());
        }

        
        $messageContent = $request->input('message');
        $documentId = null;

        // Handle file upload to MongoDB GridFS
        if ($request->hasFile('document')) {
            $file = $request->file('document');

            
            $client = new Client("mongodb+srv://CNK:CNK811@cluster0.i5gyh.mongodb.net/chatbot_db?retryWrites=true&w=majority&appName=Cluster0
");
            $database = $client->chatbot_db;
            $bucket = $database->selectGridFSBucket();

            // Store document in GridFS
            $stream = fopen($file->getPathname(), 'rb');
            $fileId = $bucket->uploadFromStream($file->getClientOriginalName(), $stream, [
                'metadata' => [
                    'mimeType' => $file->getMimeType(),
                    'filename' => $file->getClientOriginalName(),
                ]
            ]);
            fclose($stream);

            // Convert MongoDB ObjectId to string
            $documentId = (string) $fileId;

            // Set message content to indicate a document is sent
            $messageContent = "" . $file->getClientOriginalName();
        }

        // Save the message to MongoDB
        $message = Message::create([
            'from' => $fromPhoneNumber,
            'to' => $toEmpId,
            'message' => $messageContent,
            'document_id' => $documentId,
            'timestamp' => now(),
            'active_chat' => true,
        ]);

        // Increment active chats for the agent
        DB::table('agent')
          ->where('emp_id', $toEmpId)
          ->increment('active_chats', 1);

        return response()->json([
            'message' => $message,
            'agent' => $agent,
            'document_id' => $documentId,
        ]);
    } catch (\Exception $e) {
        \Log::error('Error sending message/document: ' . $e->getMessage());
        return response()->json(['error' => 'An error occurred while sending the message.'], 500);
    }
}


public function matchAgent(Request $request)
{
    $customerPhone = $request->input('customer_phone');
    $requiredSkill = $request->input('skill');
    $language = $request->input('language');

    // Find the skill ID
    $skill = Skill::where('name', $requiredSkill)
                  ->where('language', $language)
                  ->first();

    if (!$skill) {
        return response()->json(['message' => 'Skill with specified language not found!'], 404);
    }

    // Find available agents
    $agentSkill = AgentSkill::where('skill_id', $skill->id)->pluck('emp_id');

    if ($agentSkill->isEmpty()) {
        CustomerRequest::create([
            'customer_phone' => $customerPhone,
            'required_skill' => $requiredSkill,
            'language' => $language,
        ]);
        return response()->json(['message' => 'No available agents found! Please try again in a few minutes.'], 404);
    }

    // Find the least busy online agent
    $agent = Agent::whereIn('emp_id', $agentSkill)
                  ->where('is_online', true)
                  ->orderBy('active_chats', 'asc')
                  ->first();

    if (!$agent) {
        CustomerRequest::create([
            'customer_phone' => $customerPhone,
            'required_skill' => $requiredSkill,
            'language' => $language,
        ]);
        return response()->json(['message' => 'No available agents found! Please try again in a few minutes.'], 404);
    }


    Customer::updateOrCreate(
        ['phone_number' => $customerPhone],
        [
            'assigned_agent_id' => (string) $agent->emp_id,
            'required_skill' => $requiredSkill,
            'language' => $language,
        ]
    );
    

    return response()->json([
        'message' => 'Agent found!',
        'agent' => [
            'emp_id' => (string) $agent->emp_id,
            'group_code' => $agent->group_code,
        ],
    ]);
}



// public function handleIncomingMessage(Request $request)
// {
//     $message = $request->input('messages.0.text.body'); // The message content
//     $customerPhone = $request->input('messages.0.from'); // Sender's phone number

//     // Extract skill (e.g., "peo tv") from the message
//     preg_match('/(peo tv|other skills)/', $message, $matches);
//     $requiredSkill = $matches[0] ?? 'default skill'; // Default if no skill matched

//     // Extract language (e.g., "Tamil") from the message
//     preg_match('/(Tamil|English|Sinhala)/', $message, $langMatches);
//     $language = $langMatches[0] ?? 'English'; // Default if no language matched

//     // Now, call the matchAgent function to find a suitable agent
//     return $this->matchAgent($customerPhone, $requiredSkill, $language);
// }


public function download($documentId)
{
    try {
        $client = new  Client("mongodb+srv://CNK:CNK811@cluster0.i5gyh.mongodb.net/chatbot_db?retryWrites=true&w=majority&appName=Cluster0");
        $database = $client->chatbot_db;
        $bucket = $database->selectGridFSBucket();

        // Fetch the file from GridFS by the document ID (documentId)
        $file = $bucket->findOne(['_id' => new ObjectId($documentId)]);

        if (!$file) {
            \Log::error("File not found in GridFS with documentId: $documentId");
            return abort(404, 'Document not found');
        }

        // Create a temporary stream to serve the file
        $stream = $bucket->openDownloadStream($file->_id);

        // Set the correct headers to trigger file download
        return Response::stream(function() use ($stream) {
            fpassthru($stream);
        }, 200, [
            "Content-Type" => $file->metadata['mimeType'],
            "Content-Disposition" => "attachment; filename={$file->metadata['filename']}",
        ]);
    } catch (\Exception $e) {
        \Log::error('Error downloading document: ' . $e->getMessage());
        return abort(500, 'Error downloading document');
    }
}

public function view($documentId)
    {
        try {
            // Connect to MongoDB
            $client = new  Client("mongodb+srv://CNK:CNK811@cluster0.i5gyh.mongodb.net/chatbot_db?retryWrites=true&w=majority&appName=Cluster0");
            $database = $client->chatbot_db;  // Replace with your actual database name
            $bucket = $database->selectGridFSBucket();

            // Fetch the file from GridFS
            $file = $bucket->findOne(['_id' => new ObjectId($documentId)]);

            if (!$file) {
                \Log::error("File not found in GridFS with documentId: $documentId");
                return abort(404, 'Document not found');
            }

            // Open a stream for the file
            $stream = $bucket->openDownloadStream($file->_id);

            // Return the file as a response for inline viewing
            return response()->stream(
                function () use ($stream) {
                    fpassthru($stream);
                },
                200,
                [
                    "Content-Type" => $file->metadata['mimeType'],
                    "Content-Disposition" => "inline; filename={$file->metadata['filename']}",
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Error viewing document: ' . $e->getMessage());
            return abort(500, 'Error viewing document');
        }
    }
    
public function deactivateInactiveChats()
    {
        $timeoutThreshold = Carbon::now('UTC')->subMinutes(2);

        // Find messages that should be deactivated
        $messages = Message::where('active_chat', true)
            ->where('created_at', '<=', $timeoutThreshold)
            ->get();

        foreach ($messages as $message) {
            $message->update(['active_chat' => false]);

            // Decrease active chat count for the agent
            DB::table('agent')
                ->where('emp_id', $message->to)
                ->decrement('active_chats', 1);
        }

        return response()->json([
            'status' => 'success',
            'deactivated' => count($messages)
        ]);
    }

}
