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
use Carbon\Carbon;

class MasterDataController extends Controller
{
    // Tidak perlu constructor karena middleware diterapkan di routes

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

            DB::commit();

            Log::info('New user created', [
                'user_id' => $user->id,
                'employee_id' => $user->employee_id,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengguna berhasil ditambahkan',
                'data' => $user
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

            DB::commit();

            Log::info('User updated', [
                'user_id' => $user->id,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengguna berhasil diperbarui',
                'data' => $user->fresh()
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
            $user->delete();

            DB::commit();

            Log::info('User deleted', [
                'employee_id' => $employeeId,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengguna berhasil dihapus'
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

            DB::commit();

            Log::info('New product created', [
                'product_id' => $product->id,
                'code' => $product->code,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan',
                'data' => $product
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

            DB::commit();

            Log::info('Product updated', [
                'product_id' => $product->id,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil diperbarui',
                'data' => $product->fresh()
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
            $product->delete();

            DB::commit();

            Log::info('Product deleted', [
                'product_code' => $productCode,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil dihapus'
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

            DB::commit();

            Log::info('New material created', [
                'material_id' => $material->id,
                'code' => $material->code,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Material berhasil ditambahkan',
                'data' => $material
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

            DB::commit();

            Log::info('Material updated', [
                'material_id' => $material->id,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Material berhasil diperbarui',
                'data' => $material->fresh()
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
            $material->delete();

            DB::commit();

            Log::info('Material deleted', [
                'material_code' => $materialCode,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Material berhasil dihapus'
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

            DB::commit();

            Log::info('New machine created', [
                'machine_id' => $machine->id,
                'code' => $machine->code,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mesin berhasil ditambahkan',
                'data' => $machine
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

            DB::commit();

            Log::info('Machine updated', [
                'machine_id' => $machine->id,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mesin berhasil diperbarui',
                'data' => $machine->fresh()
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
            $machine->delete();

            DB::commit();

            Log::info('Machine deleted', [
                'machine_code' => $machineCode,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mesin berhasil dihapus'
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
                'processed_count' => $processedCount
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
                'count' => $data->count()
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
}