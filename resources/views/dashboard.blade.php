@extends('layouts.main')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- Info boxes -->
<div class="row">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Clients</span>
                <span class="info-box-number">{{ $stats['total_clients'] }}</span>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-rupee-sign"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Income</span>
                <span class="info-box-number">₹{{ number_format($stats['total_income'], 2) }}</span>
            </div>
        </div>
    </div>
    
    <div class="clearfix hidden-md-up"></div>
    
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-receipt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Expenses</span>
                <span class="info-box-number">₹{{ number_format($stats['total_expenses'], 2) }}</span>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-chart-line"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Net Profit</span>
                <span class="info-box-number">₹{{ number_format($stats['net_profit'], 2) }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Current Month Summary -->
<div class="row mt-4">
    <div class="col-12 col-sm-6 col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-wallet"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Current Month Income</span>
                <span class="info-box-number">₹{{ number_format($stats['current_month_income'], 2) }}</span>
                <div class="progress mt-1">
                    <div class="progress-bar bg-success" style="width: 100%"></div>
                </div>
                <small class="text-muted">As of {{ now()->format('M Y') }}</small>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-receipt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Current Month Expenses</span>
                <span class="info-box-number">₹{{ number_format($stats['current_month_expenses'], 2) }}</span>
                <div class="progress mt-1">
                    <div class="progress-bar bg-danger" style="width: 100%"></div>
                </div>
                <small class="text-muted">As of {{ now()->format('M Y') }}</small>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-calculator"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Current Month Net</span>
                @php
                    $netClass = $stats['current_month_net'] >= 0 ? 'text-success' : 'text-danger';
                @endphp
                <span class="info-box-number {{ $netClass }}">₹{{ number_format($stats['current_month_net'], 2) }}</span>
                <div class="progress mt-1">
                    @php
                        $progressWidth = $stats['current_month_income'] > 0 
                            ? abs(($stats['current_month_net'] / $stats['current_month_income']) * 100) 
                            : 0;
                        $progressClass = $stats['current_month_net'] >= 0 ? 'bg-info' : 'bg-danger';
                    @endphp
                    <div class="progress-bar {{ $progressClass }}" style="width: {{ min($progressWidth, 100) }}%"></div>
                </div>
                <small class="text-muted">Net for {{ now()->format('M Y') }}</small>
            </div>
        </div>
    </div>
</div>

<!-- Income vs Target Comparison Widgets -->
<div class="row mt-4">
    <div class="col-12 col-lg-6 mb-3">
        @include('dashboard.partials.monthly-comparison', ['data' => $comparisonData['monthly']])
    </div>
    <div class="col-12 col-lg-6 mb-3">
        @include('dashboard.partials.yearly-comparison', ['data' => $comparisonData['yearly']])
    </div>
</div>

<!-- Second row of info boxes -->
<div class="row">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-envelope"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Emails</span>
                <span class="info-box-number">{{ $stats['total_emails'] }}</span>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-globe"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Websites</span>
                <span class="info-box-number">{{ $stats['total_websites'] }}</span>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-dark elevation-1"><i class="fas fa-tasks"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Pending Tasks</span>
                <span class="info-box-number">{{ $stats['total_tasks'] }}</span>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-teal elevation-1"><i class="fas fa-percentage"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Profit Margin</span>
                <span class="info-box-number">{{ $stats['total_income'] > 0 ? number_format(($stats['net_profit'] / $stats['total_income']) * 100, 1) : 0 }}%</span>
            </div>
        </div>
    </div>
</div>

<!-- Main row -->
<div class="row">
    <!-- Left col -->
    <section class="col-lg-7 connectedSortable">
        <!-- Custom tabs (Charts with tabs)-->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-1"></i>
                    Monthly Overview
                </h3>
                <div class="card-tools">
                    <ul class="nav nav-pills ml-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="#revenue-chart" data-toggle="tab">Income</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#expense-chart" data-toggle="tab">Expenses</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div class="tab-content p-0">
                    <!-- Income Chart -->
                    <div class="chart tab-pane active" id="revenue-chart" style="position: relative; height: 300px;">
                        <canvas id="incomeChart"></canvas>
                    </div>
                    <!-- Expense Chart -->
                    <div class="chart tab-pane" id="expense-chart" style="position: relative; height: 300px;">
                        <canvas id="expenseChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.card -->
    </section>
    <!-- /.Left col -->
    
    <!-- right col (We are only adding the ID to make the widgets sortable)-->
    <section class="col-lg-5 connectedSortable">
        <!-- Quick Actions Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bolt mr-1"></i>
                    Quick Actions
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <a href="/clients" class="btn btn-app">
                            <i class="fas fa-users"></i> Clients
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="/incomes" class="btn btn-app">
                            <i class="fas fa-coins"></i> Income
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="/expenses" class="btn btn-app">
                            <i class="fas fa-receipt"></i> Expenses
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="/emails" class="btn btn-app">
                            <i class="fas fa-envelope"></i> Emails
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="/websites" class="btn btn-app">
                            <i class="fas fa-globe"></i> Websites
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="/pending-tasks" class="btn btn-app">
                            <i class="fas fa-tasks"></i> Tasks
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.card -->

        <!-- Recent Activity Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clock mr-1"></i>
                    Recent Activity
                </h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="time-label">
                        <span class="bg-red">Today</span>
                    </div>
                    <div>
                        <i class="fas fa-user bg-blue"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> 2 hours ago</span>
                            <h3 class="timeline-header"><a href="#">New client</a> added</h3>
                            <div class="timeline-body">
                                A new client has been registered in the system.
                            </div>
                        </div>
                    </div>
                    <div>
                        <i class="fas fa-coins bg-green"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> 5 hours ago</span>
                            <h3 class="timeline-header"><a href="#">Income</a> recorded</h3>
                            <div class="timeline-body">
                                New income entry of $500 has been added.
                            </div>
                        </div>
                    </div>
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.card -->
    </section>
    <!-- right col -->
</div>
<!-- /.row -->
@endsection

@section('styles')
<style>
/* Income Comparison Widgets Enhanced Styling */
.income-comparison-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 15px;
    overflow: hidden;
}

