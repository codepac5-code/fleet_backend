<?php

use App\Http\Services\Realtime\Authorize\Controller\AuthorizeRealtimeChannelController;
use App\Http\Services\Realtime\Authorize\Controller\ShardKeyController;
use Illuminate\Support\Facades\Route;

Route::post('authorize', AuthorizeRealtimeChannelController::class)->name('authorize');

// Clients ask for their room namespace instead of guessing it from their country.
Route::get('shard', ShardKeyController::class)->name('shard');
