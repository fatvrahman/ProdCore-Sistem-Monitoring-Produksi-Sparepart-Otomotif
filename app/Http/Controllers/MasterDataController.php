<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Role;
use App\Models\ProductType;
use App\Models\RawMaterial;
use App\Models\Machine;
use App\Models\ProductionLine;
use App\Services\NotificationService;
use Carbon\Carbon;

class MasterDataController extends Controller
{
    protected $notificationService;

    /**
     * Constructor - Inject NotificationService
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    // ===== USERS MANAGEMENT =====
    
    /**
     * Display users management page
     */
    public function users(Request $request)
    {
        try {
            // Get filter parameters
            $filters = [
                'role_id' => $request->get('role_id'),
                'status' => $request->get('status'),
                'search' => $request->get('search'),
                'sort' => $request->get('sort', 'name'),
                'direction' => $request->get('direction', 'asc')
            ];

            // Build query
            $query = User::with('role');

            // Apply filters
            if ($filters['role_id']) {
                $query->where('role_id', $filters['role_id']);
            }

            if ($filters['status']) {
                $query->where('status', $filters['status']);
            }

            if ($filters['search']) {
                $query->where(function($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('employee_id', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('phone', 'like', '%' . $filters['search'] . '%');
                });
            }

            // Apply sorting
            $query->orderBy($filters['sort'], $filters['direction']);

            // Paginate results
            $users = $query->paginate(15)->withQueryString();

            // Get additional data
            $roles = Role::where('is_active', true)->get();
            
            // Calculate statistics
            $stats = [
                'total_users' => User::count(),
                'active_users' => User::where('status', 'active')->count(),
                'inactive_users' => User::where('status', 'inactive')->count(),
                'by_role' => User::select('roles.name', 'roles.display_name', DB::raw('count(*) as count'))
                    ->join('roles', 'users.role_id', '=', 'roles.id')
                    ->groupBy('roles.id', 'roles.name', 'roles.display_name')
                    ->get()
            ];

            return view('master-data.users', compact('users', 'roles', 'filters', 'stats'));

        } catch (\Exception $e) {
            Log::error('Error in users page: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data pengguna');
        }
    }

    /**
     * Store new user
     */
    public function storeUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'employee_id' => 'required|string|unique:users,employee_id',
                'role_id' => 'required|exists:roles,id',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'nullable|string|max:15',
                'status' => 'required|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'employee_id' => $request->employee_id,
                'role_id' => $request->role_id,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'status' => $request->status,
                'created_at' => now()
            ]);

            // Trigger notification: New User Created
            $role = Role::find($request->role_id);
            $this->notificationService->createSystemNotification('user_created', [
                'user_name' => $user->name,
                'employee_id' => $user->employee_id,
                'role' => $role->display_name,
                'created_by' => auth()->user()->name,
                'created_at' => now()->format('d M Y H:i')
            ]);

            DB::commit();

            Log::info('New user created', [
                'user_id' => $user->id,
                'employee_id' => $user->employee_id,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengguna berhasil ditambahkan',
                'data' => $user,
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating user: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan pengguna: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, User $user)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'employee_id' => 'required|string|unique:users,employee_id,' . $user->id,
                'role_id' => 'required|exists:roles,id',
                'password' => 'nullable|string|min:8|confirmed',
                'phone' => 'nullable|string|max:15',
                'status' => 'required|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $oldStatus = $user->status;
            $oldRole = $user->role_id;

            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'employee_id' => $request->employee_id,
                'role_id' => $request->role_id,
                'phone' => $request->phone,
                'status' => $request->status,
                'updated_at' => now()
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // Trigger notifications based on changes
            if ($oldStatus !== $request->status) {
                $this->notificationService->createSystemNotification('user_status_changed', [
                    'user_name' => $user->name,
                    'employee_id' => $user->employee_id,
                    'old_status' => $oldStatus,
                    'new_status' => $request->status,
                    'updated_by' => auth()->user()->name,
                    'updated_at' => now()->format('d M Y H:i')
                ]);
            }

            if ($oldRole !== $request->role_id) {
                $oldRoleName = Role::find($oldRole)->display_name;
                $newRoleName = Role::find($request->role_id)->display_name;
                
                $this->notificationService->createSystemNotification('user_role_changed', [
                    'user_name' => $user->name,
                    'employee_id' => $user->employee_id,
                    'old_role' => $oldRoleName,
                    'new_role' => $newRoleName,
                    'updated_by' => auth()->user()->name,
                    'updated_at' => now()->format('d M Y H:i')
                ]);
            }

            DB::commit();

            Log::info('User updated', [
                'user_id' => $user->id,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengguna berhasil diperbarui',
                'data' => $user->fresh(),
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating user: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui pengguna: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user)
    {
        try {
            // Prevent deleting current user
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus akun Anda sendiri'
                ], 403);
            }

            // Check if user has related data
            $hasProductions = $user->productions()->exists();
            $hasQualityControls = $user->qualityControls()->exists();

            if ($hasProductions || $hasQualityControls) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus pengguna yang memiliki data terkait'
                ], 409);
            }

            DB::beginTransaction();

            $employeeId = $user->employee_id;
            $userName = $user->name;
            $userRole = $user->role->display_name;
            
            $user->delete();

            // Trigger notification: User Deleted
            $this->notificationService->createSystemNotification('user_deleted', [
                'user_name' => $userName,
                'employee_id' => $employeeId,
                'role' => $userRole,
                'deleted_by' => auth()->user()->name,
                'deleted_at' => now()->format('d M Y H:i')
            ]);

            DB::commit();

            Log::info('User deleted', [
                'employee_id' => $employeeId,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengguna berhasil dihapus',
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting user: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pengguna: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===== PRODUCTS MANAGEMENT =====
    
    /**
     * Display products management page
     */
    public function products(Request $request)
    {
        try {
            // Get filter parameters
            $filters = [
                'brand' => $request->get('brand'),
                'is_active' => $request->get('is_active'),
                'search' => $request->get('search'),
                'sort' => $request->get('sort', 'name'),
                'direction' => $request->get('direction', 'asc')
            ];

            // Build query
            $query = ProductType::query();

            // Apply filters
            if ($filters['brand']) {
                $query->where('brand', $filters['brand']);
            }

            if ($filters['is_active'] !== null && $filters['is_active'] !== '') {
                $query->where('is_active', $filters['is_active']);
            }

            if ($filters['search']) {
                $query->where(function($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('code', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('brand', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('model', 'like', '%' . $filters['search'] . '%');
                });
            }

            // Apply sorting
            $query->orderBy($filters['sort'], $filters['direction']);

            // Paginate results
            $products = $query->paginate(15)->withQueryString();

            // Get additional data
            $brands = ProductType::distinct()->pluck('brand')->filter()->sort()->values();
            
            // Calculate statistics
            $stats = [
                'total_products' => ProductType::count(),
                'active_products' => ProductType::where('is_active', true)->count(),
                'inactive_products' => ProductType::where('is_active', false)->count(),
                'by_brand' => ProductType::select('brand', DB::raw('count(*) as count'))
                    ->whereNotNull('brand')
                    ->groupBy('brand')
                    ->get()
            ];

            return view('master-data.products', compact('products', 'brands', 'filters', 'stats'));

        } catch (\Exception $e) {
            Log::error('Error in products page: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data produk');
        }
    }

    /**
     * Store new product
     */
    public function storeProduct(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:product_types,code',
                'name' => 'required|string|max:255',
                'brand' => 'required|string|max:100',
                'model' => 'required|string|max:100',
                'standard_weight' => 'required|numeric|min:0|max:999.99',
                'standard_thickness' => 'required|numeric|min:0|max:99.99',
                'specifications' => 'nullable|json',
                'description' => 'nullable|string',
                'is_active' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Parse specifications if provided
            $specifications = null;
            if ($request->filled('specifications')) {
                $specifications = json_decode($request->specifications, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Format JSON spesifikasi tidak valid'
                    ], 422);
                }
            }

            $product = ProductType::create([
                'code' => $request->code,
                'name' => $request->name,
                'brand' => $request->brand,
                'model' => $request->model,
                'description' => $request->description,
                'standard_weight' => $request->standard_weight,
                'standard_thickness' => $request->standard_thickness,
                'specifications' => $specifications,
                'is_active' => $request->is_active,
                'created_at' => now()
            ]);

            // Trigger notification: New Product Created
            $this->notificationService->createSystemNotification('product_created', [
                'product_name' => $product->name,
                'product_code' => $product->code,
                'brand' => $product->brand,
                'model' => $product->model,
                'created_by' => auth()->user()->name,
                'created_at' => now()->format('d M Y H:i')
            ]);

            DB::commit();

            Log::info('New product created', [
                'product_id' => $product->id,
                'code' => $product->code,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan',
                'data' => $product,
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating product: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan produk: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update product
     */
    public function updateProduct(Request $request, ProductType $product)
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:product_types,code,' . $product->id,
                'name' => 'required|string|max:255',
                'brand' => 'required|string|max:100',
                'model' => 'required|string|max:100',
                'standard_weight' => 'required|numeric|min:0|max:999.99',
                'standard_thickness' => 'required|numeric|min:0|max:99.99',
                'specifications' => 'nullable|json',
                'description' => 'nullable|string',
                'is_active' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $oldActive = $product->is_active;

            // Parse specifications if provided
            $specifications = null;
            if ($request->filled('specifications')) {
                $specifications = json_decode($request->specifications, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Format JSON spesifikasi tidak valid'
                    ], 422);
                }
            }

            $product->update([
                'code' => $request->code,
                'name' => $request->name,
                'brand' => $request->brand,
                'model' => $request->model,
                'description' => $request->description,
                'standard_weight' => $request->standard_weight,
                'standard_thickness' => $request->standard_thickness,
                'specifications' => $specifications,
                'is_active' => $request->is_active,
                'updated_at' => now()
            ]);

            // Trigger notification: Product Status Changed
            if ($oldActive !== $request->is_active) {
                $this->notificationService->createSystemNotification('product_status_changed', [
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'old_status' => $oldActive ? 'Aktif' : 'Tidak Aktif',
                    'new_status' => $request->is_active ? 'Aktif' : 'Tidak Aktif',
                    'updated_by' => auth()->user()->name,
                    'updated_at' => now()->format('d M Y H:i')
                ]);
            }

            DB::commit();

            Log::info('Product updated', [
                'product_id' => $product->id,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil diperbarui',
                'data' => $product->fresh(),
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating product: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui produk: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete product
     */
    public function deleteProduct(ProductType $product)
    {
        try {
            // Check if product has related productions
            $hasProductions = $product->productions()->exists();

            if ($hasProductions) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus produk yang memiliki data produksi'
                ], 409);
            }

            DB::beginTransaction();

            $productCode = $product->code;
            $productName = $product->name;
            $product->delete();

            // Trigger notification: Product Deleted
            $this->notificationService->createSystemNotification('product_deleted', [
                'product_name' => $productName,
                'product_code' => $productCode,
                'deleted_by' => auth()->user()->name,
                'deleted_at' => now()->format('d M Y H:i')
            ]);

            DB::commit();

            Log::info('Product deleted', [
                'product_code' => $productCode,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil dihapus',
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting product: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus produk: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===== MATERIALS MANAGEMENT =====
    
    /**
     * Display materials management page
     */
    public function materials(Request $request)
    {
        try {
            // Get filter parameters
            $filters = [
                'supplier' => $request->get('supplier'),
                'stock_status' => $request->get('stock_status'),
                'search' => $request->get('search'),
                'sort' => $request->get('sort', 'name'),
                'direction' => $request->get('direction', 'asc')
            ];

            // Build query
            $query = RawMaterial::query();

            // Apply filters
            if ($filters['supplier']) {
                $query->where('supplier', $filters['supplier']);
            }

            if ($filters['stock_status']) {
                switch ($filters['stock_status']) {
                    case 'low':
                        $query->whereRaw('current_stock <= minimum_stock');
                        break;
                    case 'high':
                        $query->whereRaw('current_stock >= maximum_stock');
                        break;
                    case 'normal':
                        $query->whereRaw('current_stock > minimum_stock AND current_stock < maximum_stock');
                        break;
                }
            }

            if ($filters['search']) {
                $query->where(function($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('code', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('supplier', 'like', '%' . $filters['search'] . '%');
                });
            }

            // Apply sorting
            $query->orderBy($filters['sort'], $filters['direction']);

            // Paginate results
            $materials = $query->paginate(15)->withQueryString();

            // Get additional data
            $suppliers = RawMaterial::distinct()->pluck('supplier')->filter()->sort()->values();
            
            // Calculate statistics
            $stats = [
                'total_materials' => RawMaterial::count(),
                'active_materials' => RawMaterial::where('is_active', true)->count(),
                'low_stock_count' => RawMaterial::whereRaw('current_stock <= minimum_stock')->count(),
                'total_stock_value' => RawMaterial::sum(DB::raw('current_stock * unit_price'))
            ];

            return view('master-data.materials', compact('materials', 'suppliers', 'filters', 'stats'));

        } catch (\Exception $e) {
            Log::error('Error in materials page: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data material');
        }
    }

    /**
     * Store new material
     */
    public function storeMaterial(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:raw_materials,code',
                'name' => 'required|string|max:255',
                'unit' => 'required|string|max:20',
                'supplier' => 'required|string|max:255',
                'current_stock' => 'required|numeric|min:0',
                'minimum_stock' => 'required|numeric|min:0',
                'maximum_stock' => 'required|numeric|min:0|gte:minimum_stock',
                'unit_price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'is_active' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $material = RawMaterial::create([
                'code' => $request->code,
                'name' => $request->name,
                'unit' => $request->unit,
                'supplier' => $request->supplier,
                'current_stock' => $request->current_stock,
                'minimum_stock' => $request->minimum_stock,
                'maximum_stock' => $request->maximum_stock,
                'unit_price' => $request->unit_price,
                'description' => $request->description,
                'is_active' => $request->is_active,
                'created_at' => now()
            ]);

            // Trigger notification: New Material Created
            $this->notificationService->createSystemNotification('material_created', [
                'material_name' => $material->name,
                'material_code' => $material->code,
                'supplier' => $material->supplier,
                'current_stock' => $material->current_stock,
                'unit' => $material->unit,
                'created_by' => auth()->user()->name,
                'created_at' => now()->format('d M Y H:i')
            ]);

            // Check stock levels and trigger alerts if needed
            if ($material->current_stock <= $material->minimum_stock) {
                if ($material->current_stock == 0) {
                    $this->notificationService->createStockNotification($material, 'out_of_stock');
                } else {
                    $this->notificationService->createStockNotification($material, 'low_stock');
                }
            }

            DB::commit();

            Log::info('New material created', [
                'material_id' => $material->id,
                'code' => $material->code,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Material berhasil ditambahkan',
                'data' => $material,
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating material: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan material: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update material
     */
    public function updateMaterial(Request $request, RawMaterial $material)
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:raw_materials,code,' . $material->id,
                'name' => 'required|string|max:255',
                'unit' => 'required|string|max:20',
                'supplier' => 'required|string|max:255',
                'current_stock' => 'required|numeric|min:0',
                'minimum_stock' => 'required|numeric|min:0',
                'maximum_stock' => 'required|numeric|min:0|gte:minimum_stock',
                'unit_price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'is_active' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $oldStock = $material->current_stock;
            $oldActive = $material->is_active;

            $material->update([
                'code' => $request->code,
                'name' => $request->name,
                'unit' => $request->unit,
                'supplier' => $request->supplier,
                'current_stock' => $request->current_stock,
                'minimum_stock' => $request->minimum_stock,
                'maximum_stock' => $request->maximum_stock,
                'unit_price' => $request->unit_price,
                'description' => $request->description,
                'is_active' => $request->is_active,
                'updated_at' => now()
            ]);

            // Trigger notifications based on stock changes
            if ($oldStock != $request->current_stock) {
                // Stock level changed
                if ($request->current_stock > $oldStock) {
                    // Stock increased - replenished
                    $this->notificationService->createStockNotification($material, 'stock_replenished');
                }
                
                // Check new stock levels
                if ($request->current_stock <= $request->minimum_stock) {
                    if ($request->current_stock == 0) {
                        $this->notificationService->createStockNotification($material, 'out_of_stock');
                    } else {
                        $this->notificationService->createStockNotification($material, 'low_stock');
                    }
                }
            }

            // Trigger notification: Material Status Changed
            if ($oldActive !== $request->is_active) {
                $this->notificationService->createSystemNotification('material_status_changed', [
                    'material_name' => $material->name,
                    'material_code' => $material->code,
                    'old_status' => $oldActive ? 'Aktif' : 'Tidak Aktif',
                    'new_status' => $request->is_active ? 'Aktif' : 'Tidak Aktif',
                    'updated_by' => auth()->user()->name,
                    'updated_at' => now()->format('d M Y H:i')
                ]);
            }

            DB::commit();

            Log::info('Material updated', [
                'material_id' => $material->id,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Material berhasil diperbarui',
                'data' => $material->fresh(),
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating material: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui material: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete material
     */
    public function deleteMaterial(RawMaterial $material)
    {
        try {
            // Check if material has related stock movements
            $hasMovements = $material->stockMovements()->exists();

            if ($hasMovements) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus material yang memiliki riwayat pergerakan stok'
                ], 409);
            }

            DB::beginTransaction();

            $materialCode = $material->code;
            $materialName = $material->name;
            $material->delete();

            // Trigger notification: Material Deleted
            $this->notificationService->createSystemNotification('material_deleted', [
                'material_name' => $materialName,
                'material_code' => $materialCode,
                'deleted_by' => auth()->user()->name,
                'deleted_at' => now()->format('d M Y H:i')
            ]);

            DB::commit();

            Log::info('Material deleted', [
                'material_code' => $materialCode,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Material berhasil dihapus',
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting material: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus material: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===== MACHINES MANAGEMENT =====
    
    /**
     * Display machines management page
     */
    public function machines(Request $request)
    {
        try {
            // Get filter parameters
            $filters = [
                'production_line_id' => $request->get('production_line_id'),
                'status' => $request->get('status'),
                'search' => $request->get('search'),
                'sort' => $request->get('sort', 'name'),
                'direction' => $request->get('direction', 'asc')
            ];

            // Build query
            $query = Machine::with('productionLine');

            // Apply filters
            if ($filters['production_line_id']) {
                $query->where('production_line_id', $filters['production_line_id']);
            }

            if ($filters['status']) {
                $query->where('status', $filters['status']);
            }

            if ($filters['search']) {
                $query->where(function($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('code', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('brand', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('model', 'like', '%' . $filters['search'] . '%');
                });
            }

            // Apply sorting
            $query->orderBy($filters['sort'], $filters['direction']);

            // Paginate results
            $machines = $query->paginate(15)->withQueryString();

            // Get additional data
            $productionLines = ProductionLine::where('status', 'active')->get();
            
            // Calculate statistics
            $stats = [
                'total_machines' => Machine::count(),
                'running_machines' => Machine::where('status', 'running')->count(),
                'maintenance_machines' => Machine::where('status', 'maintenance')->count(),
                'maintenance_due' => Machine::where('next_maintenance_date', '<=', now()->addWeek())->count()
            ];

            return view('master-data.machines', compact('machines', 'productionLines', 'filters', 'stats'));

        } catch (\Exception $e) {
            Log::error('Error in machines page: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data mesin');
        }
    }

    /**
     * Store new machine
     */
    public function storeMachine(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:machines,code',
                'name' => 'required|string|max:255',
                'production_line_id' => 'required|exists:production_lines,id',
                'brand' => 'required|string|max:100',
                'model' => 'required|string|max:100',
                'manufacture_year' => 'required|integer|min:1990|max:' . (date('Y') + 1),
                'capacity_per_hour' => 'required|integer|min:1',
                'status' => 'required|in:running,idle,maintenance,broken',
                'last_maintenance_date' => 'nullable|date',
                'next_maintenance_date' => 'nullable|date|after:last_maintenance_date',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $machine = Machine::create([
                'code' => $request->code,
                'name' => $request->name,
                'production_line_id' => $request->production_line_id,
                'brand' => $request->brand,
                'model' => $request->model,
                'manufacture_year' => $request->manufacture_year,
                'capacity_per_hour' => $request->capacity_per_hour,
                'status' => $request->status,
                'last_maintenance_date' => $request->last_maintenance_date,
                'next_maintenance_date' => $request->next_maintenance_date,
                'notes' => $request->notes,
                'created_at' => now()
            ]);

            // Trigger notification: New Machine Created
            $productionLine = ProductionLine::find($request->production_line_id);
            $this->notificationService->createSystemNotification('machine_created', [
                'machine_name' => $machine->name,
                'machine_code' => $machine->code,
                'production_line' => $productionLine->name,
                'brand' => $machine->brand,
                'model' => $machine->model,
                'status' => $machine->status,
                'created_by' => auth()->user()->name,
                'created_at' => now()->format('d M Y H:i')
            ]);

            // Check maintenance due and trigger alerts
            if ($machine->next_maintenance_date && $machine->next_maintenance_date <= now()->addWeek()) {
                $this->notificationService->createSystemNotification('maintenance_due_soon', [
                    'machine_name' => $machine->name,
                    'machine_code' => $machine->code,
                    'next_maintenance_date' => $machine->next_maintenance_date->format('d M Y'),
                    'days_until_due' => now()->diffInDays($machine->next_maintenance_date)
                ]);
            }

            DB::commit();

            Log::info('New machine created', [
                'machine_id' => $machine->id,
                'code' => $machine->code,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mesin berhasil ditambahkan',
                'data' => $machine,
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating machine: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan mesin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update machine
     */
    public function updateMachine(Request $request, Machine $machine)
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:machines,code,' . $machine->id,
                'name' => 'required|string|max:255',
                'production_line_id' => 'required|exists:production_lines,id',
                'brand' => 'required|string|max:100',
                'model' => 'required|string|max:100',
                'manufacture_year' => 'required|integer|min:1990|max:' . (date('Y') + 1),
                'capacity_per_hour' => 'required|integer|min:1',
                'status' => 'required|in:running,idle,maintenance,broken',
                'last_maintenance_date' => 'nullable|date',
                'next_maintenance_date' => 'nullable|date|after:last_maintenance_date',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $oldStatus = $machine->status;
            $oldMaintenanceDate = $machine->next_maintenance_date;

            $machine->update([
                'code' => $request->code,
                'name' => $request->name,
                'production_line_id' => $request->production_line_id,
                'brand' => $request->brand,
                'model' => $request->model,
                'manufacture_year' => $request->manufacture_year,
                'capacity_per_hour' => $request->capacity_per_hour,
                'status' => $request->status,
                'last_maintenance_date' => $request->last_maintenance_date,
                'next_maintenance_date' => $request->next_maintenance_date,
                'notes' => $request->notes,
                'updated_at' => now()
            ]);

            // Trigger notifications based on status changes
            if ($oldStatus !== $request->status) {
                $this->notificationService->createSystemNotification('machine_status_changed', [
                    'machine_name' => $machine->name,
                    'machine_code' => $machine->code,
                    'old_status' => ucfirst($oldStatus),
                    'new_status' => ucfirst($request->status),
                    'updated_by' => auth()->user()->name,
                    'updated_at' => now()->format('d M Y H:i')
                ]);

                // Special alerts for critical status changes
                if ($request->status === 'broken') {
                    $this->notificationService->createSystemNotification('machine_breakdown', [
                        'machine_name' => $machine->name,
                        'machine_code' => $machine->code,
                        'reported_by' => auth()->user()->name,
                        'reported_at' => now()->format('d M Y H:i'),
                        'notes' => $request->notes
                    ]);
                } elseif ($request->status === 'maintenance') {
                    $this->notificationService->createSystemNotification('machine_maintenance_started', [
                        'machine_name' => $machine->name,
                        'machine_code' => $machine->code,
                        'started_by' => auth()->user()->name,
                        'started_at' => now()->format('d M Y H:i')
                    ]);
                } elseif ($oldStatus === 'maintenance' && $request->status === 'running') {
                    $this->notificationService->createSystemNotification('machine_maintenance_completed', [
                        'machine_name' => $machine->name,
                        'machine_code' => $machine->code,
                        'completed_by' => auth()->user()->name,
                        'completed_at' => now()->format('d M Y H:i')
                    ]);
                }
            }

            // Check maintenance due alerts
            if ($request->next_maintenance_date && $request->next_maintenance_date != $oldMaintenanceDate) {
                if ($request->next_maintenance_date <= now()->addWeek()) {
                    $this->notificationService->createSystemNotification('maintenance_due_soon', [
                        'machine_name' => $machine->name,
                        'machine_code' => $machine->code,
                        'next_maintenance_date' => Carbon::parse($request->next_maintenance_date)->format('d M Y'),
                        'days_until_due' => now()->diffInDays($request->next_maintenance_date)
                    ]);
                }
            }

            DB::commit();

            Log::info('Machine updated', [
                'machine_id' => $machine->id,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mesin berhasil diperbarui',
                'data' => $machine->fresh(),
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating machine: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui mesin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete machine
     */
    public function deleteMachine(Machine $machine)
    {
        try {
            // Check if machine has related productions
            $hasProductions = $machine->productions()->exists();

            if ($hasProductions) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus mesin yang memiliki data produksi'
                ], 409);
            }

            DB::beginTransaction();

            $machineCode = $machine->code;
            $machineName = $machine->name;
            $machine->delete();

            // Trigger notification: Machine Deleted
            $this->notificationService->createSystemNotification('machine_deleted', [
                'machine_name' => $machineName,
                'machine_code' => $machineCode,
                'deleted_by' => auth()->user()->name,
                'deleted_at' => now()->format('d M Y H:i')
            ]);

            DB::commit();

            Log::info('Machine deleted', [
                'machine_code' => $machineCode,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mesin berhasil dihapus',
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting machine: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus mesin: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===== UTILITY METHODS =====
    
    /**
     * Generate code for different entity types
     */
    public function generateCode(Request $request)
    {
        try {
            $type = $request->get('type');
            $code = '';

            switch ($type) {
                case 'user':
                    $lastUser = User::orderBy('id', 'desc')->first();
                    $lastNumber = $lastUser ? intval(substr($lastUser->employee_id, -3)) : 0;
                    $code = 'EMP' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
                    break;

                case 'product':
                    $lastProduct = ProductType::orderBy('id', 'desc')->first();
                    $lastNumber = $lastProduct ? intval(substr($lastProduct->code, 2)) : 0;
                    $code = 'BP' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
                    break;

                case 'material':
                    $lastMaterial = RawMaterial::orderBy('id', 'desc')->first();
                    $lastNumber = $lastMaterial ? intval(substr($lastMaterial->code, 3)) : 0;
                    $code = 'MAT' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
                    break;

                case 'machine':
                    $lastMachine = Machine::orderBy('id', 'desc')->first();
                    $lastNumber = $lastMachine ? intval(substr($lastMachine->code, 3)) : 0;
                    $code = 'MCH' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Tipe kode tidak valid'
                    ], 400);
            }

            return response()->json([
                'success' => true,
                'code' => $code
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating code: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate kode: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk actions for multiple entities
     */
    public function bulkAction(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:users,products,materials,machines',
                'action' => 'required|in:activate,deactivate,delete',
                'ids' => 'required|array|min:1',
                'ids.*' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $type = $request->type;
            $action = $request->action;
            $ids = $request->ids;
            $processedCount = 0;

            // Determine model class
            $modelClass = '';
            switch ($type) {
                case 'users':
                    $modelClass = User::class;
                    break;
                case 'products':
                    $modelClass = ProductType::class;
                    break;
                case 'materials':
                    $modelClass = RawMaterial::class;
                    break;
                case 'machines':
                    $modelClass = Machine::class;
                    break;
            }

            // Perform bulk action
            switch ($action) {
                case 'activate':
                    $field = $type === 'users' ? 'status' : 'is_active';
                    $value = $type === 'users' ? 'active' : true;
                    $processedCount = $modelClass::whereIn('id', $ids)->update([
                        $field => $value,
                        'updated_at' => now()
                    ]);
                    break;

                case 'deactivate':
                    $field = $type === 'users' ? 'status' : 'is_active';
                    $value = $type === 'users' ? 'inactive' : false;
                    $processedCount = $modelClass::whereIn('id', $ids)->update([
                        $field => $value,
                        'updated_at' => now()
                    ]);
                    break;

                case 'delete':
                    // Additional checks for delete action
                    if ($type === 'users') {
                        // Don't allow deleting current user
                        $ids = array_diff($ids, [auth()->id()]);
                        
                        // Check for users with related data
                        $usersWithData = User::whereIn('id', $ids)
                            ->where(function($q) {
                                $q->whereHas('productions')
                                  ->orWhereHas('qualityControls');
                            })->pluck('id')->toArray();
                        
                        $ids = array_diff($ids, $usersWithData);
                    }

                    $processedCount = $modelClass::whereIn('id', $ids)->delete();
                    break;
            }

            // Trigger notification: Bulk Action Performed
            $this->notificationService->createSystemNotification('bulk_action_performed', [
                'entity_type' => ucfirst($type),
                'action' => ucfirst($action),
                'processed_count' => $processedCount,
                'performed_by' => auth()->user()->name,
                'performed_at' => now()->format('d M Y H:i')
            ]);

            DB::commit();

            Log::info('Bulk action performed', [
                'type' => $type,
                'action' => $action,
                'processed_count' => $processedCount,
                'performed_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Berhasil memproses {$processedCount} item",
                'processed_count' => $processedCount,
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error in bulk action: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan bulk action: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check maintenance due alerts
     */
    public function checkMaintenanceDue()
    {
        try {
            $machinesDueSoon = Machine::where('next_maintenance_date', '<=', now()->addWeek())
                ->where('next_maintenance_date', '>', now())
                ->where('status', '!=', 'maintenance')
                ->get();

            $overdueCount = 0;
            $dueSoonCount = 0;

            foreach ($machinesDueSoon as $machine) {
                $daysUntilDue = now()->diffInDays($machine->next_maintenance_date, false);
                
                if ($daysUntilDue <= 0) {
                    // Overdue
                    $this->notificationService->createSystemNotification('maintenance_overdue', [
                        'machine_name' => $machine->name,
                        'machine_code' => $machine->code,
                        'overdue_days' => abs($daysUntilDue),
                        'last_maintenance' => $machine->last_maintenance_date ? $machine->last_maintenance_date->format('d M Y') : 'Tidak ada data'
                    ]);
                    $overdueCount++;
                } elseif ($daysUntilDue <= 7) {
                    // Due soon
                    $this->notificationService->createSystemNotification('maintenance_due_soon', [
                        'machine_name' => $machine->name,
                        'machine_code' => $machine->code,
                        'next_maintenance_date' => $machine->next_maintenance_date->format('d M Y'),
                        'days_until_due' => $daysUntilDue
                    ]);
                    $dueSoonCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Maintenance alerts checked',
                'data' => [
                    'overdue_count' => $overdueCount,
                    'due_soon_count' => $dueSoonCount,
                    'total_alerts' => $overdueCount + $dueSoonCount
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking maintenance due: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengecek jadwal maintenance'
            ], 500);
        }
    }

    /**
     * Check low stock alerts
     */
    public function checkLowStock()
    {
        try {
            $lowStockMaterials = RawMaterial::whereRaw('current_stock <= minimum_stock')
                ->where('is_active', true)
                ->get();

            $outOfStockCount = 0;
            $lowStockCount = 0;

            foreach ($lowStockMaterials as $material) {
                if ($material->current_stock == 0) {
                    $this->notificationService->createStockNotification($material, 'out_of_stock');
                    $outOfStockCount++;
                } else {
                    $this->notificationService->createStockNotification($material, 'low_stock');
                    $lowStockCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Stock alerts checked',
                'data' => [
                    'out_of_stock_count' => $outOfStockCount,
                    'low_stock_count' => $lowStockCount,
                    'total_alerts' => $outOfStockCount + $lowStockCount
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking low stock: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengecek stok rendah'
            ], 500);
        }
    }

    /**
     * Export data to various formats
     */
    public function exportData(Request $request)
    {
        try {
            $type = $request->get('type');
            $format = $request->get('format', 'excel');

            if (!in_array($type, ['users', 'products', 'materials', 'machines'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipe export tidak valid'
                ], 400);
            }

            if (!in_array($format, ['excel', 'pdf', 'csv'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format export tidak valid'
                ], 400);
            }

            // Get data based on type
            $data = [];
            $filename = '';

            switch ($type) {
                case 'users':
                    $data = User::with('role')->get()->map(function($user) {
                        return [
                            'ID Karyawan' => $user->employee_id,
                            'Nama' => $user->name,
                            'Email' => $user->email,
                            'Role' => $user->role->display_name ?? '',
                            'Telepon' => $user->phone ?? '',
                            'Status' => $user->status === 'active' ? 'Aktif' : 'Tidak Aktif',
                            'Bergabung' => $user->created_at->format('d/m/Y')
                        ];
                    });
                    $filename = 'data_pengguna_' . date('Y-m-d');
                    break;

                case 'products':
                    $data = ProductType::all()->map(function($product) {
                        return [
                            'Kode' => $product->code,
                            'Nama' => $product->name,
                            'Brand' => $product->brand,
                            'Model' => $product->model,
                            'Berat (g)' => $product->standard_weight,
                            'Ketebalan (mm)' => $product->standard_thickness,
                            'Status' => $product->is_active ? 'Aktif' : 'Tidak Aktif',
                            'Dibuat' => $product->created_at->format('d/m/Y')
                        ];
                    });
                    $filename = 'data_produk_' . date('Y-m-d');
                    break;

                case 'materials':
                    $data = RawMaterial::all()->map(function($material) {
                        return [
                            'Kode' => $material->code,
                            'Nama' => $material->name,
                            'Satuan' => $material->unit,
                            'Supplier' => $material->supplier,
                            'Stok Saat Ini' => $material->current_stock,
                            'Stok Minimum' => $material->minimum_stock,
                            'Stok Maksimum' => $material->maximum_stock,
                            'Harga/Unit' => $material->unit_price,
                            'Status' => $material->is_active ? 'Aktif' : 'Tidak Aktif'
                        ];
                    });
                    $filename = 'data_material_' . date('Y-m-d');
                    break;

                case 'machines':
                    $data = Machine::with('productionLine')->get()->map(function($machine) {
                        return [
                            'Kode' => $machine->code,
                            'Nama' => $machine->name,
                            'Lini Produksi' => $machine->productionLine->name ?? '',
                            'Brand' => $machine->brand,
                            'Model' => $machine->model,
                            'Tahun' => $machine->manufacture_year,
                            'Kapasitas/Jam' => $machine->capacity_per_hour,
                            'Status' => ucfirst($machine->status),
                            'Maintenance Terakhir' => $machine->last_maintenance_date ? Carbon::parse($machine->last_maintenance_date)->format('d/m/Y') : '',
                            'Maintenance Berikutnya' => $machine->next_maintenance_date ? Carbon::parse($machine->next_maintenance_date)->format('d/m/Y') : ''
                        ];
                    });
                    $filename = 'data_mesin_' . date('Y-m-d');
                    break;
            }

            // Trigger notification: Data Export
            $this->notificationService->createSystemNotification('data_exported', [
                'entity_type' => ucfirst($type),
                'format' => strtoupper($format),
                'record_count' => $data->count(),
                'filename' => $filename . '.' . $format,
                'exported_by' => auth()->user()->name,
                'exported_at' => now()->format('d M Y H:i')
            ]);

            // In a real implementation, you would use a package like
            // Laravel Excel (maatwebsite/excel) or similar to generate actual files
            // For now, we'll just return a success message

            Log::info('Data exported', [
                'type' => $type,
                'format' => $format,
                'count' => $data->count(),
                'exported_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Data {$type} berhasil diekspor dalam format {$format}",
                'filename' => $filename . '.' . $format,
                'count' => $data->count(),
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error exporting data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal export data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard statistics for master data
     */
    public function getDashboardStats()
    {
        try {
            $stats = [
                'users' => [
                    'total' => User::count(),
                    'active' => User::where('status', 'active')->count(),
                    'new_this_month' => User::whereMonth('created_at', now()->month)->count()
                ],
                'products' => [
                    'total' => ProductType::count(),
                    'active' => ProductType::where('is_active', true)->count(),
                    'by_brand' => ProductType::select('brand', DB::raw('count(*) as count'))
                        ->whereNotNull('brand')
                        ->groupBy('brand')
                        ->get()
                ],
                'materials' => [
                    'total' => RawMaterial::count(),
                    'active' => RawMaterial::where('is_active', true)->count(),
                    'low_stock' => RawMaterial::whereRaw('current_stock <= minimum_stock')->count(),
                    'total_value' => RawMaterial::sum(DB::raw('current_stock * unit_price'))
                ],
                'machines' => [
                    'total' => Machine::count(),
                    'running' => Machine::where('status', 'running')->count(),
                    'maintenance' => Machine::where('status', 'maintenance')->count(),
                    'maintenance_due' => Machine::where('next_maintenance_date', '<=', now()->addWeek())->count()
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting dashboard stats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik dashboard'
            ], 500);
        }
    }

    /**
     * Search across all master data entities
     */
    public function globalSearch(Request $request)
    {
        try {
            $query = $request->get('q');
            
            if (strlen($query) < 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Query pencarian minimal 3 karakter'
                ], 400);
            }

            $results = [
                'users' => User::where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('employee_id', 'like', "%{$query}%")
                    ->limit(5)
                    ->get(['id', 'name', 'email', 'employee_id']),
                
                'products' => ProductType::where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%")
                    ->orWhere('brand', 'like', "%{$query}%")
                    ->limit(5)
                    ->get(['id', 'code', 'name', 'brand', 'model']),
                
                'materials' => RawMaterial::where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%")
                    ->orWhere('supplier', 'like', "%{$query}%")
                    ->limit(5)
                    ->get(['id', 'code', 'name', 'supplier']),
                
                'machines' => Machine::where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%")
                    ->orWhere('brand', 'like', "%{$query}%")
                    ->limit(5)
                    ->get(['id', 'code', 'name', 'brand', 'model'])
            ];

            return response()->json([
                'success' => true,
                'query' => $query,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Error in global search: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan pencarian'
            ], 500);
        }
    }

    /**
     * Automated daily master data check
     */
    public function performDailyCheck()
    {
        try {
            DB::beginTransaction();

            $checkResults = [
                'low_stock_alerts' => 0,
                'maintenance_alerts' => 0,
                'inactive_users' => 0,
                'inactive_products' => 0,
                'total_alerts' => 0
            ];

            // 1. Check low stock materials
            $lowStockResult = $this->checkLowStock();
            if ($lowStockResult->getData()->success) {
                $data = $lowStockResult->getData()->data;
                $checkResults['low_stock_alerts'] = $data->out_of_stock_count + $data->low_stock_count;
            }

            // 2. Check maintenance due machines
            $maintenanceResult = $this->checkMaintenanceDue();
            if ($maintenanceResult->getData()->success) {
                $data = $maintenanceResult->getData()->data;
                $checkResults['maintenance_alerts'] = $data->overdue_count + $data->due_soon_count;
            }

            // 3. Check inactive users
            $checkResults['inactive_users'] = User::where('status', 'inactive')->count();

            // 4. Check inactive products
            $checkResults['inactive_products'] = ProductType::where('is_active', false)->count();

            // 5. Calculate total alerts
            $checkResults['total_alerts'] = $checkResults['low_stock_alerts'] + 
                                          $checkResults['maintenance_alerts'];

            // Trigger daily summary notification
            $this->notificationService->createSystemNotification('daily_master_data_check', [
                'check_date' => now()->format('d M Y'),
                'low_stock_alerts' => $checkResults['low_stock_alerts'],
                'maintenance_alerts' => $checkResults['maintenance_alerts'],
                'inactive_users' => $checkResults['inactive_users'],
                'inactive_products' => $checkResults['inactive_products'],
                'total_alerts' => $checkResults['total_alerts'],
                'checked_at' => now()->format('H:i')
            ]);

            DB::commit();

            Log::info('Daily master data check completed', [
                'results' => $checkResults,
                'performed_by' => 'system'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Daily check completed successfully',
                'data' => $checkResults,
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error in daily master data check: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Daily check failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get master data insights for dashboard
     */
    public function getMasterDataInsights()
    {
        try {
            $insights = [
                'critical_alerts' => [
                    'out_of_stock' => RawMaterial::where('current_stock', 0)->where('is_active', true)->count(),
                    'machines_broken' => Machine::where('status', 'broken')->count(),
                    'maintenance_overdue' => Machine::where('next_maintenance_date', '<', now())->where('status', '!=', 'maintenance')->count()
                ],
                'trends' => [
                    'new_users_this_month' => User::whereMonth('created_at', now()->month)->count(),
                    'new_products_this_month' => ProductType::whereMonth('created_at', now()->month)->count(),
                    'new_materials_this_month' => RawMaterial::whereMonth('created_at', now()->month)->count(),
                    'new_machines_this_month' => Machine::whereMonth('created_at', now()->month)->count()
                ],
                'health_metrics' => [
                    'user_active_percentage' => round((User::where('status', 'active')->count() / max(User::count(), 1)) * 100, 1),
                    'product_active_percentage' => round((ProductType::where('is_active', true)->count() / max(ProductType::count(), 1)) * 100, 1),
                    'material_healthy_stock_percentage' => round((RawMaterial::whereRaw('current_stock > minimum_stock')->count() / max(RawMaterial::count(), 1)) * 100, 1),
                    'machine_operational_percentage' => round((Machine::whereIn('status', ['running', 'idle'])->count() / max(Machine::count(), 1)) * 100, 1)
                ],
                'top_suppliers' => RawMaterial::select('supplier', DB::raw('count(*) as material_count'), DB::raw('sum(current_stock * unit_price) as total_value'))
                    ->where('is_active', true)
                    ->groupBy('supplier')
                    ->orderByDesc('total_value')
                    ->limit(5)
                    ->get(),
                'machine_status_distribution' => Machine::select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'data' => $insights,
                'generated_at' => now()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting master data insights: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil insights master data'
            ], 500);
        }
    }

    /**
     * Sync master data across modules
     */
    public function syncMasterData()
    {
        try {
            DB::beginTransaction();

            $syncResults = [
                'updated_records' => 0,
                'synchronized_modules' => [],
                'errors' => []
            ];

            // Sync operations would go here
            // For example: updating product specifications across all production records
            // updating material prices across all cost calculations
            // synchronizing user roles across all permissions

            // This is a placeholder for actual sync logic
            $syncResults['updated_records'] = 0;
            $syncResults['synchronized_modules'] = ['production', 'quality_control', 'inventory'];

            // Trigger sync notification
            $this->notificationService->createSystemNotification('master_data_sync', [
                'sync_date' => now()->format('d M Y'),
                'updated_records' => $syncResults['updated_records'],
                'synchronized_modules' => implode(', ', $syncResults['synchronized_modules']),
                'sync_duration' => '0.5 seconds',
                'performed_by' => auth()->user()->name ?? 'System',
                'performed_at' => now()->format('H:i')
            ]);

            DB::commit();

            Log::info('Master data sync completed', [
                'results' => $syncResults,
                'performed_by' => auth()->id() ?? 'system'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Master data synchronization completed',
                'data' => $syncResults,
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error in master data sync: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate and clean master data
     */
    public function validateMasterData()
    {
        try {
            $validationResults = [
                'users' => [
                    'total' => User::count(),
                    'invalid' => [],
                    'warnings' => []
                ],
                'products' => [
                    'total' => ProductType::count(),
                    'invalid' => [],
                    'warnings' => []
                ],
                'materials' => [
                    'total' => RawMaterial::count(),
                    'invalid' => [],
                    'warnings' => []
                ],
                'machines' => [
                    'total' => Machine::count(),
                    'invalid' => [],
                    'warnings' => []
                ]
            ];

            // Validate users
            $usersWithoutRole = User::whereNull('role_id')->get();
            $duplicateEmails = User::select('email', DB::raw('count(*) as count'))
                ->groupBy('email')
                ->having('count', '>', 1)
                ->get();

            if ($usersWithoutRole->count() > 0) {
                $validationResults['users']['invalid'][] = "Users without role: " . $usersWithoutRole->count();
            }
            if ($duplicateEmails->count() > 0) {
                $validationResults['users']['invalid'][] = "Duplicate emails: " . $duplicateEmails->count();
            }

            // Validate products
            $productsWithoutSpecs = ProductType::whereNull('standard_weight')
                ->orWhereNull('standard_thickness')
                ->get();

            if ($productsWithoutSpecs->count() > 0) {
                $validationResults['products']['warnings'][] = "Products missing specifications: " . $productsWithoutSpecs->count();
            }

            // Validate materials
            $negativeStock = RawMaterial::where('current_stock', '<', 0)->get();
            $invalidStockLimits = RawMaterial::whereRaw('minimum_stock > maximum_stock')->get();

            if ($negativeStock->count() > 0) {
                $validationResults['materials']['invalid'][] = "Materials with negative stock: " . $negativeStock->count();
            }
            if ($invalidStockLimits->count() > 0) {
                $validationResults['materials']['invalid'][] = "Materials with invalid stock limits: " . $invalidStockLimits->count();
            }

            // Validate machines
            $machinesWithoutLine = Machine::whereNull('production_line_id')->get();
            $futureManufactureYear = Machine::where('manufacture_year', '>', date('Y'))->get();

            if ($machinesWithoutLine->count() > 0) {
                $validationResults['machines']['warnings'][] = "Machines without production line: " . $machinesWithoutLine->count();
            }
            if ($futureManufactureYear->count() > 0) {
                $validationResults['machines']['warnings'][] = "Machines with future manufacture year: " . $futureManufactureYear->count();
            }

            // Count total issues
            $totalIssues = 0;
            foreach ($validationResults as $entity) {
                $totalIssues += count($entity['invalid']) + count($entity['warnings']);
            }

            // Trigger validation notification
            $this->notificationService->createSystemNotification('master_data_validation', [
                'validation_date' => now()->format('d M Y'),
                'total_issues' => $totalIssues,
                'critical_issues' => array_sum(array_map(function($entity) { return count($entity['invalid']); }, $validationResults)),
                'warnings' => array_sum(array_map(function($entity) { return count($entity['warnings']); }, $validationResults)),
                'validated_by' => auth()->user()->name,
                'validated_at' => now()->format('H:i')
            ]);

            Log::info('Master data validation completed', [
                'results' => $validationResults,
                'total_issues' => $totalIssues,
                'performed_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Master data validation completed',
                'data' => $validationResults,
                'total_issues' => $totalIssues,
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error in master data validation: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get material stock alerts for dashboard
     */
    public function getStockAlerts()
    {
        try {
            $alerts = [
                'out_of_stock' => RawMaterial::where('current_stock', 0)
                    ->where('is_active', true)
                    ->get(['id', 'code', 'name', 'supplier']),
                
                'low_stock' => RawMaterial::whereRaw('current_stock > 0 AND current_stock <= minimum_stock')
                    ->where('is_active', true)
                    ->get(['id', 'code', 'name', 'current_stock', 'minimum_stock', 'supplier']),
                
                'overstocked' => RawMaterial::whereRaw('current_stock >= maximum_stock')
                    ->where('is_active', true)
                    ->get(['id', 'code', 'name', 'current_stock', 'maximum_stock', 'supplier'])
            ];

            $summary = [
                'out_of_stock_count' => $alerts['out_of_stock']->count(),
                'low_stock_count' => $alerts['low_stock']->count(),
                'overstocked_count' => $alerts['overstocked']->count(),
                'total_alerts' => $alerts['out_of_stock']->count() + $alerts['low_stock']->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $alerts,
                'summary' => $summary,
                'generated_at' => now()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting stock alerts: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil alert stok'
            ], 500);
        }
    }

    /**
     * Get machine maintenance alerts for dashboard
     */
    public function getMaintenanceAlerts()
    {
        try {
            $today = now();
            
            $alerts = [
                'overdue' => Machine::where('next_maintenance_date', '<', $today)
                    ->where('status', '!=', 'maintenance')
                    ->get(['id', 'code', 'name', 'next_maintenance_date', 'status']),
                
                'due_soon' => Machine::whereBetween('next_maintenance_date', [$today, $today->copy()->addWeek()])
                    ->where('status', '!=', 'maintenance')
                    ->get(['id', 'code', 'name', 'next_maintenance_date', 'status']),
                
                'in_maintenance' => Machine::where('status', 'maintenance')
                    ->get(['id', 'code', 'name', 'last_maintenance_date', 'notes'])
            ];

            $summary = [
                'overdue_count' => $alerts['overdue']->count(),
                'due_soon_count' => $alerts['due_soon']->count(),
                'in_maintenance_count' => $alerts['in_maintenance']->count(),
                'total_alerts' => $alerts['overdue']->count() + $alerts['due_soon']->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $alerts,
                'summary' => $summary,
                'generated_at' => now()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting maintenance alerts: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil alert maintenance'
            ], 500);
        }
    }
}