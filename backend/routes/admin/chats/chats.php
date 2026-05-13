<?php


use App\Http\Controllers\Admin\Chat\ChatController;

Route::group(['prefix' => 'chats'], function () {
    Route::get('/passive', [ChatController::class, 'passiveChats']);
    Route::get('/blocked', [ChatController::class, 'blockedChats']);
    Route::get('/{chat}', [ChatController::class, 'show']);
    Route::patch('/{chat}', [ChatController::class, 'update']);
});
