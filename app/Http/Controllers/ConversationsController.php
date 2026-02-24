<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Recipient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationsController extends Controller
{
    /**
     * List all conversations for the authenticated user.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index()
    {
        $user = Auth::user();
        return $user->conversations()->with([
            'lastMessage',
            'participants' => function($builder) use ($user) {
                $builder->where('id', '<>', $user->id);
            },])
            ->withCount([
                'recipients as new_messages' => function($builder) use ($user) {
                    $builder->where('recipients.user_id', '=', $user->id)
                        ->whereNull('read_at');
                }
            ])
            ->paginate();
    }

    /**
     * Display a specific conversation with participant and unread message counts.
     *
     * @param  int  $id
     * @return \App\Models\Conversation
     */
    public function show(int $id): Conversation
    {
        $user = Auth::user();
        return $user->conversations()->with([
            'lastMessage',
            'participants' => function($builder) use ($user) {
                $builder->where('id', '<>', $user->id);
            },])
            ->withCount([
                'recipients as new_messages' => function($builder) use ($user) {
                    $builder->where('recipients.user_id', '=', $user->id)
                        ->whereNull('read_at');
                }
            ])
            ->findOrFail($id);
    }

    /**
     * Add a participant to a conversation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Conversation  $conversation
     * @return void
     */
    public function addParticipant(Request $request, Conversation $conversation): void
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id'
        ]);

        $conversation->participants()->attach($request->post('user_id'), [
            'joined_at' => Carbon::now(),
        ]);
    }

    /**
     * Remove a participant from a conversation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Conversation  $conversation
     * @return void
     */
    public function removeParticipant(Request $request, Conversation $conversation): void
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id'
        ]);

        $conversation->participants()->detach($request->post('user_id'));
    }

    /**
     * Mark all unread messages in a conversation as read.
     *
     * @param  int  $id
     * @return array
     */
    public function markAsRead(int $id): array
    {
        Recipient::where ('user_id', '=', Auth::id())
            ->whereNull('read_at')
            ->whereRaw ('message_id IN (
            SELECT id FROM messages where conversation_id = ?)',
            [$id])
            ->update(['read_at' => Carbon::now()]);

        broadcast(new \App\Events\MessageRead($id));

        return [
            'message' => 'Messages marked as read'
        ];
    }

    /**
     * Delete an entire conversation's message history for the user.
     *
     * @param  int  $id
     * @return array
     */
    public function destroy(int $id): array
    {
        Recipient::where ('user_id', '=', Auth::id())
            ->whereRaw ('message_id IN (
            SELECT id FROM messages where conversation_id = ?)',
                [$id])
            ->delete();

        return [
            'message' => 'Conversation deleted'
        ];
    }
}
