<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class MessageRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversation_id;
    public $user_id;

    /**
     * Create a new event instance.
     *
     * @param  int  $conversation_id
     * @return void
     */
    public function __construct(int $conversation_id)
    {
        $this->conversation_id = $conversation_id;
        $this->user_id = Auth::id();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn(): Channel|array
    {
        $conversation = Conversation::findOrFail($this->conversation_id);
        $other_user = $conversation->participants()
            ->where('user_id', '<>', Auth::id())
            ->first();

        if ($other_user) {
            return new PresenceChannel('Messenger.' . $other_user->id);
        }
        
        return [];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'message-read';
    }
}
