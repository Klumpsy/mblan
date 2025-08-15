<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Signup;
use App\Models\Edition;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DiscordController extends Controller
{
    public function addBeer(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'discord_id' => 'required|string'
            ]);

            // Find user by discord_id
            $user = User::where('discord_id', $request->discord_id)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found. Please link your Discord account on the website first.'
                ], 404);
            }

            $currentEdition = Edition::where('year', now()->year)->first();

            if (!$currentEdition) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active edition found.'
                ], 404);
            }

            // Find the user's signup for the current edition
            $signup = Signup::where('user_id', $user->id)
                ->where('edition_id', $currentEdition->id)
                ->where('confirmed', true)
                ->first();

            if (!$signup) {
                return response()->json([
                    'success' => false,
                    'message' => 'You need to sign up for the current edition first.' . $user->id . ' ' . $currentEdition->id
                ], 404);
            }

            // Check if signup is confirmed
            if (!$signup->confirmed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your signup needs to be confirmed first.'
                ], 403);
            }

            // Check cooldown (1 minute)
            if ($signup->last_beer_at && $signup->last_beer_at->diffInSeconds(now()) < 60) {
                $remainingSeconds = 60 - $signup->last_beer_at->diffInSeconds(now());
                return response()->json([
                    'success' => false,
                    'message' => "You can have another beer in {$remainingSeconds} seconds."
                ], 429);
            }

            // Increment beer count
            $signup->increment('beer_count');
            $signup->update(['last_beer_at' => now()]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->discord_id,
                    'name' => $user->name,
                    'beer_count' => $signup->beer_count,
                    'last_beer_at' => $signup->last_beer_at->toISOString(),
                    'edition' => $currentEdition->name ?? 'Current Edition'
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Discord beer add error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while adding beer.'
            ], 500);
        }
    }

    public function getBeerLeaderboard(): JsonResponse
    {
        try {
            $currentEdition = Edition::where('year', now()->year)->first();

            if (!$currentEdition) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active edition found.'
                ], 404);
            }

            // Get leaderboard for current edition
            $signups = Signup::with('user')
                ->where('edition_id', $currentEdition->id)
                ->where('confirmed', true)
                ->where('beer_count', '>', 0)
                ->whereHas('user', function ($query) {
                    $query->whereNotNull('discord_id');
                })
                ->orderBy('beer_count', 'desc')
                ->orderBy('last_beer_at', 'asc') // Tie-breaker: earlier beer wins
                ->get();

            $formattedUsers = $signups->map(function ($signup) {
                return [
                    'id' => $signup->user->discord_id,
                    'name' => $signup->user->name,
                    'beer_count' => $signup->beer_count,
                    'last_beer_at' => $signup->last_beer_at ? $signup->last_beer_at->toISOString() : null
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedUsers,
                'meta' => [
                    'edition' => $currentEdition->name ?? 'Current Edition',
                    'total_participants' => $signups->count(),
                    'total_beers' => $signups->sum('beer_count')
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Discord beer leaderboard error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching leaderboard.'
            ], 500);
        }
    }

    public function addPizza(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'discord_id' => 'required|string',
                'pizza_order' => 'required|string|max:500'
            ]);

            // Find user by discord_id
            $user = User::where('discord_id', $request->discord_id)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found. Please link your Discord account on the website first.'
                ], 404);
            }

            $currentEdition = Edition::where('year', now()->year)->first();

            if (!$currentEdition) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active edition found.'
                ], 404);
            }

            // Find the user's signup for the current edition
            $signup = Signup::where('user_id', $user->id)
                ->where('edition_id', $currentEdition->id)
                ->where('confirmed', true)
                ->first();

            if (!$signup) {
                return response()->json([
                    'success' => false,
                    'message' => 'You need to sign up for the current edition first.'
                ], 404);
            }

            // Simply save the pizza order
            $signup->update(['pizza_order' => $request->pizza_order]);

            return response()->json([
                'success' => true,
                'message' => 'Pizza order saved successfully!'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Discord pizza save error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving pizza order.'
            ], 500);
        }
    }
}
