<?php

if (!function_exists('formatINR')) {
    /**
     * Format amount in INR with icon and proper styling
     * 
     * @param float $amount
     * @param string $type - 'positive', 'negative', 'warning', 'info'
     * @return string
     */
    function formatINR($amount, $type = 'positive') {
        $cssClass = 'currency-amount currency-' . $type;
        return '<span class="' . $cssClass . '"><i class="fas fa-rupee-sign rupee-icon"></i>' . number_format($amount, 2) . '</span>';
    }
}

if (!function_exists('formatINRSimple')) {
    /**
     * Format amount in INR with simple formatting
     * 
     * @param float $amount
     * @return string
     */
    function formatINRSimple($amount) {
        return '₹' . number_format($amount, 2);
    }
}

if (!function_exists('inrIcon')) {
    /**
     * Get INR rupee icon HTML
     * 
     * @param string $class
     * @return string
     */
    function inrIcon($class = 'fas fa-rupee-sign') {
        return '<i class="' . $class . '"></i>';
    }
}
