<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerVisitRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\VaccineBatch;
use App\Services\CustomerVisitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerVisitService $visitService,
    ) {}

    /**
     * Show all customers with optional search by name or phone.
     */
    public function index(Request $request)
    {
        $q = $request->input('q', '');

        $customers = Customer::query()
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            })
            ->withCount('vaccinations')
            ->with(['vaccinations' => function ($q) {
                $q->latest('vaccination_date')->limit(1);
            }])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('customers.index', compact('customers', 'q'));
    }

    /**
     * Show the customer visit form.
     */
    public function create()
    {
        // Pre-load the consultation service product
        $consultationProduct = Product::query()
            ->active()
            ->where('type', 'service')
            ->orderByDesc('id')
            ->first();

        // All active vaccine products (including out-of-stock so we can show them disabled)
        $vaccines = Product::query()
            ->active()
            ->where('type', 'vaccination')
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'stock_status', 'quantity', 'track_stock']);

        // Compute usable batch qty per vaccine product
        // (batches not expired AND quantity_remaining > 0)
        $today = now()->toDateString();
        $vaccineUsableQty = VaccineBatch::query()
            ->where('expiry_date', '>=', $today)
            ->where('quantity_remaining', '>', 0)
            ->groupBy('product_id')
            ->select('product_id', DB::raw('SUM(quantity_remaining) as usable_qty'))
            ->pluck('usable_qty', 'product_id');

        // All active selectable products for additional items
        $products = Product::query()
            ->active()
            ->whereIn('type', ['product', 'service', 'vaccination'])
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'type', 'stock_status', 'quantity', 'track_stock']);

        return view('customers.create', compact('consultationProduct', 'vaccines', 'vaccineUsableQty', 'products'));
    }

    /**
     * Save the customer visit (transactional).
     */
    public function store(StoreCustomerVisitRequest $request)
    {
        try {
            $invoice = $this->visitService->saveVisit($request->validated());

            return redirect()
                ->route('customers.index')
                ->with('success', __('customers.messages.created'));
        } catch (RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors(['vaccine' => $e->getMessage()]);
        }
    }
}
