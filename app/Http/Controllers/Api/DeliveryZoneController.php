<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\DeliveryZoneResource;
use App\Models\DeliveryZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class DeliveryZoneController extends Controller
{
    public function index(): JsonResponse
    {
        $zones = DeliveryZone::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json(DeliveryZoneResource::collection($zones));
    }
}
