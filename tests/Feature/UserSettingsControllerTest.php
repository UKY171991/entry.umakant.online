<?php

use App\Models\User;

test('displays settings page for authenticated user', function () {
    $user = User::factory()->create();
    
    $response = $this
        ->actingAs($user)
        ->get(route('settings.index'));
    
    $response->assertStatus(200);
    $response->assertViewIs('settings.index');
    $response->assertViewHas('user', $user);
});

test('redirects unauthenticated users to login', function () {
    $response = $this->get(route('settings.index'));
    
    $response->assertRedirect(route('login'));
});
test('returns current target income', function () {
    $user = User::factory()->create(['daily_target_income' => 5000.00]);
    
    $response = $this
        ->actingAs($user)
        ->get(route('settings.target-income.get'));
    
    $response->assertStatus(200);
    $response->assertJson([
        'target_income' => '5000.00',
        'formatted_target_income' => '5,000.00'
    ]);
});

test('returns null when no target income set', function () {
    $user = User::factory()->create();
    
    $response = $this
        ->actingAs($user)
        ->get(route('settings.target-income.get'));
    
    $response->assertStatus(200);
    $response->assertJson([
        'target_income' => null,
        'formatted_target_income' => '0.00'
    ]);
});
test('updates target income with valid data', function () {
    $user = User::factory()->create();
    
    $response = $this
        ->actingAs($user)
        ->post(route('settings.target-income.update'), [
            'daily_target_income' => 3000.50
        ]);
    
    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => 'Target income updated successfully.',
        'target_income' => '3000.50',
        'formatted_target_income' => '3,000.50'
    ]);
    
    expect($user->fresh()->daily_target_income)->toBe('3000.50');
});

test('updates target income to null when empty', function () {
    $user = User::factory()->create(['daily_target_income' => 1000.00]);
    
    $response = $this
        ->actingAs($user)
        ->post(route('settings.target-income.update'), [
            'daily_target_income' => ''
        ]);
    
    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'target_income' => null
    ]);
    
    expect($user->fresh()->daily_target_income)->toBeNull();
});

test('validates negative values', function () {
    $user = User::factory()->create();
    
    $response = $this
        ->actingAs($user)
        ->post(route('settings.target-income.update'), [
            'daily_target_income' => -100
        ]);
    
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['daily_target_income']);
});

test('validates maximum value', function () {
    $user = User::factory()->create();
    
    $response = $this
        ->actingAs($user)
        ->post(route('settings.target-income.update'), [
            'daily_target_income' => 1000000
        ]);
    
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['daily_target_income']);
});

test('validates non-numeric values', function () {
    $user = User::factory()->create();
    
    $response = $this
        ->actingAs($user)
        ->post(route('settings.target-income.update'), [
            'daily_target_income' => 'invalid'
        ]);
    
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['daily_target_income']);
});

test('accepts zero as valid value', function () {
    $user = User::factory()->create();
    
    $response = $this
        ->actingAs($user)
        ->post(route('settings.target-income.update'), [
            'daily_target_income' => 0
        ]);
    
    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'target_income' => '0.00'
    ]);
});

test('accepts maximum allowed value', function () {
    $user = User::factory()->create();
    
    $response = $this
        ->actingAs($user)
        ->post(route('settings.target-income.update'), [
            'daily_target_income' => 999999.99
        ]);
    
    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'target_income' => '999999.99'
    ]);
});
test('clears target income successfully', function () {
    $user = User::factory()->create(['daily_target_income' => 5000.00]);
    
    $response = $this
        ->actingAs($user)
        ->delete(route('settings.target-income.clear'));
    
    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => 'Target income cleared successfully.',
        'target_income' => null,
        'formatted_target_income' => '0.00'
    ]);
    
    expect($user->fresh()->daily_target_income)->toBeNull();
});

test('clears target income when already null', function () {
    $user = User::factory()->create();
    
    $response = $this
        ->actingAs($user)
        ->delete(route('settings.target-income.clear'));
    
    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'target_income' => null
    ]);
});

test('requires authentication for get target income', function () {
    $response = $this->get(route('settings.target-income.get'));
    
    $response->assertRedirect(route('login'));
});

test('requires authentication for update target income', function () {
    $response = $this->post(route('settings.target-income.update'), [
        'daily_target_income' => 1000
    ]);
    
    $response->assertRedirect(route('login'));
});

test('requires authentication for clear target income', function () {
    $response = $this->delete(route('settings.target-income.clear'));
    
    $response->assertRedirect(route('login'));
});

test('different users get their own target income', function () {
    $firstUser = User::factory()->create(['daily_target_income' => 1000.00]);
    $secondUser = User::factory()->create(['daily_target_income' => 2000.00]);
    
    $response = $this
        ->actingAs($secondUser)
        ->get(route('settings.target-income.get'));
    
    $response->assertJson([
        'target_income' => '2000.00'
    ]);
});