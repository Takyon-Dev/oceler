<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MessageController extends Controller
{
    /**
     * Display a listing of messages.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $messages = Message::with('sender', 'receiver')
            ->where('receiver_id', Auth::id())
            ->orWhere('sender_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('messages.index', compact('messages'));
    }

    /**
     * Show the form for creating a new message.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        $users = User::where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get();

        return view('messages.create', compact('users'));
    }

    /**
     * Store a newly created message.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string'
        ]);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'read' => false
        ]);

        Log::info('Message sent from ' . Auth::id() . ' to ' . $validated['receiver_id']);

        return redirect()->route('messages.index')
            ->with('success', 'Message sent successfully');
    }

    /**
     * Display the specified message.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show(int $id): View
    {
        $message = Message::with('sender', 'receiver')
            ->where(function($query) {
                $query->where('sender_id', Auth::id())
                    ->orWhere('receiver_id', Auth::id());
            })
            ->findOrFail($id);

        if ($message->receiver_id === Auth::id() && !$message->read) {
            $message->update(['read' => true]);
        }

        return view('messages.show', compact('message'));
    }

    /**
     * Mark a message as read.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(int $id): JsonResponse
    {
        $message = Message::where('receiver_id', Auth::id())
            ->findOrFail($id);

        $message->update(['read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete the specified message.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        $message = Message::where(function($query) {
            $query->where('sender_id', Auth::id())
                ->orWhere('receiver_id', Auth::id());
        })->findOrFail($id);

        $message->delete();

        Log::info('Message ' . $id . ' deleted by user ' . Auth::id());

        return redirect()->route('messages.index')
            ->with('success', 'Message deleted successfully');
    }

    /**
     * Get unread message count.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadCount(): JsonResponse
    {
        $count = Message::where('receiver_id', Auth::id())
            ->where('read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get recent messages.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recent(): JsonResponse
    {
        $messages = Message::with('sender')
            ->where('receiver_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json($messages);
    }
}
