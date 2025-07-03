<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'action_url',
        'priority',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    // Accessors
    public function getIsReadAttribute(): bool
    {
        return !is_null($this->read_at);
    }

    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getIconAttribute(): string
    {
        return match($this->type) {
            'production' => 'bi-gear-fill',
            'qc' => 'bi-shield-check',
            'stock' => 'bi-box-seam',
            'distribution' => 'bi-truck',
            'system' => 'bi-gear',
            'alert' => 'bi-exclamation-triangle-fill',
            'warning' => 'bi-exclamation-circle-fill',
            'success' => 'bi-check-circle-fill',
            default => 'bi-bell-fill'
        };
    }

    public function getBadgeClassAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'bg-danger',
            'high' => 'bg-warning',
            'normal' => 'bg-primary',
            'low' => 'bg-secondary',
            default => 'bg-info'
        };
    }

    public function getFormattedDataAttribute(): array
    {
        $data = $this->data ?? [];
        
        // Format data berdasarkan type notification
        return match($this->type) {
            'production' => [
                'batch_number' => $data['batch_number'] ?? '',
                'quantity' => $data['quantity'] ?? 0,
                'status' => $data['status'] ?? '',
                'operator' => $data['operator'] ?? ''
            ],
            'qc' => [
                'inspection_number' => $data['inspection_number'] ?? '',
                'status' => $data['status'] ?? '',
                'pass_rate' => $data['pass_rate'] ?? 0,
                'inspector' => $data['inspector'] ?? ''
            ],
            'stock' => [
                'item_name' => $data['item_name'] ?? '',
                'current_stock' => $data['current_stock'] ?? 0,
                'minimum_stock' => $data['minimum_stock'] ?? 0,
                'percentage' => $data['percentage'] ?? 0
            ],
            'distribution' => [
                'delivery_number' => $data['delivery_number'] ?? '',
                'customer' => $data['customer'] ?? '',
                'status' => $data['status'] ?? '',
                'quantity' => $data['quantity'] ?? 0
            ],
            default => $data
        };
    }

    // Methods
    public function markAsRead(): bool
    {
        $this->read_at = Carbon::now();
        return $this->save();
    }

    public function markAsUnread(): bool
    {
        $this->read_at = null;
        return $this->save();
    }

    // Static methods
    public static function createForUser(
        int $userId, 
        string $type, 
        string $title, 
        string $message,
        array $data = [],
        string $priority = 'normal',
        string $actionUrl = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'priority' => $priority,
            'action_url' => $actionUrl
        ]);
    }

    public static function createForRole(
        string $roleName, 
        string $type, 
        string $title, 
        string $message,
        array $data = [],
        string $priority = 'normal',
        string $actionUrl = null
    ): int {
        $users = User::whereHas('role', function($query) use ($roleName) {
            $query->where('name', $roleName);
        })->where('status', 'active')->get();

        $count = 0;
        foreach ($users as $user) {
            self::createForUser($user->id, $type, $title, $message, $data, $priority, $actionUrl);
            $count++;
        }

        return $count;
    }

    public static function createBroadcast(
        string $type, 
        string $title, 
        string $message,
        array $data = [],
        string $priority = 'normal',
        string $actionUrl = null
    ): int {
        $users = User::where('status', 'active')->get();

        $count = 0;
        foreach ($users as $user) {
            self::createForUser($user->id, $type, $title, $message, $data, $priority, $actionUrl);
            $count++;
        }

        return $count;
    }

    // Statistics
    public static function getUnreadCountForUser(int $userId): int
    {
        return self::where('user_id', $userId)->unread()->count();
    }

    public static function getRecentForUser(int $userId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function markAllAsReadForUser(int $userId): int
    {
        return self::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);
    }
}