<?php

namespace App\Jobs;

use App\Events\StoreMessageStatusEvent;
use App\Models\MessageStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class StoreMessageStatusJob implements ShouldQueue
{
    use Queueable;

    private $data;
    private $message;

    /**
     * Create a new job instance.
     */
    public function __construct($data, $message)
    {
        //
        $this->data = $data;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->data['user_ids'] as $user_id) {

            MessageStatus::create([
                'chat_id' => $this->data['chat_id'],
                'message_id' => $this->message->id,
                'user_id' => $user_id,
            ]);

            $count = MessageStatus::where('chat_id', $this->data['chat_id'])
                ->where('user_id', $user_id)
                ->where('is_read', false)
                ->count();


            broadcast(new StoreMessageStatusEvent($count, $this->data['chat_id'], $user_id, $this->message));
        }
    }
}
