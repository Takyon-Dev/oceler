<?php

namespace App\Http\Controllers;

use App\Models\Reply;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReplyController extends Controller
{
    /**
     * Store a newly created reply.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $messageId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, int $messageId): RedirectResponse
    {
        $validated = $request->validate([
            'body' => 'required|string'
        ]);

        $message = Message::findOrFail($messageId);

        $reply = Reply::create([
            'message_id' => $messageId,
            'user_id' => Auth::id(),
            'body' => $validated['body']
        ]);

        Log::info('Reply created for message ' . $messageId . ' by user ' . Auth::id());

        return redirect()->route('messages.show', $messageId)
            ->with('success', 'Reply sent successfully');
    }

    /**
     * Update the specified reply.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'body' => 'required|string'
        ]);

        $reply = Reply::where('user_id', Auth::id())
            ->findOrFail($id);

        $reply->update(['body' => $validated['body']]);

        Log::info('Reply ' . $id . ' updated by user ' . Auth::id());

        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified reply.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $reply = Reply::where('user_id', Auth::id())
            ->findOrFail($id);

        $reply->delete();

        Log::info('Reply ' . $id . ' deleted by user ' . Auth::id());

        return response()->json(['success' => true]);
    }
}
