<?php

namespace App\Http\Controllers\Admin\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\UpdateChatRequest;
use App\Http\Resources\Chat\ChatResource;
use App\Models\CustomTelegraphChat;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChatController extends Controller
{
    public function passiveChats(): AnonymousResourceCollection
    {
        $query = CustomTelegraphChat::query()
            ->where(function ($query) {
                $query->whereDoesntHave('subscriptions')
                    ->orWhere(function ($q) {
                        $q->whereHas('subscriptions')
                            ->whereDoesntHave('subscriptions', function ($subQ) {
                                $subQ->has('vpnKeys');
                            });
                    });
            })
            ->orderBy('created_at', 'desc');

        $paginated = $query->paginate(50);

        return ChatResource::collection($paginated);
    }

    public function blockedChats(Request $request): AnonymousResourceCollection
    {
        $isTrial = $request->input('is_trial');;

        if ($isTrial !== null) {
            $isTrial = filter_var($isTrial, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        $query = Subscription::findBlocked($isTrial);

        $chatIds = $query->pluck('telegraph_chat_id')->all();
        $chatsQuery = CustomTelegraphChat::query()
            ->whereIn('id', $chatIds)
            ->orderBy('created_at', 'desc');

        $paginated = $chatsQuery->paginate(50);

        return ChatResource::collection($paginated);
    }

    public function show(CustomTelegraphChat $chat): ChatResource
    {
        $chat->load([
            'subscriptions' => function ($query) {
                $query->with(['duration', 'vpnKeys'])
                    ->orderBy('created_at', 'desc');
            }
        ]);

        return new ChatResource($chat);
    }

    public function update(CustomTelegraphChat $chat, UpdateChatRequest $request): ChatResource
    {
        $chat->update($request->validated());

        return new ChatResource($chat);
    }
}
