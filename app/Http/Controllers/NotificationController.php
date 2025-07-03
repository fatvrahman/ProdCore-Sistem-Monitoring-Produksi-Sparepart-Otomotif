<?php
// File: app/Http/Controllers/NotificationController.php - FIXED VERSION

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications with filters and pagination
     * ✅ FIXED: Proper data handling to prevent htmlspecialchars() errors
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $perPage = $request->get('per_page', 15);
            $type = $request->get('type');
            $status = $request->get('status');

            $query = Notification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($type && $type !== 'all') {
                $query->where('type', $type);
            }

            if ($status === 'read') {
                $query->whereNotNull('read_at');
            } elseif ($status === 'unread') {
                $query->whereNull('read_at');
            }

            $notifications = $query->paginate($perPage);

            // ✅ SAFE DATA TRANSFORMATION - Convert everything to safe strings
            $notifications->getCollection()->transform(function ($notification) {
                // Ensure all text fields are strings
                $notification->title = (string) ($notification->title ?? 'Notifikasi');
                $notification->message = (string) ($notification->message ?? 'Tidak ada pesan');
                $notification->type = (string) ($notification->type ?? 'system');
                $notification->priority = (string) ($notification->priority ?? 'normal');
                
                // Safe icon handling
                $notification->icon = (string) ($notification->icon ?? 'bi-bell-fill');
                $notification->badge_class = (string) ($notification->badge_class ?? 'bg-primary');
                $notification->action_url = $notification->action_url ? (string) $notification->action_url : null;
                
                // Handle time_ago safely
                $notification->time_ago = (string) ($notification->time_ago ?? 'Baru saja');
                
                // Handle is_read safely  
                $notification->is_read = (bool) $notification->read_at;
                
                // ✅ CRITICAL: Handle data field safely
                if (is_string($notification->data)) {
                    try {
                        $decodedData = json_decode($notification->data, true);
                        $notification->data = is_array($decodedData) ? $decodedData : [];
                    } catch (\Exception $e) {
                        $notification->data = [];
                    }
                } elseif (!is_array($notification->data)) {
                    $notification->data = [];
                }
                
                // Create safe formatted_data for display
                $notification->formatted_data = $notification->data;
                
                return $notification;
            });

            // Calculate stats safely with explicit casting
            $totalNotifications = (int) Notification::where('user_id', $user->id)->count();
            $unreadNotifications = (int) Notification::where('user_id', $user->id)->whereNull('read_at')->count();
            $todayNotifications = (int) Notification::where('user_id', $user->id)->whereDate('created_at', today())->count();
            $weekNotifications = (int) Notification::where('user_id', $user->id)->where('created_at', '>=', now()->startOfWeek())->count();
            
            $stats = [
                'total' => $totalNotifications,
                'unread' => $unreadNotifications,
                'today' => $todayNotifications,
                'this_week' => $weekNotifications,
                'by_type' => [
                    'production' => (int) Notification::where('user_id', $user->id)->where('type', 'production')->count(),
                    'qc' => (int) Notification::where('user_id', $user->id)->where('type', 'qc')->count(),
                    'stock' => (int) Notification::where('user_id', $user->id)->where('type', 'stock')->count(),
                    'distribution' => (int) Notification::where('user_id', $user->id)->where('type', 'distribution')->count(),
                    'system' => (int) Notification::where('user_id', $user->id)->where('type', 'system')->count()
                ],
                'by_priority' => [
                    'urgent' => (int) Notification::where('user_id', $user->id)->where('priority', 'urgent')->count(),
                    'high' => (int) Notification::where('user_id', $user->id)->where('priority', 'high')->count(),
                    'normal' => (int) Notification::where('user_id', $user->id)->where('priority', 'normal')->count(),
                    'low' => (int) Notification::where('user_id', $user->id)->where('priority', 'low')->count()
                ]
            ];

            // Notification types for filter dropdown
            $notificationTypes = [
                'all' => 'Semua Notifikasi',
                'production' => 'Produksi',
                'qc' => 'Quality Control',
                'stock' => 'Stok & Gudang',
                'distribution' => 'Distribusi',
                'system' => 'Sistem'
            ];

            // ✅ SAFE: Create types for tabs with explicit values
            $types = [
                'all' => $stats['by_type']['production'] + $stats['by_type']['qc'] + $stats['by_type']['stock'] + $stats['by_type']['distribution'] + $stats['by_type']['system'],
                'production' => $stats['by_type']['production'],
                'qc' => $stats['by_type']['qc'],
                'stock' => $stats['by_type']['stock'],
                'distribution' => $stats['by_type']['distribution'],
                'system' => $stats['by_type']['system']
            ];

            return view('notifications.index', compact(
                'notifications', 
                'stats', 
                'notificationTypes',
                'types'
            ));

        } catch (\Exception $e) {
            Log::error('Failed to load notifications index', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'user_id' => auth()->id(),
                'request' => $request->all()
            ]);

            // ✅ SAFE FALLBACK: Return with empty but properly structured data
            $emptyNotifications = collect()->paginate(15);
            
            $stats = [
                'total' => 0,
                'unread' => 0,
                'today' => 0,
                'this_week' => 0,
                'by_type' => [
                    'production' => 0,
                    'qc' => 0,
                    'stock' => 0,
                    'distribution' => 0,
                    'system' => 0
                ],
                'by_priority' => [
                    'urgent' => 0,
                    'high' => 0,
                    'normal' => 0,
                    'low' => 0
                ]
            ];
            
            $notificationTypes = [
                'all' => 'Semua Notifikasi',
                'production' => 'Produksi',
                'qc' => 'Quality Control',
                'stock' => 'Stok & Gudang',
                'distribution' => 'Distribusi',
                'system' => 'Sistem'
            ];
            
            $types = [
                'all' => 0,
                'production' => 0,
                'qc' => 0,
                'stock' => 0,
                'distribution' => 0,
                'system' => 0
            ];

            return view('notifications.index', [
                'notifications' => $emptyNotifications,
                'stats' => $stats,
                'notificationTypes' => $notificationTypes,
                'types' => $types
            ])->with('error', 'Terjadi kesalahan saat memuat notifikasi: ' . $e->getMessage());
        }
    }

    /**
     * Mark a notification as read
     * ✅ COMPATIBLE: Works with both web and API requests
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        try {
            $user = Auth::user();

            // Authorization check
            if ($notification->user_id !== $user->id) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Unauthorized access to notification'
                ], 403);
            }

            // Mark as read if not already read
            if (!$notification->read_at) {
                $notification->update(['read_at' => now()]);
                
                Log::info('Notification marked as read', [
                    'notification_id' => $notification->id,
                    'user_id' => $user->id,
                    'title' => $notification->title
                ]);
            }

            // Get updated unread count
            $unreadCount = Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi ditandai sudah dibaca',
                'data' => [
                    'unread_count' => $unreadCount,
                    'notification_id' => $notification->id
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', [
                'error' => $e->getMessage(),
                'notification_id' => $notification->id ?? null,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai notifikasi sebagai dibaca'
            ], 500);
        }
    }

    /**
     * Mark all notifications as read for current user
     * ✅ COMPATIBLE: Works with both web and API requests
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $count = Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            Log::info('All notifications marked as read', [
                'user_id' => $user->id,
                'count' => $count
            ]);

            return response()->json([
                'success' => true,
                'message' => "Berhasil menandai {$count} notifikasi sebagai sudah dibaca",
                'data' => [
                    'marked_count' => $count,
                    'unread_count' => 0
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai semua notifikasi sebagai dibaca'
            ], 500);
        }
    }

    /**
     * Get unread count and recent notifications for real-time navbar
     * ✅ OPTIMIZED: For AJAX calls from navbar dropdown
     */
    public function getUnreadCount(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $limit = $request->get('limit', 5);

            // Get unread count
            $unreadCount = Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->count();

            // Get recent notifications for dropdown
            $recentNotifications = Notification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function($notification) {
                    return [
                        'id' => $notification->id,
                        'title' => (string) $notification->title,
                        'message' => (string) $notification->message,
                        'type' => (string) $notification->type,
                        'priority' => (string) $notification->priority,
                        'icon' => $notification->icon,
                        'badge_class' => $notification->badge_class,
                        'time_ago' => $notification->time_ago,
                        'action_url' => $notification->action_url,
                        'is_read' => (bool) $notification->read_at,
                        'created_at' => $notification->created_at->toISOString(),
                        'formatted_data' => is_array($notification->data) 
                            ? $notification->data 
                            : json_decode($notification->data ?? '{}', true)
                    ];
                });

            return response()->json([
                'success' => true,
                'unread_count' => $unreadCount,
                'recent_notifications' => $recentNotifications,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get unread count', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat notifikasi',
                'unread_count' => 0,
                'recent_notifications' => []
            ], 500);
        }
    }

    /**
     * Get notifications with filters (for API calls)
     * ✅ NEW: Support for dropdown filters
     */
    public function getFilteredNotifications(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $type = $request->get('type', 'all');
            $limit = $request->get('limit', 10);
            $page = $request->get('page', 1);

            $query = Notification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc');

            // Apply type filter
            if ($type !== 'all') {
                if ($type === 'urgent') {
                    $query->where('priority', 'urgent');
                } else {
                    $query->where('type', $type);
                }
            }

            $notifications = $query->paginate($limit, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => $notifications->items(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total()
                ],
                'filter' => $type
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get filtered notifications', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'type' => $type ?? 'unknown'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat notifikasi dengan filter'
            ], 500);
        }
    }

    /**
     * Get notification statistics for dashboard
     * ✅ NEW: Comprehensive stats for admin dashboard
     */
    public function getNotificationStats(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $period = $request->get('period', '7d');
            
            $days = match($period) {
                '7d' => 7,
                '30d' => 30,
                '3m' => 90,
                default => 7
            };

            $startDate = now()->subDays($days);

            $stats = [
                'overview' => [
                    'total' => Notification::where('user_id', $user->id)->count(),
                    'unread' => Notification::where('user_id', $user->id)->whereNull('read_at')->count(),
                    'today' => Notification::where('user_id', $user->id)->whereDate('created_at', today())->count(),
                    'this_week' => Notification::where('user_id', $user->id)->where('created_at', '>=', now()->startOfWeek())->count()
                ],
                'by_type' => [
                    'production' => Notification::where('user_id', $user->id)->where('type', 'production')->count(),
                    'qc' => Notification::where('user_id', $user->id)->where('type', 'qc')->count(),
                    'stock' => Notification::where('user_id', $user->id)->where('type', 'stock')->count(),
                    'distribution' => Notification::where('user_id', $user->id)->where('type', 'distribution')->count(),
                    'system' => Notification::where('user_id', $user->id)->where('type', 'system')->count()
                ],
                'by_priority' => [
                    'urgent' => Notification::where('user_id', $user->id)->where('priority', 'urgent')->count(),
                    'high' => Notification::where('user_id', $user->id)->where('priority', 'high')->count(),
                    'normal' => Notification::where('user_id', $user->id)->where('priority', 'normal')->count(),
                    'low' => Notification::where('user_id', $user->id)->where('priority', 'low')->count()
                ],
                'trend' => $this->getNotificationTrend($user->id, $days),
                'period' => $period
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'generated_at' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get notification stats', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat statistik notifikasi'
            ], 500);
        }
    }

    /**
     * Create a test notification (for development/testing)
     * ✅ UTILITY: Testing notification system
     */
    public function createTestNotification(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $notification = Notification::createForUser(
                $user->id,
                $request->get('type', 'system'),
                $request->get('title', 'Test Notification'),
                $request->get('message', 'This is a test notification from the system'),
                $request->get('data', ['test' => true]),
                $request->get('priority', 'normal'),
                $request->get('action_url')
            );

            Log::info('Test notification created', [
                'notification_id' => $notification->id,
                'user_id' => $user->id,
                'type' => $notification->type
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test notification created successfully',
                'notification' => [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'priority' => $notification->priority
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create test notification', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat test notification'
            ], 500);
        }
    }

    /**
     * Get notification trend data for charts
     * ✅ PRIVATE: Helper method for statistics
     */
    private function getNotificationTrend(int $userId, int $days): array
    {
        $trend = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Notification::where('user_id', $userId)
                ->whereDate('created_at', $date)
                ->count();
            
            $trend[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'count' => $count
            ];
        }
        
        return $trend;
    }
}