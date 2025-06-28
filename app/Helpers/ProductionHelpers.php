<?php
// File: app/Helpers/ProductionHelpers.php

if (!function_exists('getStatusColor')) {
    function getStatusColor($status) {
        return match($status) {
            'completed' => 'success',
            'qc_passed' => 'success', 
            'qc_failed' => 'danger',
            'qc_rework' => 'warning',
            'quality_review' => 'secondary',
            'in_progress' => 'warning',
            'planned' => 'info',
            default => 'secondary'
        };
    }
}

if (!function_exists('getStatusLabel')) {
    function getStatusLabel($status) {
        return match($status) {
            'completed' => 'Selesai',
            'qc_passed' => 'QC Lolos',
            'qc_failed' => 'QC Gagal', 
            'qc_rework' => 'Perlu Rework',
            'quality_review' => 'Review QC',
            'in_progress' => 'Berlangsung',
            'planned' => 'Direncanakan',
            default => ucfirst($status)
        };
    }
}

if (!function_exists('getQCStatusColor')) {
    function getQCStatusColor($status) {
        return match($status) {
            'approved' => 'success',
            'rejected' => 'danger', 
            'rework' => 'warning',
            default => 'secondary'
        };
    }
}

if (!function_exists('getQCStatusLabel')) {
    function getQCStatusLabel($status) {
        return match($status) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'rework' => 'Rework', 
            default => ucfirst($status)
        };
    }
}

if (!function_exists('getMovementTypeColor')) {
    function getMovementTypeColor($type) {
        return match($type) {
            'in' => 'success',
            'out' => 'danger',
            'adjustment' => 'warning',
            default => 'secondary'
        };
    }
}

if (!function_exists('getMovementTypeLabel')) {
    function getMovementTypeLabel($type) {
        return match($type) {
            'in' => 'Stock IN',
            'out' => 'Stock OUT', 
            'adjustment' => 'Adjustment',
            default => ucfirst($type)
        };
    }
}