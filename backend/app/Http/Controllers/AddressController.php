<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class AddressController extends Controller
{
    private function ensureBuyer(): ?JsonResponse
    {
        if (JWTAuth::parseToken()->getPayload()->get('active_role') !== 'buyer') {
            return response()->json(['message' => 'Hanya Buyer yang bisa mengakses alamat.'], 403);
        }
        return null;
    }

    public function index(Request $request): JsonResponse
    {
        if ($err = $this->ensureBuyer()) return $err;

        $addresses = $request->user()->addresses()->orderByDesc('is_default')->get();
        return response()->json(['data' => $addresses]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($err = $this->ensureBuyer()) return $err;

        $data = $request->validate([
            'label'          => 'required|string|max:50',
            'recipient_name' => 'required|string|max:100',
            'phone'          => 'required|string|max:20',
            'full_address'   => 'required|string',
            'province'       => 'required|string|max:100',
            'city'           => 'required|string|max:100',
            'district'       => 'required|string|max:100',
            'postal_code'    => 'required|string|max:10',
            'is_default'     => 'boolean',
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user, $data) {
            if (! empty($data['is_default'])) {
                $user->addresses()->update(['is_default' => false]);
            }
            if ($user->addresses()->count() === 0) {
                $data['is_default'] = true;
            }
            $user->addresses()->create($data);
        });

        return response()->json([
            'message' => 'Alamat berhasil ditambahkan.',
            'data'    => $user->addresses()->orderByDesc('is_default')->get(),
        ], 201);
    }

    public function update(Request $request, Address $address): JsonResponse
    {
        if ($err = $this->ensureBuyer()) return $err;

        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Bukan alamat kamu.'], 403);
        }

        $data = $request->validate([
            'label'          => 'required|string|max:50',
            'recipient_name' => 'required|string|max:100',
            'phone'          => 'required|string|max:20',
            'full_address'   => 'required|string',
            'province'       => 'required|string|max:100',
            'city'           => 'required|string|max:100',
            'district'       => 'required|string|max:100',
            'postal_code'    => 'required|string|max:10',
            'is_default'     => 'boolean',
        ]);

        DB::transaction(function () use ($request, $address, $data) {
            if (! empty($data['is_default'])) {
                $request->user()->addresses()->update(['is_default' => false]);
            }
            $address->update($data);
        });

        return response()->json(['message' => 'Alamat diperbarui.', 'data' => $address->fresh()]);
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        if ($err = $this->ensureBuyer()) return $err;

        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Bukan alamat kamu.'], 403);
        }

        $address->delete();

        return response()->json(['message' => 'Alamat dihapus.']);
    }

    public function setDefault(Request $request, Address $address): JsonResponse
    {
        if ($err = $this->ensureBuyer()) return $err;

        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Bukan alamat kamu.'], 403);
        }

        DB::transaction(function () use ($request, $address) {
            $request->user()->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return response()->json(['message' => 'Alamat utama diperbarui.']);
    }
}
