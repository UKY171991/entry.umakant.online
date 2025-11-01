<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('User Model Target Income', function () {
    
    test('user can have daily target income set', function () {
        $user = User::factory()->create([
            'daily_target_income' => 5000.50
        ]);
        
        expect($user->daily_target_income)->toBe('5000.50');
    });
    
    test('user daily target income can be null', function () {
        $user = User::factory()->create([
            'daily_target_income' => null
        ]);
        
        expect($user->daily_target_income)->toBeNull();
    });
    
    test('daily target income is cast to decimal', function () {
        $user = User::factory()->create([
            'daily_target_income' => 1000
        ]);
        
        expect($user->daily_target_income)->toBe('1000.00');
    });
    
    test('formatted target income accessor returns formatted value', function () {
        $user = User::factory()->create([
            'daily_target_income' => 5000.50
        ]);
        
        expect($user->formatted_target_income)->toBe('5,000.50');
    });
    
    test('formatted target income accessor returns 0.00 when null', function () {
        $user = User::factory()->create([
            'daily_target_income' => null
        ]);
        
        expect($user->formatted_target_income)->toBe('0.00');
    });
    
    test('daily target income is fillable', function () {
        $user = new User();
        
        expect($user->getFillable())->toContain('daily_target_income');
    });
    
    test('daily target income accepts decimal values', function () {
        $user = User::factory()->create();
        $user->update(['daily_target_income' => 2500.75]);
        
        expect($user->fresh()->daily_target_income)->toBe('2500.75');
    });
    
    test('daily target income can be updated', function () {
        $user = User::factory()->create([
            'daily_target_income' => 1000.00
        ]);
        
        $user->update(['daily_target_income' => 2000.00]);
        
        expect($user->fresh()->daily_target_income)->toBe('2000.00');
    });
    
    test('daily target income can be cleared', function () {
        $user = User::factory()->create([
            'daily_target_income' => 1000.00
        ]);
        
        $user->update(['daily_target_income' => null]);
        
        expect($user->fresh()->daily_target_income)->toBeNull();
    });
});