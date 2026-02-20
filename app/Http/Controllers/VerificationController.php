<?php

namespace App\Http\Controllers;

use App\Models\VerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class VerificationController extends Controller
{
    /**
     * Submit verification request with ID upload
     */
    public function submit(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // If user is already verified, return early
        if ($user->is_verified) {
            return response()->json([
                'is_verified' => true,
                'message' => 'User is already verified',
            ]);
        }

        // Check if user has a pending request
        $existingRequest = VerificationRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return response()->json([
                'error' => 'You already have a pending verification request',
                'status' => 'pending',
            ], 400);
        }

        try {
            $validated = $request->validate([
                'selfie_front' => 'required|file|mimes:jpg,jpeg,png|max:10240', // 10MB max
                'selfie_back' => 'required|file|mimes:jpg,jpeg,png|max:10240', // 10MB max
            ]);

            // Store selfie with ID front
            $selfieFrontPath = $request->file('selfie_front')->store('verification-selfies', 'private');

            // Store selfie with ID back
            $selfieBackPath = $request->file('selfie_back')->store('verification-selfies', 'private');

            // Create verification request
            $verificationRequest = VerificationRequest::create([
                'user_id' => $user->id,
                'selfie_front_path' => $selfieFrontPath,
                'selfie_back_path' => $selfieBackPath,
                'status' => 'pending',
            ]);

            return response()->json([
                'message' => 'Verification request submitted successfully',
                'status' => 'pending',
                'request_id' => $verificationRequest->id,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to submit verification request',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current verification status
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $pendingRequest = VerificationRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        $rejectedRequest = VerificationRequest::where('user_id', $user->id)
            ->where('status', 'rejected')
            ->latest()
            ->first();

        return response()->json([
            'is_verified' => $user->is_verified,
            'verified_at' => $user->verified_at?->toIso8601String(),
            'has_pending_request' => (bool) $pendingRequest,
            'was_rejected' => (bool) $rejectedRequest,
            'rejection_reason' => $rejectedRequest?->admin_notes,
        ]);
    }

    /**
     * Admin: Get all pending verification requests
     */
    public function index(Request $request): JsonResponse
    {
        if (!$request->user()?->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $requests = VerificationRequest::with(['user', 'reviewer'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(20);

        return response()->json($requests);
    }

    /**
     * Admin: View verification request details
     */
    public function show(Request $request, VerificationRequest $verificationRequest): JsonResponse
    {
        if (!$request->user()?->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $verificationRequest->load(['user', 'reviewer']);

        // Generate temporary signed URLs for viewing selfies
        $selfieFrontUrl = Storage::disk('private')->temporaryUrl(
            $verificationRequest->selfie_front_path,
            now()->addMinutes(5)
        );

        $selfieBackUrl = Storage::disk('private')->temporaryUrl(
            $verificationRequest->selfie_back_path,
            now()->addMinutes(5)
        );

        return response()->json([
            'request' => $verificationRequest,
            'selfie_front_url' => $selfieFrontUrl,
            'selfie_back_url' => $selfieBackUrl,
        ]);
    }

    /**
     * Admin: Approve verification request
     */
    public function approve(Request $request, VerificationRequest $verificationRequest): JsonResponse
    {
        $admin = $request->user();

        if (!$admin?->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$verificationRequest->isPending()) {
            return response()->json(['error' => 'Request is not pending'], 400);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $verificationRequest->approve($admin, $validated['notes'] ?? null);

        return response()->json([
            'message' => 'Verification request approved',
            'request' => $verificationRequest->fresh(),
        ]);
    }

    /**
     * Admin: Reject verification request
     */
    public function reject(Request $request, VerificationRequest $verificationRequest): JsonResponse
    {
        $admin = $request->user();

        if (!$admin?->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$verificationRequest->isPending()) {
            return response()->json(['error' => 'Request is not pending'], 400);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $verificationRequest->reject($admin, $validated['reason']);

        return response()->json([
            'message' => 'Verification request rejected',
            'request' => $verificationRequest->fresh(),
        ]);
    }
}
