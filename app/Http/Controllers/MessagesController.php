<?php

namespace App\Http\Controllers;

use App\Events\MessageCreated;
use App\Events\MessageDeleted;
use App\Events\MessageUpdated;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Throwable;

class MessagesController extends Controller
{

    /**
     * Fetch all messages for a specific conversation.
     *
     * @param  int  $id
     * @return array
     */
    public function index(int $id): array
    {
        $user = Auth::user();
        $conversation = $user->conversations()
            ->with(['participants' => function ($query) use ($user) {
                $query->where('id', '<>', $user->id);
            }])
            ->findOrFail($id);

        $messages = $conversation->messages()
            ->withTrashed()
            ->with(['user', 'recipients'])
            ->where(function($query) use ($user) {
                $query
                    ->where(function($query) use ($user) {
                        $query->where('user_id', $user->id)
                            ->whereNull('deleted_at');
                    })
                    ->orWhereRaw('id IN (
                        SELECT message_id FROM recipients
                        WHERE recipients.message_id = messages.id
                        AND recipients.user_id = ?
                        AND recipients.deleted_at IS NULL
                    )', [$user->id]);
            })
            ->latest()
            ->paginate();

        return [
            'conversation' => $conversation,
            'messages' =>  $messages,
        ];
    }


    /**
     * Store a new message (text or attachment) in a conversation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Models\Message
     */
    public function store(Request $request): \App\Models\Message
    {
        $request->validate([
            'message' => [Rule::requiredIf(function () use ($request) {
                return !$request->hasFile('attachment');
            }), 'nullable', 'string'],

            'attachment' => ['file'],

            'conversation_id' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->input('user_id');
                }),
                'int',
                'exists:conversations,id'
            ],
            'user_id' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->input('conversation_id');
                }),
                'int',
                'exists:users,id'
            ],
        ]);

        $user = Auth::user();

        $conversation_id = $request->post('conversation_id');
        $user_id = $request->post('user_id');



        DB::beginTransaction();

        try {

            if ($conversation_id) {
                $conversation = $user->conversations()->findOrFail($conversation_id);
            } else {
                $conversation = Conversation::where('type', 'peer')
                    ->whereHas('participants', function ($builder) use ($user_id, $user) {
                        $builder->join('participants as participants2', 'participants2.conversation_id', '=', 'participants.conversation_id')
                            ->where('participants.user_id', '=', $user_id)
                            ->where('participants2.user_id', '=', $user->id);
                    })
                    ->first();

                if (!$conversation) {
                    $conversation = Conversation::create ([
                        'user_id' => $user->id,
                        'type' => 'peer',
                    ]);

                    $conversation->participants()->attach([
                        $user->id => ['joined_at' => now()] ,
                    $user_id => ['joined_at' => now()]
                    ]);
                }

            }

            $type = 'text';
            $message = $request->post('message');
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $message = [
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mimetype' => $file->getMimeType(),
                    'file_path' => $file->store('attachments', [
                        'disk' => 'public'
                    ]),
                ];
                $type = 'attachment';
            }

            $message = $conversation->messages()->create([
                'user_id' => $user->id,
                'type' => $type,
                'body' => $message,
            ]);

            DB::statement('INSERT INTO recipients (user_id, message_id)
    SELECT user_id, ? FROM participants
    WHERE conversation_id = ?
    AND user_id <> ?',
                [
                    $message->id,
                    $conversation->id,
                    $user->id
                ]
            );


            $conversation->update([
                'last_message_id' => $message->id
            ]);

            DB::commit();

            $message->load(['user', 'recipients']);
            broadcast(new MessageCreated($message));

        }
        catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $message;

    }



    public function show($id)
    {
        //
    }


    public function update(Request $request, int $id): Message
    {
        // 1) Validate input
        $validated = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $userId = Auth::id();

        // 2) Find the message that belongs to the current user
        $message = Message::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        // 3) Only allow editing text messages (not attachments)
        if ($message->type !== 'text') {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Only text messages can be edited.');
        }

        // 4) Update body and edited_at
        $message->body = $validated['message'];
        $message->edited_at = now();
        $message->save();

        // 5) Load relations so frontend has everything it expects
        $message->load(['user', 'recipients', 'conversation.participants']);

        broadcast(new MessageUpdated($message));

        return $message;
    }


    /**
     * Soft-delete a message for the authenticated user.
     *
     * @param  int  $id
     * @return array
     */
    public function destroy(int $id): array
    {
        $userId = Auth::id();

        // Find the message (or fail with 404)
        $message = Message::findOrFail($id);

        // CASE 1: current user is the author → soft delete for everyone
        if ($message->user_id === $userId) {
            // Soft delete the message row (sets messages.deleted_at)
            $message->delete();

            // Optionally also delete all recipient rows
            Recipient::where('message_id', $id)->delete();

            $message->load(['user', 'recipients', 'conversation.participants']);
            broadcast(new MessageDeleted($message));

            return [
                'message' => 'deleted_for_everyone',
            ];
        }

        // CASE 2: current user is just a recipient → delete only for this user
        Recipient::where([
            'user_id' => $userId,
            'message_id' => $id,
        ])->delete();

        return [
            'message' => 'deleted',
        ];
    }
}
