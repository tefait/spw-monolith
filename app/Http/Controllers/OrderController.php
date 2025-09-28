<?php

namespace App\Http\Controllers;

use App\Events\NewOrderCreated;
use App\Models\Cart;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;


class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'whatsapp_number' => 'required|max:255',
            'notes' => 'nullable|string|max:255',
            'payment_method' => 'required|in:qris,cash',
            'carts' => 'nullable|array',
            'source' => 'nullable|string',
        ]);
        if (Str::startsWith($data['whatsapp_number'], '08')) {
            $data['whatsapp_number'] = preg_replace('/^08/', '628', $data['whatsapp_number']);
        } elseif (Str::startsWith($data['whatsapp_number'], '8')) {
            $data['whatsapp_number'] = '62' . $data['whatsapp_number'];
        }

        if ($request->has('carts')) {
            $carts = collect($request->input('carts'))->map(function ($cartItem) {
                $item = Item::find($cartItem['item_id']);

                return $item ? (object) [
                    'item' => $item,
                    'amount' => $cartItem['amount'],
                ] : null;
            })->filter();
        } else {
            $carts = Auth::check()
                ? Cart::with('item')->where('user_id', Auth::id())->get()
                : collect(json_decode($request->cookie('cart', '[]'), true))->map(function ($cartItem) {
                    $item = Item::find($cartItem['item_id']);

                    return $item ? (object) [
                        'item' => $item,
                        'amount' => $cartItem['amount'],
                    ] : null;
                })->filter();
        }
        if ($carts->isEmpty()) {
            return redirect()->back()->withErrors(['checkout_error' => 'Keranjang kosong.']);
        }

        // Hitung total belanja
        $total = $carts->sum(fn($cart) => $cart->item->price * $cart->amount);

        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => Auth::id(),
                'transaction_code' => 'SPW' . now()->format('Ymd') . '-' . random_int(100000, 999999),
                'customer_name' => $data['customer_name'],
                'user_has_account' => Auth::check(),
                'whatsapp_number' => $data['whatsapp_number'],
                'email' => $data['email'],
                'payment_method' => $data['payment_method'],
                'notes' => $data['notes'],
                'cash_given' => $total,
                'total_amount' => $total,
            ]);

            foreach ($carts as $cart) {
                $item = $cart->item;

                // Cek stok cukup
                if ($item->stock < $cart->amount) {
                    throw new \Exception("Stok produk {$item->name} tidak mencukupi.");
                }

                // Kurangi stok dan tambah sold
                $item->decrement('stock', $cart->amount);
                $item->increment('sold', $cart->amount);

                // Buat order item
                OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $item->id,
                    'quantity' => $cart->amount,
                    'price' => $item->price,
                    'supplier_price' => $item->supplier_price,
                ]);
            }

            // Hapus cart
            if (Auth::check()) {
                Cart::where('user_id', Auth::id())->delete();
            } else {
                // Simpan transaksi ke cookies untuk non-registered users
                $transactions = json_decode($request->cookie('transactions', '[]'), true);
                $transactions[] = [
                    'transaction_code' => $order->transaction_code,
                    'customer_name' => $order->customer_name,
                    'payment_method' => $order->payment_method,
                    'total_amount' => $order->total_amount,
                    'notes' => $order->notes,
                    'created_at' => now()->toDateTimeString(),
                ];
                cookie()->queue(cookie('transactions', json_encode($transactions), 60 * 24 * 30)); // Simpan selama 30 hari
                cookie()->queue(cookie()->forget('cart')); // Hapus cart cookie
            }
            DB::commit();


            if ($request->input('source') === 'kasir') {
                return redirect('/kasir/pesanan')->with('success', 'Pesanan berhasil ditambahkan, silahkan lanjut dihalaman pesanan.');
            }

            event(new NewOrderCreated($order->load('items.item', 'payment')));
            return Inertia::render('Berhasil', [
                'order' => $order,
                'total' => $total,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withErrors(['checkout_error' => $e->getMessage()]);
        }
    }

    public function checkoutKasir(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'whatsapp_number' => 'required|max:255',
            'notes' => 'nullable|string|max:255',
            'payment_method' => 'required|in:qris,cash',
            'carts' => 'nullable|array',
            'change' => 'nullable|max:255',
            'cash_given' => 'nullable|max:255',
            'source' => 'nullable|string',
        ]);
        if (Str::startsWith($data['whatsapp_number'], '08')) {
            $data['whatsapp_number'] = preg_replace('/^08/', '628', $data['whatsapp_number']);
        } elseif (Str::startsWith($data['whatsapp_number'], '8')) {
            $data['whatsapp_number'] = '62' . $data['whatsapp_number'];
        }

        $carts = collect($request->input('carts'))->map(function ($cartItem) {
            $item = Item::find($cartItem['item_id']);

            return $item ? (object) [
                'item' => $item,
                'amount' => $cartItem['amount'],
            ] : null;
        })->filter();

        if ($carts->isEmpty()) {
            return redirect()->back()->withErrors(['checkout_error' => 'Keranjang kosong.']);
        }

        $total = $carts->sum(fn($cart) => $cart->item->price * $cart->amount);
        $cash = [
            'given' => $total,
            'change' => 0,
        ];
        if ($data['payment_method'] === 'cash' && !(empty($data['cash_given']) || empty($data['change']))) {
            $cash['given'] = $data['cash_given'];
            $cash['change'] = $data['change'];
        }

        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => Auth::id(),
                'transaction_code' => 'SPW' . now()->format('Ymd') . '-' . random_int(100000, 999999),
                'customer_name' => $data['customer_name'],
                'user_has_account' => Auth::check(),
                'cash_given' => $cash['given'],
                'change' => $cash['change'],
                'whatsapp_number' => $data['whatsapp_number'],
                'email' => $data['email'],
                'status' => 'done',
                'payment_method' => $data['payment_method'],
                'notes' => $data['notes'],
                'total_amount' => $total,
            ]);

            foreach ($carts as $cart) {
                $item = $cart->item;
                if ($item->stock < $cart->amount) {
                    throw new \Exception("Stok produk {$item->name} tidak mencukupi.");
                }

                // Kurangi stok dan tambah sold
                $item->decrement('stock', $cart->amount);
                $item->increment('sold', $cart->amount);

                // Buat order item
                OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $item->id,
                    'quantity' => $cart->amount,
                    'price' => $item->price,
                    'supplier_price' => $item->supplier_price,
                ]);
            }

            DB::commit();
            // return redirect('kasir/riwayat')->with(['success' => 'Pesanan berhasil ditambahkan, silahkan lanjut dihalaman pesanan.']);
            // event(new NewOrderCreated($order->load('items.item', 'payment')));
            return redirect()->back()->with('success', [
                'success' => true,
                'order' => $order->load('items.item'),
                'total' => $total,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withErrors(['checkout_error' => $e->getMessage()]);
        }
    }

    public function index(Request $request)
    {
        if (Auth::check()) {
            // User login → ambil semua transaksi user dari database
            $transactions = Order::where('user_id', Auth::id())
                ->latest()->with('items.item')
                ->get();
        } else {
            // Guest → ambil kode dari cookie
            $cookieTransactions = json_decode($request->cookie('transactions', '[]'), true);

            // Ambil hanya kode transaksi dari cookie
            $transactionCodes = collect($cookieTransactions)
                ->pluck('transaction_code')
                ->filter()
                ->toArray();

            // Query ke database pakai kode transaksi dari cookie
            $transactions = Order::whereIn('transaction_code', $transactionCodes)
                ->latest()->with('items.item')
                ->get();
        }

        return Inertia::render('Transaksi', [
            'transactions' => $transactions,
        ]);
    }

    public function viewOrder(Order $order, Request $request)
    {
        return Inertia::render('DetailTransaksi', [
            'order' => $order->load('items.item'),
        ]);
    }

    public function uploadBukti(Request $request, Order $order)
    {
        $request->validate([
            'bukti_pembayaran' => 'required|image|max:2048', // max 2MB
        ]);

        $path = $request->file('bukti_pembayaran')->store('bukti_pembayaran', 'public');

        $order->update([
            'status' => 'under-review',
        ]);
        $order->payment()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'amount_paid' => $order->total_amount,
                'payment_method' => $order->payment_method,
                'proof' => $path,
            ]
        );
        return back()->with('message', 'Bukti pembayaran berhasil diupload!');
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:paid,unpaid,under-review,rejected,done',
            'cash_given' => 'nullable|numeric|min:0',
            'change' => 'nullable|numeric|min:0',
            'proof' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ]);

        // Update the Order with cash details
        $order->update([
            'status' => $validated['status'],
            'cash_given' => $validated['cash_given'] ?? $order->cash_given,
            'change' => $validated['change'] ?? $order->change,
        ]);

        // Handle proof of payment upload
        if ($request->hasFile('proof')) {
            // Find the related payment record, or create it if it doesn't exist
            $payment = $order->payment()->firstOrCreate(
                ['order_id' => $order->id],
                [
                    'payment_method' => $order->payment_method,
                    'amount_paid' => $order->total_amount
                ]
            );

            // Delete old proof if it exists
            if ($payment->proof && Storage::disk('public')->exists($payment->proof)) {
                try {
                    Storage::disk('public')->delete($payment->proof);
                } catch (\Exception $e) {
                    // Log the error or handle it as needed
                    // logger()->error("Failed to delete old proof: " . $e->getMessage());
                }
            }

            // Store the new proof and update the record
            $path = $request->file('proof')->store('proofs', 'public');
            $payment->update(['proof' => $path]);
        }

        return redirect()->back()->with('message', 'Status transaksi berhasil diperbarui!');
    }
}
