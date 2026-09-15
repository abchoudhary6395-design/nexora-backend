<?php

use Illuminate\Support\Facades\Route;

// This is an API-only backend — the SPA is served separately by Vite/the
// built frontend. This route just confirms the API is running.
Route::get('/', function () {
    return response()->json(['message' => 'Nexora Business OS API', 'status' => 'ok']);
});
