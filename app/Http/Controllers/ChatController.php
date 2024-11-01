<?php

namespace App\Http\Controllers;

use App\Http\Requests\Chat\StoreRequest;
use App\Http\Resources\Chat\ChatResource;
use App\Http\Resources\Message\MessageResource;
use App\Http\Resources\User\UserResource;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function index()
    {

        $users = User::where('id', '!=', auth()->id())->get();
        $users = UserResource::collection($users)->resolve();

//        $chats = auth()->user()->chats()->has('messages')->get();
        $chats = auth()->user()->chats()->get();
        $chats = ChatResource::collection($chats)->resolve();


        return inertia('Chat/Index', compact('users', 'chats'));
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $userIds = array_merge($data['users'], [auth()->id()]);
        sort($userIds);
        $userIdsString = implode('-', $userIds);

        try {
            DB::beginTransaction();

            $chat = Chat::updateOrCreate([
                'users' => $userIdsString
            ], [
                'title' => $data['title']
            ]);

            $chat->users()->sync($userIds);

            DB::commit();

        } catch (\Exception $exception) {
            DB::rollBack();
        }

        return redirect()->route('chats.show', $chat->id);

    }

    public function show(Chat $chat) {
        $users = $chat->users()->get();
        $users = UserResource::collection($users)->resolve();

        $messages = $chat->messages()->with('user')->get();
        $messages = MessageResource::collection($messages)->resolve();

        $chat = ChatResource::make($chat)->resolve();

        return inertia('Chat/Show', compact('chat', 'users', 'messages'));
    }
}
