<?php

namespace App\Helpers;

use Carbon\Carbon;

class ShiftHelper
{
    /**
     * Shift definitions with start/end hours and labels
     */
    const SHIFT_DEFINITIONS = [
        'pagi' => [
            'start' => 7,
            'end' => 15,
            'label' => 'Shift Pagi (07:00-14:59)',
            'display' => 'Pagi'
        ],
        'siang' => [
            'start' => 15,
            'end' => 23,
            'label' => 'Shift Siang (15:00-22:59)',
            'display' => 'Siang'
        ],
        'malam' => [
            'start' => 23,
            'end' => 7, // This wraps around midnight
            'label' => 'Shift Malam (23:00-06:59)',
            'display' => 'Malam'
        ]
    ];

    /**
     * Get current shift based on hour
     * 
     * @param int|null $hour Optional hour (0-23), defaults to current hour
     * @return string Shift name (pagi, siang, malam)
     */
    public static function getCurrentShift($hour = null): string
    {
        $hour = $hour ?? now()->hour;
        
        if ($hour >= 7 && $hour < 15) {
            return 'pagi';
        } elseif ($hour >= 15 && $hour < 23) {
            return 'siang';
        } else {
            // Covers 23:00-23:59 and 00:00-06:59
            return 'malam';
        }
    }

    /**
     * Get shift display name (capitalized)
     * 
     * @param string|null $shift Shift name, defaults to current shift
     * @return string Display name (Pagi, Siang, Malam)
     */
    public static function getShiftDisplay($shift = null): string
    {
        $shift = $shift ?? self::getCurrentShift();
        return self::SHIFT_DEFINITIONS[$shift]['display'] ?? 'Unknown';
    }

    /**
     * Get shift label with time range
     * 
     * @param string|null $shift Shift name, defaults to current shift
     * @return string Full label with time range
     */
    public static function getShiftLabel($shift = null): string
    {
        $shift = $shift ?? self::getCurrentShift();
        return self::SHIFT_DEFINITIONS[$shift]['label'] ?? 'Unknown Shift';
    }

    /**
     * Get all available shifts
     * 
     * @return array Array of shift definitions
     */
    public static function getAllShifts(): array
    {
        return self::SHIFT_DEFINITIONS;
    }

    /**
     * Check if given hour is within specific shift
     * 
     * @param int $hour Hour to check (0-23)
     * @param string $shift Shift to check against
     * @return bool
     */
    public static function isHourInShift(int $hour, string $shift): bool
    {
        if (!isset(self::SHIFT_DEFINITIONS[$shift])) {
            return false;
        }

        $definition = self::SHIFT_DEFINITIONS[$shift];
        $start = $definition['start'];
        $end = $definition['end'];

        // Handle overnight shift (malam)
        if ($shift === 'malam') {
            return ($hour >= $start) || ($hour < $end);
        }

        return ($hour >= $start && $hour < $end);
    }

    /**
     * Get shift for specific Carbon instance
     * 
     * @param Carbon $datetime
     * @return string
     */
    public static function getShiftForDateTime(Carbon $datetime): string
    {
        return self::getCurrentShift($datetime->hour);
    }

    /**
     * Get next shift after given shift
     * 
     * @param string|null $shift Current shift, defaults to current
     * @return string Next shift name
     */
    public static function getNextShift($shift = null): string
    {
        $shift = $shift ?? self::getCurrentShift();
        
        $shifts = ['pagi', 'siang', 'malam'];
        $currentIndex = array_search($shift, $shifts);
        
        if ($currentIndex === false) {
            return 'pagi'; // Default fallback
        }
        
        $nextIndex = ($currentIndex + 1) % count($shifts);
        return $shifts[$nextIndex];
    }

