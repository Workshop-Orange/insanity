<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\SanityDeployment;

class SanityDeploymentStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sanityDeployment;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(SanityDeployment $sanityDeployment)
    {
        $this->sanityDeployment = $sanityDeployment;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [
            new Channel('sanityDeployment.' . $this->sanityDeployment->id),
            new Channel('sanityMainRepo.' . $this->sanityDeployment->sanityMainRepo->id),
        ];
    }

    public function broadcastWith()
    {
        $message['deployment'] = $this->sanityDeployment->only('id', 'deployment_status', 'deployment_message');
        $message['mainRepository'] = $this->sanityDeployment->sanityMainRepo->only('id','title');

        return $message;
    }
}
