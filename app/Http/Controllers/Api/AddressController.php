<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAddressRequest;
use App\Http\Requests\Api\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = Address::where('user_id', $request->user()->id)->get();

        return response()->json(AddressResource::collection($addresses));
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $address = Address::create(array_merge($request->validated(), ['user_id' => $request->user()->id]));

        return response()->json(new AddressResource($address), 201);
    }

    public function update(UpdateAddressRequest $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $address->update($request->validated());

        return response()->json(new AddressResource($address));
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $address->delete();

        return response()->json(['message' => 'Address deleted.']);
    }

    public function setDefault(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        DB::transaction(function () use ($request, $address) {
            Address::where('user_id', $request->user()->id)->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return response()->json(new AddressResource($address->fresh()));
    }
}
