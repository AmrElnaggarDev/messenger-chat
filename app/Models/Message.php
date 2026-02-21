<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id', 'user_id', 'body', 'type'
    ];

    protected $casts = [
        'body' => 'json'
    ];

    /**
     * Get the conversation that owns the message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the user that sent the message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)
            ->withDefault([
                'name' => __('User')
            ]);
    }

    /**
     * Get the recipients for the message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'recipients')
            ->withPivot('read_at', 'deleted_at');
    }
}
