<?php

use App\Http\Controllers\Auth\{
    ConfirmablePasswordController,
    EmailVerificationNotificationController,
    EmailVerificationPromptController,
    LoginController,
    NewPasswordController,
    PasswordController,
    PasswordResetLinkController,
    RegisterController,
    VerifyEmailController
};
use App\Http\Controllers\CartController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupplierController;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'renderHomePage'])->name('home');
Route::get('/unsupported-browser', fn() => view('unsupported-browser'));
Route::get('/keranjang', [CartController::class, 'index'])->name('keranjang');
Route::post('/cart/add', [CartController::class, 'addToCart'])->name('keranjang.add');
Route::get('/checkout', [CartController::class, 'Checkout'])->name('confirm_checkout');
Route::post('/checkout', [OrderController::class, 'Checkout'])->name('checkout');
Route::get('/berhasil', fn() => Inertia::render('Berhasil'))->name('berhasil');
Route::get('/detail-transaksi/{order:transaction_code}', [OrderController::class, 'viewOrder'])->name('detailTransaksi');
Route::get('/pusat-bantuan', fn() => Inertia::render('PusatBantuan'))->name('pusatBantuan');
Route::post('/upload-bukti/{order}', [OrderController::class, 'uploadBukti']);
Route::get('/transaksi', [OrderController::class, 'index'])->name('transaksi');
Route::get('/underconstruction', fn() => Inertia::render('UnderConstruction'));
Route::patch('/cart/{cart}', [CartController::class, 'update'])->name('keranjang.update');
Route::delete('/cart/{cart}', [CartController::class, 'destroy'])->name('keranjang.destroy');
/*
|--------------------------------------------------------------------------
| Guest Only Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', fn() => Inertia::render('auth/Login'))->name('login');
    Route::get('/daftar', fn() => Inertia::render('auth/Daftar'))->name('daftar');

    // Auth Controllers
    // Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);

    // Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);

    // Forgot password
    // Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    // Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    // Reset password
    // Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    // Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/akun', fn() => Inertia::render('Akun'))->name('akun');
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/pengaturan-akun', fn() => Inertia::render('PengaturanAkun'))->name('pengaturan-akun');
    Route::get('/admin/dashboard', [DashboardController::class, 'render_home'])->middleware('role:admin')->name('dashboard');
    Route::get('/admin/menu', [DashboardController::class, 'render_menu'])->middleware('role:admin')->name('dashboard.menu');
    Route::get('/admin/supplier', [DashboardController::class, 'render_supplier'])->middleware('role:admin')->name('dashboard.supplier');
    Route::get('/admin/pengguna', [DashboardController::class, 'render_users'])->middleware('role:admin')->name('dashboard.pengguna');

    Route::get('/admin/pengaturan', fn() => Inertia::render('admin/Pengaturan'))->middleware('role:admin');
    Route::get('/admin/laporan', function (Request $request) {
        $query = Order::query()->with('user');

        if ($request->has('period')) {
            switch ($request->period) {
                case 'today':
                    $query->whereDate('created_at', Carbon::today());
                    break;

                case 'this_month':
                    $query->whereMonth('created_at', Carbon::now()->month)
                        ->whereYear('created_at', Carbon::now()->year);
                    break;

                case 'custom':
                    if ($request->filled(['start', 'end'])) {
                        $query->whereBetween('created_at', [
                            Carbon::parse($request->start)->startOfDay(),
                            Carbon::parse($request->end)->endOfDay(),
                        ]);
                    }
                    break;
            }
        }

        $orders = $query->get();

        return Inertia::render('admin/Laporan', [
            'orders' => $orders,
            'filters' => $request->only(['period', 'start', 'end']),
        ]);
    })->middleware('role:admin');



    Route::post('/user', function () {
        $validatedData = request()->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'whatsapp_number' => 'nullable|string|max:15',
            'password' => 'required|string',
            'role' => 'required|string|in:admin,kasir,staff,customer',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'required|integer',
        ]);

        // Handle image upload if provided
        if (request()->hasFile('image')) {
            $validatedData['image'] = request()->file('image')->store('user_images', 'public');
        }

        // Create the user
        \App\Models\User::create($validatedData);

        return redirect()->back()->with('success', 'Mantap! Pengguna berhasil ditambahkan');
    });

    Route::patch('/user/{user}', function (\App\Models\User $user) {
        $validatedData = request()->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'whatsapp_number' => 'nullable|string|max:15',
            'password' => 'nullable|string',
            'role' => 'required|string|in:admin,kasir,staff,customer',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'required|integer',
        ]);

        // Handle image upload if provided
        if (request()->hasFile('image')) {
            $validatedData['image'] = request()->file('image')->store('user_images', 'public');

            // Delete the old image if it exists
            if ($user->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->image);
            }
        }

        // Update the user
        if (!empty($validatedData['password'])) {
            $validatedData['password'] = bcrypt($validatedData['password']);
        } else {
            unset($validatedData['password']); // Ignore password if not provided
        }

        $user->update($validatedData);

        return redirect()->back()->with('success', 'Mantap! Pengguna berhasil diperbarui');
    });

    Route::delete('/user/{user}', function (\App\Models\User $user) {
        if ($user->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->image);
        }
        $user->delete();
        return redirect()->back()->with('success', 'Pengguna berhasil dihapus');
    });

    Route::put('/profil/', function () {
        $user = auth()->user();
        $validatedData = request()->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'whatsapp_number' => 'nullable|string|max:15',
            'password' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'required|integer',
        ]);

        // Handle image upload if provided
        if (request()->hasFile('image')) {
            $validatedData['image'] = request()->file('image')->store('user_images', 'public');

            // Delete the old image if it exists
            if ($user->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->image);
            }
        }

        // Update the user
        if (!empty($validatedData['password'])) {
            $validatedData['password'] = bcrypt($validatedData['password']);
        } else {
            unset($validatedData['password']); // Ignore password if not provided
        }

        $user->update($validatedData);

        return redirect()->back()->with('success', 'Mantap! Pengguna berhasil diperbarui');
    });
    // Kasir
    Route::get('/kasir/dashboard', [DashboardController::class, 'render_kasir_dashboard'])->name('kasir.dashboard')->middleware('role:kasir,admin');
    Route::get('/kasir/pesanan', [DashboardController::class, 'render_kasir_orders'])->name('kasir.pesanan')->middleware('role:kasir,admin');
    Route::get('/kasir/riwayat', [DashboardController::class, 'render_kasir_history'])->name('kasir.riwayat')->middleware('role:kasir,admin');
    Route::get('/kasir/pengaturan', fn() => Inertia::render('kasir/Pengaturan'))->middleware('role:kasir,admin');
    Route::get('/kasir/berhasil', fn() => Inertia::render('kasir/Berhasil'))->middleware('role:kasir,admin');
    Route::post('/kasir/cart/add', [CartController::class, 'kasir_add_to_cart'])->name('kasir.keranjang.add')->middleware('role:kasir,admin');
    Route::patch('/pesanan/{order}', [OrderController::class, 'updateOrderStatus'])->name('kasir.update_order_status')->middleware('role:kasir,admin');


    // Pelayan
    Route::get('/pelayan/dashboard', [DashboardController::class, 'render_menu'])->middleware('role:staff,admin');
    Route::get('/pelayan/supplier', [DashboardController::class, 'render_supplier'])->middleware('role:staff,admin');
    Route::get('/pelayan/pengaturan', fn() => Inertia::render('pelayan/Pengaturan'))->middleware('role:staff,admin');
    Route::get('/pelayan/pesanan', function (\Illuminate\Http\Request $request) {
        $search = $request->input('search');
        $ordersQuery = Order::query()->with(['items.item', 'payment']);
        if ($search) {
            $ordersQuery->where('invoice_number', 'like', '%' . $search . '%');
        }
        $orders = $ordersQuery->latest()->get();
        return Inertia::render('pelayan/Pesanan', [
            'orders' => $orders,
            'filters' => [
                'search' => $search,
            ],
        ]);
    })->middleware('role:staff,admin');


    // Email Verification
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Password Confirmation and Update
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password/update', [PasswordController::class, 'update'])->name('password.update');

    // Logout
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    // Item
    Route::post('item/toggle/{item}', [ItemController::class, 'toggleActiveState'])->name('toggle.item')->middleware('role:admin,staff');
    Route::put('item/update/{item}', [ItemController::class, 'update'])->name('update.item')->middleware('role:admin,staff');
    Route::post('item/store', [ItemController::class, 'store'])->name('store.item')->middleware('role:admin,staff');
    Route::delete('item/delete/{item}', [ItemController::class, 'destroy'])->name('delete.item')->middleware('role:admin,staff');
    // Supplier
    Route::post('supplier/store', [SupplierController::class, 'store'])->name('store.supplier')->middleware('role:admin,staff');
    Route::put('supplier/update/{supplier}', [SupplierController::class, 'update'])->name('update.supplier')->middleware('role:admin,staff');
    Route::delete('supplier/delete/{supplier}', [SupplierController::class, 'destroy'])->name('delete.supplier')->middleware('role:admin,staff');
});
