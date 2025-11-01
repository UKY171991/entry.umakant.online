<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Income;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class DashboardWidgetIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user with daily target income
        $this->user = User::factory()->create([
            'daily_target_income' => 1000.00
        ]);
        
        // Create a test client for income entries
        $this->client = Client::factory()->create();
    }

    /** @test */
    public function it_loads_dashboard_with_comparison_widgets_successfully()
    {
        // Arrange
        $this->actingAs($this->user);
        
        // Act
        $response = $this->get('/dashboard');
        
        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertViewHas('comparisonData');
        
        // Verify comparison data structure
        $comparisonData = $response->viewData('comparisonData');
        $this->assertArrayHasKey('monthly', $comparisonData);
        $this->assertArrayHasKey('yearly', $comparisonData);
        
        // Verify monthly data structure
        $this->assertArrayHasKey('actual', $comparisonData['monthly']);
        $this->assertArrayHasKey('target', $comparisonData['monthly']);
        $this->assertArrayHasKey('percentage', $comparisonData['monthly']);
        $this->assertArrayHasKey('difference', $comparisonData['monthly']);
        $this->assertArrayHasKey('has_target', $comparisonData['monthly']);
        
        // Verify yearly data structure
        $this->assertArrayHasKey('actual', $comparisonData['yearly']);
        $this->assertArrayHasKey('target', $comparisonData['yearly']);
        $this->assertArrayHasKey('percentage', $comparisonData['yearly']);
        $this->assertArrayHasKey('difference', $comparisonData['yearly']);
        $this->assertArrayHasKey('has_target', $comparisonData['yearly']);
    }

    /** @test */
    public function it_renders_monthly_comparison_widget_with_target_set()
    {
        // Arrange
        $this->actingAs($this->user);
        
        // Create income entries for current month
        Income::factory()->create([
            'client_id' => $this->client->id,
            'total_amount' => 15000.00,
            'date' => now()->format('Y-m-15')
        ]);
        
        // Act
        $response = $this->get('/dashboard');
        
        // Assert
        $response->assertStatus(200);
        
        // Check for monthly widget elements
        $response->assertSee('Monthly Income vs Target');
        $response->assertSee('₹15,000.00'); // Actual income
        $response->assertSee('Actual Income');
        $response->assertSee('Target Income');
        $response->assertSee('progress-bar');
        
        // Check for current month period
        $response->assertSee(now()->format('M Y'));
    }

    /** @test */
    public function it_renders_yearly_comparison_widget_with_target_set()
    {
        // Arrange
        $this->actingAs($this->user);
        
        // Create income entries for current year
        Income::factory()->create([
            'client_id' => $this->client->id,
            'total_amount' => 180000.00,
            'date' => now()->format('Y-06-15')
        ]);
        
        // Act
        $response = $this->get('/dashboard');
        
        // Assert
        $response->assertStatus(200);
        
        // Check for yearly widget elements
        $response->assertSee('Yearly Income vs Target');
        $response->assertSee('₹180,000.00'); // Actual income
        $response->assertSee('Actual Income');
        $response->assertSee('Target Income');
        $response->assertSee('progress-bar');
        
        // Check for current year period
        $response->assertSee(now()->format('Y'));
    }

    /** @test */
    public function it_displays_correct_color_coding_for_performance_levels()
    {
        // Test scenario 1: Meeting target (100% - should be green/success)
        $this->actingAs($this->user);
        
        $currentMonth = now();
        $targetAmount = 1000.00 * $currentMonth->daysInMonth;
        
        Income::factory()->create([
            'client_id' => $this->client->id,
            'total_amount' => $targetAmount,
            'date' => $currentMonth->format('Y-m-15')
        ]);
        
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('bg-gradient-success');
        $response->assertSee('Target Achieved!');
        
        // Clean up for next test
        Income::truncate();
        
        // Test scenario 2: Close to target (80-99% - should be yellow/warning)
        Income::factory()->create([
            'client_id' => $this->client->id,
            'total_amount' => $targetAmount * 0.85, // 85% of target
            'date' => $currentMonth->format('Y-m-15')
        ]);
        
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('bg-gradient-warning');
        $response->assertSee('Close to Target');
        
        // Clean up for next test
        Income::truncate();
        
        // Test scenario 3: Below target (<80% - should be red/danger)
        Income::factory()->create([
            'client_id' => $this->client->id,
            'total_amount' => $targetAmount * 0.5, // 50% of target
            'date' => $currentMonth->format('Y-m-15')
        ]);
        
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('bg-gradient-info'); // 50% falls into info category
        $response->assertSee('Making Progress');
    }

    /** @test */
    public function it_displays_no_target_message_when_target_not_set()
    {
        // Arrange
        $userWithoutTarget = User::factory()->create([
            'daily_target_income' => null
        ]);
        $this->actingAs($userWithoutTarget);
        
        // Act
        $response = $this->get('/dashboard');
        
        // Assert
        $response->assertStatus(200);
        $response->assertSee('No Target Set');
        $response->assertSee('Set your daily target income');
        $response->assertSee('Set Target Income');
    }

    /** @test */
    public function it_displays_no_income_message_when_no_data_exists()
    {
        // Arrange
        $this->actingAs($this->user);
        // No income entries created
        
        // Act
        $response = $this->get('/dashboard');
        
        // Assert
        $response->assertStatus(200);
        $response->assertSee('No Income Yet');
        $response->assertSee('₹0.00'); // Should show zero actual income
    }

    /** @test */
    public function it_displays_correct_difference_indicators()
    {
        // Test positive difference (above target)
        $this->actingAs($this->user);
        
        $currentMonth = now();
        $targetAmount = 1000.00 * $currentMonth->daysInMonth;
        
        Income::factory()->create([
            'client_id' => $this->client->id,
            'total_amount' => $targetAmount * 1.2, // 20% above target
            'date' => $currentMonth->format('Y-m-15')
        ]);
        
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('fas fa-arrow-up');
        $response->assertSee('Above target');
        
        // Clean up for next test
        Income::truncate();
        
        // Test negative difference (below target)
        Income::factory()->create([
            'client_id' => $this->client->id,
            'total_amount' => $targetAmount * 0.8, // 20% below target
            'date' => $currentMonth->format('Y-m-15')
        ]);
        
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('fas fa-arrow-down');
        $response->assertSee('Below target');
    }

    /** @test */
    public function it_handles_different_data_scenarios_correctly()
    {
        // Scenario 1: Multiple income entries in same month
        $this->actingAs($this->user);
        
        Income::factory()->create([
            'client_id' => $this->client->id,
            'total_amount' => 10000.00,
            'date' => now()->format('Y-m-10')
        ]);
        
        Income::factory()->create([
            'client_id' => $this->client->id,
            'total_amount' => 15000.00,
            'date' => now()->format('Y-m-20')
        ]);
        
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('₹25,000.00'); // Should sum both entries
        
        // Clean up for next scenario
        Income::truncate();
        
        // Scenario 2: Income from different months (should not affect current month)
        Income::factory()->create([
            'client_id' => $this->client->id,
            'total_amount' => 10000.00,
            'date' => now()->subMonth()->format('Y-m-15') // Previous month
        ]);
        
        Income::factory()->create([
            'client_id' => $this->client->id,
            'total_amount' => 5000.00,
            'date' => now()->format('Y-m-15') // Current month
        ]);
        
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('₹5,000.00'); // Should only show current month
    }

    /** @test */
    public function it_displays_responsive_widget_layout()
    {
        // Arrange
        $this->actingAs($this->user);
        
        // Act
        $response = $this->get('/dashboard');
        
        // Assert
        $response->assertStatus(200);
        
        // Check for responsive grid classes
        $response->assertSee('col-12 col-lg-6'); // Responsive column classes
        $response->assertSee('income-comparison-card'); // Widget container class
        $response->assertSee('h-100'); // Height consistency class
        
        // Check for mobile-friendly elements
        $response->assertSee('d-none d-sm-inline'); // Hide text on small screens
        $response->assertSee('d-inline d-sm-none'); // Show abbreviated text on small screens
    }

    /** @test */
    public function it_integrates_seamlessly_with_existing_dashboard_components()
    {
        // Arrange
        $this->actingAs($this->user);
        
        // Act
        $response = $this->get('/dashboard');
        
        // Assert
        $response->assertStatus(200);
        
        // Check for existing dashboard elements
        $response->assertSee('Total Clients');
        $response->assertSee('Total Income');
        $response->assertSee('Total Expenses');
        $response->assertSee('Net Profit');
        
        // Check for new comparison widgets alongside existing elements
        $response->assertSee('Monthly Income vs Target');
        $response->assertSee('Yearly Income vs Target');
        
        // Verify proper integration without breaking existing layout
        $response->assertSee('Quick Actions');
        $response->assertSee('Monthly Overview');
    }

    /** @test */
    public function it_loads_dashboard_efficiently_without_performance_issues()
    {
        // Arrange
        $this->actingAs($this->user);
        
        // Create multiple income entries to test performance
        for ($i = 0; $i < 50; $i++) {
            Income::factory()->create([
                'client_id' => $this->client->id,
                'total_amount' => $this->faker->randomFloat(2, 100, 5000),
                'date' => $this->faker->dateTimeBetween('-1 year', 'now')
            ]);
        }
        
        // Act & Assert
        $startTime = microtime(true);
        $response = $this->get('/dashboard');
        $endTime = microtime(true);
        
        $response->assertStatus(200);
        
        // Verify response time is reasonable (less than 2 seconds)
        $responseTime = $endTime - $startTime;
        $this->assertLessThan(2.0, $responseTime, 'Dashboard should load within 2 seconds');
        
        // Verify data is still accurate with multiple entries
        $response->assertViewHas('comparisonData');
        $comparisonData = $response->viewData('comparisonData');
        $this->assertIsArray($comparisonData);
        $this->assertArrayHasKey('monthly', $comparisonData);
        $this->assertArrayHasKey('yearly', $comparisonData);
    }

    /** @test */
    public function it_validates_visual_indicator_functionality()
    {
        // Arrange
        $this->actingAs($this->user);
        
        $currentMonth = now();
        $targetAmount = 1000.00 * $currentMonth->daysInMonth;
        
        // Create income that exceeds target significantly
        Income::factory()->create([
            'client_id' => $this->client->id,
            'total_amount' => $targetAmount * 2.5, // 250% of target
            'date' => $currentMonth->format('Y-m-15')
        ]);
        
        // Act
        $response = $this->get('/dashboard');
        
        // Assert
        $response->assertStatus(200);
        
        // Check for visual indicators
        $response->assertSee('progress-bar'); // Progress bar element
        $response->assertSee('bg-gradient-success'); // Success color for exceeding target
        $response->assertSee('fas fa-trophy'); // Trophy icon for achievement
        $response->assertSee('Target Achieved!'); // Achievement badge
        
        // Check percentage display (should be capped at reasonable value)
        $response->assertSee('%'); // Percentage symbol should be present
        
        // Check difference indicator
        $response->assertSee('fas fa-arrow-up'); // Up arrow for positive difference
        $response->assertSee('Above target'); // Positive difference text
    }

    /** @test */
    public function it_handles_leap_year_calculations_in_widget_display()
    {
        // Arrange
        $this->actingAs($this->user);
        
        // Mock Carbon to simulate leap year
        Carbon::setTestNow(Carbon::create(2024, 2, 15)); // 2024 is a leap year
        
        // Act
        $response = $this->get('/dashboard');
        
        // Assert
        $response->assertStatus(200);
        
        // Verify leap year calculation is reflected in target
        $comparisonData = $response->viewData('comparisonData');
        $expectedYearlyTarget = 1000.00 * 366; // 366 days in leap year
        $this->assertEquals($expectedYearlyTarget, $comparisonData['yearly']['target']);
        
        // Reset Carbon
        Carbon::setTestNow();
    }

    /** @test */
    public function it_displays_correct_period_formatting_in_widgets()
    {
        // Arrange
        $this->actingAs($this->user);
        
        // Mock specific date for consistent testing
        Carbon::setTestNow(Carbon::create(2023, 6, 15));
        
        // Act
        $response = $this->get('/dashboard');
        
        // Assert
        $response->assertStatus(200);
        
        // Check monthly period formatting
        $response->assertSee('Jun 2023'); // Monthly period badge
        
        // Check yearly period formatting
        $response->assertSee('2023'); // Yearly period badge
        
        // Reset Carbon
        Carbon::setTestNow();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // Reset Carbon after each test
        parent::tearDown();
    }
}