.income-comparison-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.monthly-card .card-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
}

.yearly-card .card-header {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
}

.income-comparison-card .card-header {
    border: none;
    padding: 1rem 1.25rem;
}

.income-comparison-card .card-body {
    padding: 1.5rem 1.25rem;
}

.income-comparison-card .description-block {
    padding: 0.75rem;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.income-comparison-card .description-block:hover {
    background-color: rgba(0,0,0,0.02);
}

.income-comparison-card .description-header {
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.income-comparison-card .description-text {
    font-size: 0.85rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Progress Bar Enhancements */
.income-comparison-card .progress {
    border-radius: 10px;
    background-color: rgba(0,0,0,0.05);
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
}

.income-comparison-card .progress-bar {
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.85rem;
    line-height: 20px;
    transition: all 0.6s ease;
    position: relative;
    overflow: hidden;
}

.income-comparison-card .progress-bar::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { left: -100%; }
    100% { left: 100%; }
}

/* Badge Enhancements */
.income-comparison-card .badge {
    font-size: 0.8rem;
    padding: 0.5rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.income-comparison-card .badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* Performance Summary Styling */
.income-comparison-card .performance-summary {
    padding: 1rem;
    border-radius: 10px;
    background: linear-gradient(135deg, rgba(0,0,0,0.02) 0%, rgba(0,0,0,0.01) 100%);
    margin: 1rem 0;
}

.income-comparison-card .performance-amount {
    font-size: 1.1rem;
    font-weight: 700;
}

.income-comparison-card .performance-label {
    font-size: 0.8rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Responsive Enhancements */
@media (max-width: 576px) {
    .income-comparison-card .card-header {
        padding: 0.75rem 1rem;
    }
    
    .income-comparison-card .card-body {
        padding: 1rem;
    }
    
    .income-comparison-card .description-header {
        font-size: 1.2rem;
    }
    
    .income-comparison-card .description-text {
        font-size: 0.75rem;
    }
    
    .income-comparison-card .progress {
        height: 16px !important;
    }
    
    .income-comparison-card .progress-bar {
        font-size: 0.75rem;
        line-height: 16px;
    }
    
    .income-comparison-card .badge {
        font-size: 0.7rem;
        padding: 0.4rem 0.6rem;
    }
}

@media (max-width: 768px) {
    .income-comparison-card .border-right {
        border-right: none !important;
    }
    
    .income-comparison-card .border-bottom {
        border-bottom: 1px solid #dee2e6 !important;
    }
}

@media (min-width: 769px) {
    .income-comparison-card .border-sm-right {
        border-right: 1px solid #dee2e6 !important;
    }
    
    .income-comparison-card .border-sm-bottom-0 {
        border-bottom: none !important;
    }
}

/* Icon Animations */
.income-comparison-card i {
    transition: all 0.3s ease;
}

.income-comparison-card:hover i {
    transform: scale(1.1);
}

/* No Target Message Styling */
.income-comparison-card .no-target-message {
    padding: 2rem 1rem;
}

.income-comparison-card .no-target-message i {
    opacity: 0.6;
    transition: all 0.3s ease;
}

.income-comparison-card:hover .no-target-message i {
    opacity: 0.8;
    transform: scale(1.05);
}

/* Card Height Consistency */
.h-100 {
    height: 100% !important;
}

/* Enhanced Button Styling for Target Setting */
.income-comparison-card .btn-primary {
    border-radius: 20px;
    padding: 0.5rem 1.5rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.income-comparison-card .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
}

/* Compact Mode for Small Screens */
.income-comparison-card.compact-mode .description-header {
    font-size: 1rem !important;
}

.income-comparison-card.compact-mode .description-text {
    font-size: 0.7rem !important;
}

/* Enhanced Visual Effects */
.income-comparison-card .progress-bar {
    position: relative;
    overflow: hidden;
}

.income-comparison-card .progress-bar::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { left: -100%; }
    100% { left: 100%; }
}

/* Improved Accessibility */
.income-comparison-card:focus-within {
    outline: 2px solid #007bff;
    outline-offset: 2px;
}

.income-comparison-card .btn:focus {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    .income-comparison-card {
        background-color: #2d3748;
        color: #e2e8f0;
    }
    
    .income-comparison-card .text-muted {
        color: #a0aec0 !important;
    }
}

/* Touch Device Enhancements */
.income-comparison-card.touch-device {
    cursor: pointer;
}

.income-comparison-card.touch-active {
    transform: scale(0.98);
    transition: transform 0.1s ease;
}

/* High DPI Display Support */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    .income-comparison-card {
        border-width: 0.5px;
    }
    
    .income-comparison-card .progress {
        border-width: 0.5px;
    }
}

/* Print Styles */
@media print {
    .income-comparison-card {
        break-inside: avoid;
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
    
    .income-comparison-card .card-header {
        background: #f8f9fa !important;
        color: #333 !important;
    }
    
    .income-comparison-card .btn {
        display: none !important;
    }
}

.income-comparison-card.compact-mode .badge {
    font-size: 0.65rem !important;
    padding: 0.3rem 0.5rem !important;
}

/* Loading Animation */
.income-comparison-card .btn .fa-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Enhanced Focus States for Accessibility */
.income-comparison-card:focus-within {
    outline: 2px solid #007bff;
    outline-offset: 2px;
}

.income-comparison-card .btn:focus {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Print Styles */
@media print {
    .income-comparison-card {
        break-inside: avoid;
        box-shadow: none !important;
        border: 1px solid #dee2e6 !important;
    }
    
    .income-comparison-card .card-header {
        background: #f8f9fa !important;
        color: #212529 !important;
    }
    
    .income-comparison-card .progress-bar::before {
        display: none;
    }
}

/* Dark Mode Support (if implemented) */
@media (prefers-color-scheme: dark) {
    .income-comparison-card {
        background-color: #2d3748;
        color: #e2e8f0;
    }
    
    .income-comparison-card .card-header {
        background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%) !important;
    }
}
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Income Chart Data
    const incomeData = {
        labels: [
            @foreach($monthlyIncomes as $income)
                '{{ $income["month"] }}',
            @endforeach
        ],
        datasets: [{
            label: 'Monthly Income',
            data: [
                @foreach($monthlyIncomes as $income)
                    {{ $income["total"] }},
                @endforeach
            ],
            borderColor: 'rgb(40, 167, 69)',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    };

    // Expense Chart Data
    const expenseData = {
        labels: [
            @foreach($monthlyExpenses as $expense)
                '{{ $expense["month"] }}',
            @endforeach
        ],
        datasets: [{
            label: 'Monthly Expenses',
            data: [
                @foreach($monthlyExpenses as $expense)
                    {{ $expense["total"] }},
                @endforeach
            ],
            borderColor: 'rgb(220, 53, 69)',
            backgroundColor: 'rgba(220, 53, 69, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    };

    // Chart Configuration
    const chartConfig = {
        type: 'line',
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + value.toLocaleString();
                        }
                    }
                }
            },
            elements: {
                point: {
                    radius: 4,
                    hoverRadius: 6
                }
            }
        }
    };

    // Initialize Income Chart
    const incomeCtx = document.getElementById('incomeChart').getContext('2d');
    new Chart(incomeCtx, {
        ...chartConfig,
        data: incomeData
    });

    // Initialize Expense Chart
    const expenseCtx = document.getElementById('expenseChart').getContext('2d');
    new Chart(expenseCtx, {
        ...chartConfig,
        data: expenseData
    });
    
    // Enhanced Income Comparison Widgets Interactions
    initializeComparisonWidgets();
});

function initializeComparisonWidgets() {
    // Add hover effects to comparison cards
    $('.income-comparison-card').hover(
        function() {
            $(this).find('.progress-bar').addClass('progress-bar-animated');
        },
        function() {
            // Keep animation for visual appeal
        }
    );
    
    // Add click-to-expand functionality for mobile
    $('.income-comparison-card .description-block').on('click', function() {
        if ($(window).width() <= 576) {
            $(this).toggleClass('expanded');
        }
    });
    
    // Animate progress bars on scroll
    if (typeof IntersectionObserver !== 'undefined') {
        const progressBars = document.querySelectorAll('.income-comparison-card .progress-bar');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const progressBar = entry.target;
                    const width = progressBar.style.width;
                    progressBar.style.width = '0%';
                    setTimeout(() => {
                        progressBar.style.width = width;
                    }, 100);
                }
            });
        }, { threshold: 0.5 });
        
        progressBars.forEach(bar => observer.observe(bar));
    }
    
    // Add tooltip functionality for better UX
    $('[data-toggle="tooltip"]').tooltip();
    
    // Responsive text sizing
    function adjustTextSizes() {
        const cards = $('.income-comparison-card');
        cards.each(function() {
            const card = $(this);
            const cardWidth = card.width();
            
            if (cardWidth < 300) {
                card.addClass('compact-mode');
            } else {
                card.removeClass('compact-mode');
            }
        });
    }
    
    // Initial adjustment and on resize
    adjustTextSizes();
    $(window).on('resize', debounce(adjustTextSizes, 250));
    
    // Add smooth scrolling to settings link
    $('.income-comparison-card a[href*="settings"]').on('click', function(e) {
        // Add a subtle loading animation
        const btn = $(this);
        const originalText = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Loading...');
        
        setTimeout(() => {
            btn.html(originalText);
        }, 1000);
    });
}

