{{-- Monthly Income vs Target Comparison Widget --}}
<div class="card income-comparison-card monthly-card h-100 shadow-sm">
    <div class="card-header">
        <h3 class="card-title text-white">
            <i class="fas fa-calendar-alt mr-2"></i>
            <span class="d-none d-sm-inline">Monthly Income vs Target</span>
            <span class="d-inline d-sm-none">Monthly Target</span>
        </h3>
        <div class="card-tools">
            <span class="badge badge-light">{{ now()->format('M Y') }}</span>
        </div>
    </div>
    <div class="card-body">
        @if($data['has_target'])
            <div class="row">
                <div class="col-12 col-sm-6">
                    <div class="description-block border-right border-sm-right border-bottom border-sm-bottom-0 pb-3 pb-sm-0 mb-3 mb-sm-0">
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="text-center">
                                <i class="fas fa-wallet text-success mb-2 d-block" style="font-size: 1.2rem;"></i>
                                <h5 class="description-header text-success mb-1">₹{{ number_format($data['actual'], 2) }}</h5>
                                <span class="description-text text-muted small">Actual Income</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="description-block">
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="text-center">
                                <i class="fas fa-bullseye text-primary mb-2 d-block" style="font-size: 1.2rem;"></i>
                                <h5 class="description-header text-primary mb-1">₹{{ number_format($data['target'], 2) }}</h5>
                                <span class="description-text text-muted small">Target Income</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="mt-4">
                @php
                    // Handle edge cases for percentage display
                    $percentage = is_numeric($data['percentage']) ? floatval($data['percentage']) : 0;
                    $progressWidth = min(max($percentage, 0), 100); // Ensure between 0-100 for display
                    
                    // Enhanced color coding with gradients
                    if ($percentage >= 100) {
                        $progressClass = 'bg-gradient-success';
                        $progressIcon = 'fas fa-trophy';
                    } elseif ($percentage >= 80) {
                        $progressClass = 'bg-gradient-warning';
                        $progressIcon = 'fas fa-clock';
                    } elseif ($percentage >= 50) {
                        $progressClass = 'bg-gradient-info';
                        $progressIcon = 'fas fa-chart-line';
                    } else {
                        $progressClass = 'bg-gradient-danger';
                        $progressIcon = 'fas fa-exclamation-triangle';
                    }
                    
                    // Handle very high percentages for display
                    $displayPercentage = $percentage > 999 ? '999+' : number_format($percentage, 1);
                @endphp
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted">
                        <i class="{{ $progressIcon }} mr-1"></i>
                        Progress
                    </small>
                    <small class="font-weight-bold">{{ $displayPercentage }}%</small>
                </div>
                
                <div class="progress progress-sm" style="height: 25px; border-radius: 15px;">
                    <div class="progress-bar {{ $progressClass }} progress-bar-striped progress-bar-animated" 
                         style="width: {{ $progressWidth }}%; transition: width 0.6s ease;"
                         role="progressbar" 
                         aria-valuenow="{{ min($percentage, 100) }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                    </div>
                </div>
            </div>
            
            <!-- Performance Summary -->
            <div class="row mt-4">
                <div class="col-12">
                    @php
                        $difference = is_numeric($data['difference']) ? floatval($data['difference']) : 0;
                        $absDifference = abs($difference);
                    @endphp
                    
                    <div class="d-flex justify-content-between align-items-center p-3 rounded" 
                         style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                        <div class="text-center flex-fill">
                            @if($difference >= 0)
                                <div class="text-success">
                                    <i class="fas fa-arrow-up fa-lg mb-1"></i>
                                    <div class="font-weight-bold h5 mb-0">+₹{{ number_format($absDifference, 2) }}</div>
                                    <small class="text-muted">
                                        {{ $difference == 0 ? 'On target' : 'Above target' }}
                                    </small>
                                </div>
                            @else
                                <div class="text-danger">
                                    <i class="fas fa-arrow-down fa-lg mb-1"></i>
                                    <div class="font-weight-bold h5 mb-0">₹{{ number_format($absDifference, 2) }}</div>
                                    <small class="text-muted">Below target</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Achievement Status -->
            <div class="mt-3 text-center">
                @php
                    $percentage = is_numeric($data['percentage']) ? floatval($data['percentage']) : 0;
                    $actual = is_numeric($data['actual']) ? floatval($data['actual']) : 0;
                    $target = is_numeric($data['target']) ? floatval($data['target']) : 0;
                @endphp
                
                @if($actual == 0 && $target > 0)
                    <span class="badge badge-pill badge-secondary px-3 py-2" style="font-size: 0.9rem;">
                        <i class="fas fa-info-circle mr-1"></i> No Income Yet
                    </span>
                @elseif($percentage >= 100)
                    <span class="badge badge-pill badge-success px-3 py-2 shadow-sm" style="font-size: 0.9rem;">
                        <i class="fas fa-trophy mr-1"></i> Target Achieved!
                    </span>
                @elseif($percentage >= 80)
                    <span class="badge badge-pill badge-warning px-3 py-2 shadow-sm" style="font-size: 0.9rem;">
                        <i class="fas fa-clock mr-1"></i> Close to Target
                    </span>
                @elseif($percentage >= 50)
                    <span class="badge badge-pill badge-info px-3 py-2 shadow-sm" style="font-size: 0.9rem;">
                        <i class="fas fa-chart-line mr-1"></i> Making Progress
                    </span>
                @else
                    <span class="badge badge-pill badge-danger px-3 py-2 shadow-sm" style="font-size: 0.9rem;">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Needs Improvement
                    </span>
                @endif
            </div>
        @else
            <!-- No Target Set Message -->
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-bullseye fa-4x text-muted opacity-50"></i>
                </div>
                <h5 class="text-muted mb-3">No Target Set</h5>
                <p class="text-muted px-3">Set your daily target income to track monthly performance and achieve your financial goals.</p>
                @if(Route::has('settings.index'))
                    <a href="{{ route('settings.index') }}" class="btn btn-primary btn-lg shadow-sm" style="border-radius: 25px;">
                        <i class="fas fa-cog mr-2"></i> Set Target Income
                    </a>
                @else
                    <div class="alert alert-info mt-3 mx-3" style="border-radius: 15px;">
                        <i class="fas fa-info-circle mr-2"></i> 
                        Please configure your daily target income in the settings to enable income tracking.
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>