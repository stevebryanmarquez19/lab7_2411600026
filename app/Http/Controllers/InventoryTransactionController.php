<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryTransaction;
use App\Services\SearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use RuntimeException;

class InventoryTransactionController extends Controller
{
    public function index(Request $request, SearchService $search)
    {
        $filters = $request->only(['search', 'type', 'user', 'product_id', 'date_from', 'date_to']);
        $transactions = $search->searchTransactions($filters)->paginate(15)->withQueryString();
        return view('transactions.index', compact('transactions'));
    }

    public function show(InventoryTransaction $transaction)
    {
        $transaction->load(['product', 'user']);
        return view('transactions.show', compact('transaction'));
    }

    public function store(Request $request)
    {
        $type = $request->input('type');
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => ($type === 'adjustment' ? 'required|integer|min:0|max:1000000' : 'required|integer|min:1|max:1000000'),
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);
        if ($data['type'] === 'adjustment' && empty(trim($data['notes'] ?? ''))) {
            return back()->withErrors(['notes' => 'Please enter a reason for the stock adjustment.'])->withInput();
        }

        try {
            DB::transaction(function () use ($data) {
                $product = Product::whereKey($data['product_id'])->lockForUpdate()->firstOrFail();
                if ($data['type'] === 'out' && $product->quantity < $data['quantity']) {
                    throw new RuntimeException('Not enough stock. Available quantity: ' . $product->quantity . '.');
                }
                InventoryTransaction::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'actor_name' => session('fitpulse_user', 'Admin'),
                    'type' => $data['type'],
                    'quantity' => $data['quantity'],
                    'reference' => $data['reference'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);
            });
        } catch (RuntimeException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()])->withInput();
        } catch (QueryException $e) {
            return back()->withErrors(['quantity' => 'Stock could not be updated. Please check the available quantity and try again.'])->withInput();
        }

        $message = $data['type'] === 'adjustment'
            ? 'Stock count adjusted and saved to the transaction history.'
            : 'Stock movement recorded and inventory updated.';
        return redirect()->route('dashboard')->with('success', $message);
    }
}
