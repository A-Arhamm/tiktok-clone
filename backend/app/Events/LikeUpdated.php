<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LikeUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $postId;
    public $likesCount;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($postId, $likesCount)
    {
        $this->postId = $postId;
        $this->likesCount = $likesCount;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('post.likes.' . $this->postId);
    }

    public function broadcastWith()
    {
        return [
            'postId' => $this->postId,
            'likesCount' => $this->likesCount,
        ];
    }
}