// Utility function for debouncing
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Additional responsive enhancements
function enhanceResponsiveFeatures() {
    // Add touch-friendly interactions for mobile
    if ('ontouchstart' in window) {
        $('.income-comparison-card').addClass('touch-device');
        
        // Add tap feedback
        $('.income-comparison-card').on('touchstart', function() {
            $(this).addClass('touch-active');
        }).on('touchend', function() {
            setTimeout(() => {
                $(this).removeClass('touch-active');
            }, 150);
        });
    }
    
    // Improve accessibility with keyboard navigation
    $('.income-comparison-card .btn').on('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this)[0].click();
        }
    });
    
    // Add smooth transitions for progress bars
    $('.progress-bar').each(function() {
        const $bar = $(this);
        const width = $bar.css('width');
        $bar.css('width', '0%');
        
        setTimeout(() => {
            $bar.css('width', width);
        }, 500);
    });
    
    // Enhanced tooltip functionality
    $('[data-toggle="tooltip"]').tooltip({
        trigger: 'hover focus',
        placement: 'auto'
    });
}

// Initialize enhancements when DOM is ready
$(document).ready(function() {
    enhanceResponsiveFeatures();
    
    // Re-initialize on window resize
    $(window).on('resize', debounce(function() {
        enhanceResponsiveFeatures();
    }, 250));
});
</script>
@endsection
