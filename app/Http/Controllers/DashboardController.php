<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function render_home()
    {
        $activeItemCount = Item::where('stock', '>', 0)->count();
        $orderCount = Order::count();

        $orders = Order::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->pluck('count', 'month');

        $monthlyCounts = collect(range(1, 12))->map(function ($month) use ($orders) {
            return $orders->get($month, 0);
        });
        $supplierCount = Supplier::count();
        $customerCount = User::where('role', 'customer')->count();
        $todayIncome = Order::whereDate('created_at', now())
            ->whereIn('status', ['paid', 'done'])
            ->with('items')
            ->get()
            ->flatMap(function ($order) {
                return $order->items;
            })
            ->sum(function ($orderItem) {
                return $orderItem->quantity * $orderItem->price;
            });
        $profit = Order::whereDate('created_at', now())
            ->whereIn('status', ['paid', 'done'])
            ->with('items.item')
            ->get()
            ->flatMap(function ($order) {
                return $order->items;
            })
            ->sum(function ($orderItem) {
                return ($orderItem->price - $orderItem->supplier_price) * $orderItem->quantity;
            });
        return Inertia::render('admin/Home', [
            'stats' => [
                'items' => $activeItemCount,
                'orders' => $orderCount,
                'monthlyCounts' => $monthlyCounts,
                'supplier' => $supplierCount,
                'customer' => $customerCount,
                'income' => $todayIncome,
                'profit' => $profit,
            ],
        ]);
    }
    public function render_menu(Request $request)
    {
        $search = $request->input('search');

        $itemsQuery = Item::query();

        if ($search) {
            $itemsQuery->where('name', 'like', '%' . $search . '%');
        }

        $items = $itemsQuery->latest()->get();

        return Inertia::render('admin/Menu', [
            'items' => $items,
            'filters' => [
                'search' => $search,
            ],
            'suppliers' => Supplier::all()
        ]);
    }
    public function render_report_page(Request $request)
    {
        // === Tentukan rentang tanggal berdasar period ===
        $start = null;
        $end   = null;
        $bucket = null; // 'hour' atau 'day' (untuk chart)

        if ($request->filled('period')) {
            switch ($request->period) {
                case 'today':
                    $start  = Carbon::now()->startOfDay();
                    $end    = Carbon::now()->endOfDay();
                    $bucket = 'hour';
                    break;

                case 'this_month':
                    $start  = Carbon::now()->startOfMonth();
                    $end    = Carbon::now()->endOfMonth();
                    $bucket = 'day';
                    break;

                case 'custom':
                    if ($request->filled(['start', 'end'])) {
                        $start = Carbon::parse($request->start)->startOfDay();
                        $end   = Carbon::parse($request->end)->endOfDay();
                        // kalau start=end → tampilkan per jam, selain itu per hari
                        $bucket = $start->isSameDay($end) ? 'hour' : 'day';
                    }
                    break;
            }
        }

        // === Query utama untuk tabel ===
        $query = Order::query()->with('user');
        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }
        $orders = $query->get();

        // === Stats global yang tidak terkait period (tetap) ===
        $activeItemCount = Item::where('stock', '>', 0)->count();
        $supplierCount   = Supplier::count();
        $customerCount   = User::where('role', 'customer')->count();

        // === Stats yang mengikuti period: orders count, income, profit ===
        $ordersInPeriod = Order::when($start && $end, fn($q) => $q->whereBetween('created_at', [$start, $end]))->count();

        // Ambil item order dalam period yang statusnya selesai/dibayar
        $orderItems = Order::when($start && $end, fn($q) => $q->whereBetween('created_at', [$start, $end]))
            ->whereIn('status', ['paid', 'done'])
            ->with(['items:id,order_id,price,quantity,supplier_price'])
            ->get()
            ->flatMap->items;

        $income = $orderItems->sum(fn($i) => (float) $i->price * (int) $i->quantity);
        $profit = $orderItems->sum(fn($i) => ((float) $i->price - (float) ($i->supplier_price ?? 0)) * (int) $i->quantity);

        // === Chart dinamis mengikuti period ===
        $chartLabels = [];
        $chartSeries = [];

        if ($start && $end) {
            if ($bucket === 'hour') {
                // per jam (00-23)
                $rows = Order::selectRaw('HOUR(created_at) as h, COUNT(*) as c')
                    ->whereBetween('created_at', [$start, $end])
                    ->groupBy('h')
                    ->pluck('c', 'h');

                for ($h = 0; $h < 24; $h++) {
                    $chartLabels[] = sprintf('%02d:00', $h);
                    $chartSeries[] = (int) ($rows[$h] ?? 0);
                }
            } else {
                // per hari pada rentang
                $rows = Order::selectRaw('DATE(created_at) as d, COUNT(*) as c')
                    ->whereBetween('created_at', [$start, $end])
                    ->groupBy('d')
                    ->pluck('c', 'd'); // key: 'Y-m-d'

                $periodDays = CarbonPeriod::create($start->copy()->startOfDay(), $end->copy()->startOfDay());
                foreach ($periodDays as $d) {
                    $key = $d->format('Y-m-d');
                    $chartLabels[] = $d->format('d M');
                    $chartSeries[] = (int) ($rows[$key] ?? 0);
                }
            }
        } else {
            // fallback: setahun per bulan (jika belum pilih period)
            $rows = Order::selectRaw('MONTH(created_at) as m, COUNT(*) as c')
                ->whereYear('created_at', now()->year)
                ->groupBy('m')
                ->pluck('c', 'm');

            for ($m = 1; $m <= 12; $m++) {
                $chartLabels[] = Carbon::create(null, $m, 1)->format('M');
                $chartSeries[] = (int) ($rows[$m] ?? 0);
            }
            $bucket = 'month';
        }

        return Inertia::render('admin/Laporan', [
            'orders'  => $orders,
            'filters' => $request->only(['period', 'start', 'end']),
            'stats'   => [
                'items'    => $activeItemCount, // global
                'supplier' => $supplierCount,   // global
                'customer' => $customerCount,   // global
                'orders'   => $ordersInPeriod,  // SUDAH ikut period
                'income'   => $income,          // SUDAH ikut period
                'profit'   => $profit,          // SUDAH ikut period + aman dari NaN
            ],
            'chart' => [
                'labels' => $chartLabels,
                'series' => $chartSeries, // counts per label
                'bucket' => $bucket,      // 'hour' | 'day' | 'month' (opsional untuk frontend)
            ],
        ]);
    }

    public function render_users(Request $request)
    {
        $search = $request->input('search');
        $usersQuery = User::query();
        if ($search) {
            $usersQuery->where('name', 'like', '%' . $search . '%');
        }
        $users = $usersQuery->latest()->get();
        return Inertia::render('admin/Pengguna', [
            'users' => $users,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }
    public function render_supplier(Request $request)
    {
        $search = $request->input('search');
        $suppliersQuery = Supplier::query()->with('items');
        if ($search) {
            $suppliersQuery->where('name', 'like', '%' . $search . '%');
        }
        $suppliers = $suppliersQuery->latest()->get()->map(function ($supplier) {
            $items = $supplier->items->pluck('name')->map(function ($name) {
                return $name;
            })->implode(', ');

            return [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'image' => $supplier->image,
                'whatsapp_number' => $supplier->whatsapp_number,
                'items' => $items,
            ];
        });
        return Inertia::render('admin/Supplier', [
            'suppliers' => $suppliers,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }
    public function render_kasir_dashboard(Request $request)
    {
        $search = $request->input('search');

        $itemsQuery = Item::query();

        if ($search) {
            $itemsQuery->where('name', 'like', '%' . $search . '%');
        }

        $items = $itemsQuery->latest()->get();
        $carts = auth()->user()->carts->load('item');
        return Inertia::render('kasir/HomeDashboard', [
            'items' => $items,
            'carts' => $carts,
            'total' => $carts->reduce(function ($carry, $cart) {
                return $carry + ($cart->item->price * $cart->amount);
            }, 0),
            'filters' => [
                'search' => $search,
            ],
        ]);
    }
    public function render_kasir_orders(Request $request)
    {
        $search = $request->input('search');
        $ordersQuery = Order::query()->with(['items.item', 'payment'])->whereNot('status', 'done');
        if ($search) {
            $ordersQuery->where('invoice_number', 'like', '%' . $search . '%');
        }
        $orders = $ordersQuery->latest()->get();
        return Inertia::render('kasir/Pesanan', [
            'orders' => $orders,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }
    public function render_kasir_history(Request $request)
    {
        $search = $request->input('search');
        $ordersQuery = Order::query()->with(['items.item', 'payment'])->where('status', 'done');
        if ($search) {
            $ordersQuery->where('invoice_number', 'like', '%' . $search . '%');
        }
        $orders = $ordersQuery->latest()->get();
        return Inertia::render('kasir/Riwayat', [
            'orders' => $orders,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }
}
