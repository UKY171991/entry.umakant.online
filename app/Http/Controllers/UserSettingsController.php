<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserSettingsController extends Controller
{
    /**
     * Display the user settings page.
     */
    public function index()
    {
        $user = Auth::user();
        return view('settings.index', compact('user'));
    }

    /**
     * Get the current user's target income.
     */
    public function getTargetIncome()
    {
        $user = Auth::user();
        return response()->json([
            'target_income' => $user->daily_target_income,
            'formatted_target_income' => $user->formatted_target_income
        ]);
    }

    /**
     * Update the user's daily target income.
     */
    public function updateTargetIncome(Request $request)
    {
        try {
            $request->validate([
                'daily_target_income' => 'nullable|numeric|min:0|max:999999.99'
            ], [
                'daily_target_income.numeric' => 'Target income must be a valid number.',
                'daily_target_income.min' => 'Target income cannot be negative.',
                'daily_target_income.max' => 'Target income cannot exceed 999,999.99.'
            ]);

            $user = Auth::user();
            $user->daily_target_income = $request->daily_target_income;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Target income updated successfully.',
                'target_income' => $user->daily_target_income,
                'formatted_target_income' => $user->formatted_target_income
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating target income.'
            ], 500);
        }
    }

    /**
     * Clear the user's daily target income.
     */
    public function clearTargetIncome()
    {
        try {
            $user = Auth::user();
            $user->daily_target_income = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Target income cleared successfully.',
                'target_income' => null,
                'formatted_target_income' => '0.00'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while clearing target income.'
            ], 500);
        }
    }
}
