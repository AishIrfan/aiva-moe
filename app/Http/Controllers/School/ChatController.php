<?php

namespace App\Http\Controllers\School;

use App\Models\ChatBroadcast;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class ChatController extends SchoolContextController
{
    public function index(Request $request)
    {
        $school = $this->requireSchool($request);
        $conversations = Conversation::where('school_id', $school->id)
            ->with(['student', 'teacher'])
            ->withCount('messages')
            ->latest('last_message_at')->paginate(30);
        $active = $conversations->firstWhere('id', (int) $request->get('conv'));
        $messages = $active?->messages()->orderBy('id')->get() ?? collect();
        $broadcasts = ChatBroadcast::where('school_id', $school->id)->latest()->limit(20)->get();
        return view('school.chat', compact('school', 'conversations', 'active', 'messages', 'broadcasts'));
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        $this->ensureOwned($request, $conversation);
        $data = $request->validate(['body' => ['required', 'string', 'max:4000']]);
        $msg = Message::create([
            'conversation_id' => $conversation->id,
            'sender_user_id' => $request->user()->id,
            'sender_role' => 'teacher',
            'body' => $data['body'],
        ]);
        $conversation->update(['last_message_at' => now()]);
        AuditLogger::log('chat.send', $msg);
        return back()->with('status', 'Message sent.');
    }

    public function setStatus(Request $request, Conversation $conversation)
    {
        $this->ensureOwned($request, $conversation);
        $data = $request->validate(['status' => ['required', 'in:open,resolved,archived']]);
        $conversation->update($data);
        AuditLogger::log('chat.status', $conversation, [], $data);
        return back()->with('status', 'Conversation '.$data['status'].'.');
    }

    public function flag(Request $request, Message $message)
    {
        $this->ensureOwned($request, $message, 'conversation');
        $message->update(['flagged' => ! $message->flagged]);
        AuditLogger::log('chat.flag', $message, [], ['flagged' => $message->flagged]);
        return back()->with('status', 'Message flag toggled.');
    }

    public function broadcast(Request $request)
    {
        $data = $request->validate([
            'audience' => ['required', 'in:all_parents,class,grade,custom'],
            'audience_ref_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:4000'],
        ]);
        $school = $this->requireSchool($request);
        $bc = ChatBroadcast::create(array_merge($data, [
            'school_id' => $school->id,
            'user_id' => $request->user()->id,
            'sent_at' => now(),
        ]));
        AuditLogger::log('chat.broadcast', $bc, [], $data);
        return back()->with('status', 'Broadcast sent.');
    }
}
