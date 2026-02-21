<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'label',
        'type',
        'last_message_id'
    ];

    /**
     * The users that belong to the conversation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'participants')
            ->withPivot('joined_at', 'role');
    }

    /**
     * Get the messages for the conversation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id', 'id');
    }

    /**
     * Get the user that owns the conversation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the last message in the conversation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lastMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'last_message_id', 'id')
            ->withDefault();
    }

    /**
     * Get all recipients for the conversation's messages.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function recipients(): HasManyThrough
    {
        return $this->hasManyThrough(
            Recipient::class,
            Message::class,
            'conversation_id',
            'message_id',
            'id',
            'id'
        );
    }
}
