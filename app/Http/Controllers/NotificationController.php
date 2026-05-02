<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            AppNotification::where('user_id', auth()->id())
                ->latest('created_at')
                ->take(10)
                ->get()
        );
    }

    public function readAll(): JsonResponse
    {
        AppNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
