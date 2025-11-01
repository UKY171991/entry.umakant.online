<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\DashboardController;
use App\Models\User;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Mockery;

class DashboardComparisonTest extends TestCase
{
    use RefreshDatabase;

    protected $dashboardController;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->dashboardController = new DashboardController();
        
        // Create a test user with daily target income
        $this->user = User::factory()->create([
            'daily_target_income' => 1000.00
        ]);
    }

    /** @test */
    public function it_calculates_monthly_comparison_data_correctly()
    {
        // Arrange
        Auth::login($this->user);
        
        // Create income entries for current month
        $currentMonth = now();
        Income::factory()->create([
            'total_amount' => 15000.00,
            'date' => $currentMonth->format('Y-m-15')
        ]);
        Income::factory()->create([
            'total_amount' => 10000.00,
            'date' => $currentMonth->format('Y-m-20')
        ]);
        
        // Act
        $comparisonData = $this->invokePrivateMethod($this->dashboardController, 'getIncomeComparisonData');
        
        // Assert
        $this->assertTrue($comparisonData['monthly']['has_target']);
        $this->assertEquals(25000.00, $comparisonData['monthly']['actual']);
        $this->assertEquals(1000.00 * $currentMonth->daysInMonth, $comparisonData['monthly']['target']);
        $this->assertEquals(25000.00 - (1000.00 * $currentMonth->daysInMonth), $comparisonData['monthly']['difference']);
        
        $expectedPercentage = (25000.00 / (1000.00 * $currentMonth->daysInMonth)) * 100;
        $this->assertEquals(round($expectedPercentage, 2), $comparisonData['monthly']['percentage']);
    }

    /** @test */
    public function it_calculates_yearly_comparison_data_correctly()
    {
        // Arrange
        Auth::login($this->user);
        
        // Create income entries for current year
        $currentYear = now();
        Income::factory()->create([
            'total_amount' => 180000.00,
            'date' => $currentYear->format('Y-06-15')
        ]);
        Income::factory()->create([
            'total_amount' => 120000.00,
            'date' => $currentYear->format('Y-09-20')
        ]);
        
        // Act
        $comparisonData = $this->invokePrivateMethod($this->dashboardController, 'getIncomeComparisonData');
        
        // Assert
        $this->assertTrue($comparisonData['yearly']['has_target']);
        $this->assertEquals(300000.00, $comparisonData['yearly']['actual']);
        
        $daysInYear = $currentYear->isLeapYear() ? 366 : 365;
        $expectedTarget = 1000.00 * $daysInYear;
        $this->assertEquals($expectedTarget, $comparisonData['yearly']['target']);
        $this->assertEquals(300000.00 - $expectedTarget, $comparisonData['yearly']['difference']);
        
        $expectedPercentage = (300000.00 / $expectedTarget) * 100;
        $this->assertEquals(round($expectedPercentage, 2), $comparisonData['yearly']['percentage']);
    }

    /** @test */
    public function it_handles_leap_year_calculations_correctly()
    {
        // Arrange
        Auth::login($this->user);
        
        // Mock Carbon to return a leap year date
        Carbon::setTestNow(Carbon::create(2024, 2, 15)); // 2024 is a leap year
        
        // Act
        $comparisonData = $this->invokePrivateMethod($this->dashboardController, 'getIncomeComparisonData');
        
        // Assert
        $this->assertEquals(366 * 1000.00, $comparisonData['yearly']['target']);
        
        // Reset Carbon
        Carbon::setTestNow();
    }

    /** @test */
    public function it_handles_non_leap_year_calculations_correctly()
    {
        // Arrange
        Auth::login($this->user);
        
        // Mock Carbon to return a non-leap year date
        Carbon::setTestNow(Carbon::create(2023, 6, 15)); // 2023 is not a leap year
        
        // Act
        $comparisonData = $this->invokePrivateMethod($this->dashboardController, 'getIncomeComparisonData');
        
        // Assert
        $this->assertEquals(365 * 1000.00, $comparisonData['yearly']['target']);
        
        // Reset Carbon
        Carbon::setTestNow();
    }

    /** @test */
    public function it_handles_month_boundary_edge_cases()
    {
        // Arrange
        Auth::login($this->user);
        
        // Test February (shortest month)
        Carbon::setTestNow(Carbon::create(2023, 2, 15));
        
        // Act
        $comparisonData = $this->invokePrivateMethod($this->dashboardController, 'getIncomeComparisonData');
        
        // Assert
        $this->assertEquals(28 * 1000.00, $comparisonData['monthly']['target']); // February 2023 has 28 days
        
        // Test February in leap year
        Carbon::setTestNow(Carbon::create(2024, 2, 15));
        $comparisonData = $this->invokePrivateMethod($this->dashboardController, 'getIncomeComparisonData');
        $this->assertEquals(29 * 1000.00, $comparisonData['monthly']['target']); // February 2024 has 29 days
        
        // Reset Carbon
        Carbon::setTestNow();
    }

    /** @test */
    public function it_handles_division_by_zero_gracefully()
    {
        // Arrange
        $userWithZeroTarget = User::factory()->create([
            'daily_target_income' => 0
        ]);
        Auth::login($userWithZeroTarget);
        
        // Act
        $comparisonData = $this->invokePrivateMethod($this->dashboardController, 'getIncomeComparisonData');
        
        // Assert
        $this->assertFalse($comparisonData['monthly']['has_target']);
        $this->assertFalse($comparisonData['yearly']['has_target']);
        $this->assertEquals(0, $comparisonData['monthly']['percentage']);
        $this->assertEquals(0, $comparisonData['yearly']['percentage']);
    }

    /** @test */
    public function it_handles_null_target_income_gracefully()
    {
        // Arrange
        $userWithNullTarget = User::factory()->create([
            'daily_target_income' => null
        ]);
        Auth::login($userWithNullTarget);
        
        // Act
        $comparisonData = $this->invokePrivateMethod($this->dashboardController, 'getIncomeComparisonData');
        
        // Assert
        $this->assertFalse($comparisonData['monthly']['has_target']);
        $this->assertFalse($comparisonData['yearly']['has_target']);
        $this->assertEquals(0, $comparisonData['monthly']['actual']);
        $this->assertEquals(0, $comparisonData['yearly']['actual']);
    }

    /** @test */
    public function it_handles_no_authenticated_user_gracefully()
    {
        // Arrange - No user authenticated
        Auth::logout();
        
        // Act
        $comparisonData = $this->invokePrivateMethod($this->dashboardController, 'getIncomeComparisonData');
        
        // Assert
        $this->assertFalse($comparisonData['monthly']['has_target']);
        $this->assertFalse($comparisonData['yearly']['has_target']);
        $this->assertEquals(0, $comparisonData['monthly']['actual']);
        $this->assertEquals(0, $comparisonData['yearly']['actual']);
    }

    /** @test */
    public function it_handles_no_income_data_gracefully()
    {
        // Arrange
        Auth::login($this->user);
        // No income entries created
        
        // Act
        $comparisonData = $this->invokePrivateMethod($this->dashboardController, 'getIncomeComparisonData');
        
        // Assert
        $this->assertTrue($comparisonData['monthly']['has_target']);
        $this->assertTrue($comparisonData['yearly']['has_target']);
        $this->assertEquals(0, $comparisonData['monthly']['actual']);
        $this->assertEquals(0, $comparisonData['yearly']['actual']);
        $this->assertEquals(0, $comparisonData['monthly']['percentage']);
        $this->assertEquals(0, $comparisonData['yearly']['percentage']);
    }

    /** @test */
    public function it_calculates_percentage_accurately_for_various_scenarios()
    {
        // Arrange
        Auth::login($this->user);
        
        // Test scenario 1: Exactly meeting target
        $currentMonth = now();
        $targetAmount = 1000.00 * $currentMonth->daysInMonth;
        Income::factory()->create([
            'total_amount' => $targetAmount,
            'date' => $currentMonth->format('Y-m-15')
        ]);
        
        // Act
        $comparisonData = $this->invokePrivateMethod($this->dashboardController, 'getIncomeComparisonData');
        
        // Assert
        $this->assertEquals(100.00, $comparisonData['monthly']['percentage']);
        $this->assertEquals(0, $comparisonData['monthly']['difference']);
        
        // Clean up for next test
        Income::truncate();
        
        // Test scenario 2: Exceeding target by 50%
        Income::factory()->create([
            'total_amount' => $targetAmount * 1.5,
            'date' => $currentMonth->format('Y-m-15')
        ]);
        
        $comparisonData = $this->invokePrivateMethod($this->dashboardController, 'getIncomeComparisonData');
        $this->assertEquals(150.00, $comparisonData['monthly']['percentage']);
        $this->assertEquals($targetAmount * 0.5, $comparisonData['monthly']['difference']);
        
        // Clean up for next test
        Income::truncate();
        
        // Test scenario 3: Below target by 25%
        Income::factory()->create([
            'total_amount' => $targetAmount * 0.75,
            'date' => $currentMonth->format('Y-m-15')
        ]);
        
        $comparisonData = $this->invokePrivateMethod($this->dashboardController, 'getIncomeComparisonData');
        $this->assertEquals(75.00, $comparisonData['monthly']['percentage']);
        $this->assertEquals($targetAmount * -0.25, $comparisonData['monthly']['difference']);
    }

    /** @test */
    public function it_caps_extremely_high_daily_targets()
    {
        // Arrange
        $userWithHighTarget = User::factory()->create([
            'daily_target_income' => 2000000.00 // 2M per day, should be capped at 1M
        ]);
        Auth::login($userWithHighTarget);
        
        // Act
        $comparisonData = $this->invokePrivateMethod($this->dashboardController, 'getIncomeComparisonData');
        
        // Assert
        $currentMonth = now();
        $expectedCappedTarget = 1000000.00 * $currentMonth->daysInMonth; // Capped at 1M per day
        $this->assertEquals($expectedCappedTarget, $comparisonData['monthly']['target']);
    }

    /** @test */
    public function it_caps_percentage_at_maximum_display_value()
    {
        // Arrange
        Auth::login($this->user);
        
        // Create extremely high income compared to target
        $currentMonth = now();
        $targetAmount = 1000.00 * $currentMonth->daysInMonth;
        Income::factory()->create([
            'total_amount' => $targetAmount * 150, // 15000% of target
            'date' => $currentMonth->format('Y-m-15')
        ]);
        
        // Act
        $comparisonData = $this->invokePrivateMethod($this->dashboardController, 'getIncomeComparisonData');
        
        // Assert
        $this->assertEquals(9999, $comparisonData['monthly']['percentage']); // Should be capped at 9999%
    }

    /** @test */
    public function it_handles_negative_income_amounts()
    {
        // Arrange
        Auth::login($this->user);
        
        // Create negative income (refund scenario)
        Income::factory()->create([
            'total_amount' => -5000.00,
            'date' => now()->format('Y-m-15')
        ]);
        
        // Act
        $comparisonData = $this->invokePrivateMethod($this->dashboardController, 'getIncomeComparisonData');
        
        // Assert
        $this->assertEquals(-5000.00, $comparisonData['monthly']['actual']);
        $this->assertEquals(0, $comparisonData['monthly']['percentage']); // Should be 0 due to max(0, percentage)
        $this->assertTrue($comparisonData['monthly']['difference'] < 0); // Should be negative
    }

    /** @test */
    public function it_formats_period_strings_correctly()
    {
        // Arrange
        Auth::login($this->user);
        Carbon::setTestNow(Carbon::create(2023, 6, 15));
        
        // Act
        $comparisonData = $this->invokePrivateMethod($this->dashboardController, 'getIncomeComparisonData');
        
        // Assert
        $this->assertEquals('Jun 2023', $comparisonData['monthly']['period']);
        $this->assertEquals('2023', $comparisonData['yearly']['period']);
        
        // Reset Carbon
        Carbon::setTestNow();
    }

    /**
     * Helper method to invoke private methods for testing
     */
    private function invokePrivateMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        
        return $method->invokeArgs($object, $parameters);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // Reset Carbon after each test
        parent::tearDown();
    }
}