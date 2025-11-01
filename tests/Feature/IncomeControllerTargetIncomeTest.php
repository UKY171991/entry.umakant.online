<?php

use App\Models\User;
use App\Models\Client;

test('passes user target income to view when set', function () {
    $user = User::factory()->create(['daily_target_income' => 2500.75]);
    Client::create(['name' => 'Test Client', 'email' => 'test@example.com']);
    
    $response = $this
        ->actingAs($user)
        ->get(route('incomes.index'));
    
    $response->assertStatus(200);
    $response->assertViewIs('incomes.index');
    $response->assertViewHas('userTargetIncome', '2500.75');
});

test('passes null target income to view when not set', function () {
    $user = User::factory()->create();
    Client::create(['name' => 'Test Client', 'email' => 'test@example.com']);
    
    $response = $this
        ->actingAs($user)
        ->get(route('incomes.index'));
    
    $response->assertStatus(200);
    $response->assertViewIs('incomes.index');
    $response->assertViewHas('userTargetIncome', null);
});

test('passes zero target income to view when set to zero', function () {
    $user = User::factory()->create(['daily_target_income' => 0.00]);
    Client::create(['name' => 'Test Client', 'email' => 'test@example.com']);
    
    $response = $this
        ->actingAs($user)
        ->get(route('incomes.index'));
    
    $response->assertStatus(200);
    $response->assertViewHas('userTargetIncome', '0.00');
});

test('passes updated target income after user changes it', function () {
    $user = User::factory()->create(['daily_target_income' => 1000.00]);
    Client::create(['name' => 'Test Client', 'email' => 'test@example.com']);
    
    $response = $this
        ->actingAs($user)
        ->get(route('incomes.index'));
    $response->assertViewHas('userTargetIncome', '1000.00');
    
    // Update target income
    $user->update(['daily_target_income' => 2000.00]);
    
    $response = $this
        ->actingAs($user)
        ->get(route('incomes.index'));
    $response->assertViewHas('userTargetIncome', '2000.00');
});

test('passes clients data along with target income', function () {
    $user = User::factory()->create(['daily_target_income' => 1500.00]);
    Client::create(['name' => 'Test Client 1', 'email' => 'test1@example.com']);
    Client::create(['name' => 'Test Client 2', 'email' => 'test2@example.com']);
    Client::create(['name' => 'Test Client 3', 'email' => 'test3@example.com']);
    
    $response = $this
        ->actingAs($user)
        ->get(route('incomes.index'));
    
    $response->assertStatus(200);
    $response->assertViewHas('userTargetIncome', '1500.00');
    $response->assertViewHas('clients');
});
test('ajax requests return json data for income listing', function () {
    $user = User::factory()->create(['daily_target_income' => 3000.00]);
    Client::create(['name' => 'Test Client', 'email' => 'test@example.com']);
    
    $response = $this
        ->actingAs($user)
        ->get(route('incomes.index'), [
            'X-Requested-With' => 'XMLHttpRequest'
        ]);
    
    // AJAX requests should return JSON data, not view
    $response->assertStatus(200);
    $response->assertJson([]);
});

test('requires authentication to access income index', function () {
    $response = $this->get(route('incomes.index'));
    
    $response->assertRedirect(route('login'));
});

test('different users get their own target income', function () {
    $firstUser = User::factory()->create(['daily_target_income' => 1000.00]);
    $secondUser = User::factory()->create(['daily_target_income' => 2000.00]);
    Client::create(['name' => 'Test Client', 'email' => 'test@example.com']);
    
    $response = $this
        ->actingAs($secondUser)
        ->get(route('incomes.index'));
    
    $response->assertViewHas('userTargetIncome', '2000.00');
});

test('target income data is available for javascript auto-fill', function () {
    $user = User::factory()->create(['daily_target_income' => 4500.25]);
    Client::create(['name' => 'Test Client', 'email' => 'test@example.com']);
    
    $response = $this
        ->actingAs($user)
        ->get(route('incomes.index'));
    
    $response->assertStatus(200);
    $response->assertSee('window.userTargetIncome = 4500.25', false);
});

test('null target income is handled in javascript', function () {
    $user = User::factory()->create();
    Client::create(['name' => 'Test Client', 'email' => 'test@example.com']);
    
    $response = $this
        ->actingAs($user)
        ->get(route('incomes.index'));
    
    $response->assertStatus(200);
    $response->assertSee('window.userTargetIncome = null', false);
});

test('zero target income is handled in javascript', function () {
    $user = User::factory()->create(['daily_target_income' => 0.00]);
    Client::create(['name' => 'Test Client', 'email' => 'test@example.com']);
    
    $response = $this
        ->actingAs($user)
        ->get(route('incomes.index'));
    
    $response->assertStatus(200);
    $response->assertSee('window.userTargetIncome = 0', false);
});