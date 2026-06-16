<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function index(): JsonResponse
    {
        $reviews = Review::latest('created_at')->paginate(20);
        return response()->json($reviews);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'reviewer_name' => 'required|string|max:100',
            'rating'        => 'required|integer|min:1|max:5',
            'comment'       => 'required|string|max:2000',
        ]);

        $user = Auth::guard('api')->user();
        if ($user) {
            $data['user_id'] = $user->id;
        }

        $review = Review::create($data);
        $review->created_at = now();
        $review->save();

        return response()->json(['message' => 'Ulasan berhasil disimpan.', 'data' => $review], 201);
    }

    public function update(Request $request, Review $review): JsonResponse
    {
        $user = Auth::guard('api')->user();

        if (!$user || $review->user_id !== $user->id) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        $data = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:2000',
        ]);

        $review->update(array_merge($data, ['updated_at' => now()]));

        return response()->json(['message' => 'Ulasan diperbarui.', 'data' => $review]);
    }
}
