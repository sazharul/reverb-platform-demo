<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\App;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReverbChannelAuthController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'app_key' => 'required|string|max:64',
            'socket_id' => 'required|string|max:100',
            'channel_name' => 'required|string|max:200',
        ]);

        $user = auth()->user();

        $query = App::query()
            ->where('app_key', $data['app_key'])
            ->where('is_active', true);

        if (! $user->isAdminType()) {
            $query->where('user_id', $user->id);
        }

        $app = $query->first();

        if (! $app) {
            return response()->json(['message' => 'App not found or unauthorized.'], 404);
        }

        // Auth is only needed for private- and presence- channels
        if (! str_starts_with($data['channel_name'], 'private-') && ! str_starts_with($data['channel_name'], 'presence-')) {
            return response()->json(['message' => 'Only private and presence channels require auth.'], 422);
        }

        $authSignature = hash_hmac(
            'sha256',
            $data['socket_id'].':'.$data['channel_name'],
            $app->revealSecret(),
        );

        return response()->json([
            'auth' => $app->app_key.':'.$authSignature,
        ]);
    }
}

