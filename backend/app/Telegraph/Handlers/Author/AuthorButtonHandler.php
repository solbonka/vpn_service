<?php

namespace App\Telegraph\Handlers\Author;

use App\Models\AuthorInfo;
use App\Telegraph\Handlers\BaseMessageHandler;
use Illuminate\Support\Facades\Storage;

class AuthorButtonHandler extends BaseMessageHandler
{

    public function canHandle(string $message): bool
    {
        return $message === '🙎🏻‍♂️ Об авторе';

    }

    public function handle(string $message): void
    {
        $authorImgPath = Storage::disk('public')->path('images/author/author.jpeg');

        $authorInfo = AuthorInfo::query()->first();

        $this->chat
            ->message($authorInfo->text)
            ->photo($authorImgPath)
            ->markdown()
            ->send();
    }
}
