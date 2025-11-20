<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\ModePrediction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModePredictionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
          'device_ids' => ['nullable', 'array'],
          'device_ids.*' => ['integer'],
          'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        $deviceIds = $data['device_ids'] ?? [];
        $limit = $data['limit'] ?? 100;

        $query = ModePrediction::query()
            ->where('user_id', $user->id)
            ->orderByDesc('predicted_at');

        if (!empty($deviceIds)) {
            $query->whereIn('device_id', $deviceIds);
        }

        $items = $query->limit($limit)->get();

        return response()->json([
            'ok' => true,
            'items' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'device_id'   => ['required', 'integer'],
            'mode'        => ['required', 'string', Rule::in(['idle', 'normal', 'high', 'peak', 'fault', 'offline'])],
            'confidence'  => ['nullable', 'numeric', 'min:0', 'max:1'],
            'payload'     => ['nullable', 'array'],
            'predicted_at'=> ['nullable', 'date'],
        ]);

        $device = Device::where('id', $data['device_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $prediction = ModePrediction::create([
            'user_id'      => $user->id,
            'device_id'    => $device->id,
            'mode'         => $data['mode'],
            'confidence'   => $data['confidence'] ?? 0,
            'payload'      => $data['payload'] ?? null,
            'predicted_at' => $data['predicted_at'] ?? now(),
        ]);

        return response()->json([
            'ok' => true,
            'prediction' => $prediction,
        ], 201);
    }
}