    /**
     * Get previous shift before given shift
     * 
     * @param string|null $shift Current shift, defaults to current
     * @return string Previous shift name
     */
    public static function getPreviousShift($shift = null): string
    {
        $shift = $shift ?? self::getCurrentShift();
        
        $shifts = ['pagi', 'siang', 'malam'];
        $currentIndex = array_search($shift, $shifts);
        
        if ($currentIndex === false) {
            return 'malam'; // Default fallback
        }
        
        $prevIndex = ($currentIndex - 1 + count($shifts)) % count($shifts);
        return $shifts[$prevIndex];
    }

    /**
     * Get shift start time for given date
     * 
     * @param string $shift
     * @param Carbon|null $date
     * @return Carbon
     */
    public static function getShiftStartTime(string $shift, Carbon $date = null): Carbon
    {
        $date = $date ?? now();
        $definition = self::SHIFT_DEFINITIONS[$shift] ?? self::SHIFT_DEFINITIONS['pagi'];
        
        $startTime = $date->copy()->startOfDay()->addHours($definition['start']);
        
        // Handle overnight shift (malam) - if we're asking for malam shift start on current day
        // but it's already past 7 AM, we want the malam shift that starts tonight
        if ($shift === 'malam' && $date->hour >= 7) {
            $startTime = $date->copy()->startOfDay()->addHours(23);
        }
        
        return $startTime;
    }

    /**
     * Get shift end time for given date
     * 
     * @param string $shift
     * @param Carbon|null $date
     * @return Carbon
     */
    public static function getShiftEndTime(string $shift, Carbon $date = null): Carbon
    {
        $date = $date ?? now();
        $definition = self::SHIFT_DEFINITIONS[$shift] ?? self::SHIFT_DEFINITIONS['pagi'];
        
        $endHour = $definition['end'];
        
        // Handle overnight shift (malam)
        if ($shift === 'malam') {
            // Malam shift ends at 7 AM next day
            return $date->copy()->addDay()->startOfDay()->addHours($endHour);
        }
        
        return $date->copy()->startOfDay()->addHours($endHour);
    }

    /**
     * Get shift duration in hours
     * 
     * @param string $shift
     * @return int Duration in hours
     */
    public static function getShiftDuration(string $shift): int
    {
        $definition = self::SHIFT_DEFINITIONS[$shift] ?? self::SHIFT_DEFINITIONS['pagi'];
        
        if ($shift === 'malam') {
            // Malam: 23:00 to 07:00 = 8 hours
            return 8;
        }
        
        return $definition['end'] - $definition['start'];
    }

    /**
     * Check if current time is within break time for any shift
     * This is a placeholder for future break time functionality
     * 
     * @param string|null $shift
     * @return bool
     */
    public static function isBreakTime($shift = null): bool
    {
        // TODO: Implement break time logic
        // For now, always return false
        return false;
    }

    /**
     * Get formatted shift info for display
     * 
     * @param string|null $shift
     * @param bool $includeTime Whether to include time range
     * @return array
     */
    public static function getShiftInfo($shift = null, bool $includeTime = true): array
    {
        $shift = $shift ?? self::getCurrentShift();
        $definition = self::SHIFT_DEFINITIONS[$shift] ?? self::SHIFT_DEFINITIONS['pagi'];
        
        $info = [
            'name' => $shift,
            'display' => $definition['display'],
            'duration' => self::getShiftDuration($shift)
        ];
        
        if ($includeTime) {
            $info['label'] = $definition['label'];
            $info['start_hour'] = $definition['start'];
            $info['end_hour'] = $definition['end'];
        }
        
        return $info;
    }

    /**
     * Debug function to test shift logic
     * 
     * @return array Test results for all hours
     */
    public static function debugShiftLogic(): array
    {
        $results = [];
        
        for ($hour = 0; $hour < 24; $hour++) {
            $shift = self::getCurrentShift($hour);
            $results[] = [
                'hour' => sprintf('%02d:00', $hour),
                'shift' => $shift,
                'display' => self::getShiftDisplay($shift),
                'label' => self::getShiftLabel($shift)
            ];
        }
        
        return $results;
    }
}