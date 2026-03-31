<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuickSaleRequest;
use App\Models\Invoice;
use App\Models\Product;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Mpdf\Mpdf;
use RuntimeException;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
    ) {}

    /**
     * Show invoice list with optional search/filter.
     */
    public function index(Request $request)
    {
        // تم الإضافة: لو الموظف يشوف فواتيره النهارده بس
        if (auth()->user()->hasRole('employee')) {
            // حساب نافذة يوم العمل (تبدأ الساعة 2 صباحاً)
            $now = \Carbon\Carbon::now();
            $businessDayStart = $now->copy()->startOfDay()->addHours(2);

            if ($now->lt($businessDayStart)) {
                $periodStart = $businessDayStart->copy()->subDay();
                $periodEnd = $businessDayStart->copy()->subSecond();
            } else {
                $periodStart = $businessDayStart;
                $periodEnd = $businessDayStart->copy()->addDay()->subSecond();
            }

            $invoices = Invoice::query()
                ->with('customer')
                ->where('created_by', auth()->id())
                ->whereBetween('created_at', [$periodStart, $periodEnd])
                ->latest()
                ->paginate(25)
                ->withQueryString();

            // الموظف يشوف قائمة بسيطة بدون فلاتر
            return view('invoices.index', [
                'invoices' => $invoices,
                'q' => '',
                'source' => '',
                'status' => '',
                'isEmployee' => true,
            ]);
        }

        // لو الأدمن: المنطق الموجود كما هو
        $q = $request->input('q', '');
        $source = $request->input('source', '');
        // فلتر الحالة — مؤكدة أو ملغية
        $status = $request->input('status', '');
        $date = $request->input('date');

        $invoices = Invoice::query()
            ->with('customer')
            ->when($q, function ($query) use ($q) {
                $query->where('invoice_number', 'like', "%{$q}%")
                    ->orWhere('customer_name', 'like', "%{$q}%");
            })
            ->when($source, fn ($query) => $query->where('source', $source))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($date, fn ($query) => $query->whereDate('created_at', $date))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('invoices.index', compact('invoices', 'q', 'source', 'status', 'date'));
    }

    /**
     * Show the quick-sale form.
     */
    public function create()
    {
        $products = Product::query()
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'stock_status', 'quantity', 'track_stock', 'type']);

        return view('invoices.create', compact('products'));
    }

    /**
     * Save a quick-sale invoice (transactional).
     */
    public function store(StoreQuickSaleRequest $request)
    {
        try {
            $invoice = $this->invoiceService->saveQuickSale($request->validated());

            return redirect()
                ->route('invoices.index')
                ->with('success', __('invoices.messages.created'));
        } catch (RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors(['stock' => $e->getMessage()]);
        }
    }

    /**
     * Show a single invoice with its items.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'items.product', 'vaccinations']);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * إلغاء فاتورة مع إرجاع الستوك — يستدعي InvoiceService::cancelInvoice()
     */
    public function cancel(Request $request, Invoice $invoice)
    {
        try {
            // تمرير سبب الإلغاء إن وُجد
            $this->invoiceService->cancelInvoice(
                $invoice,
                $request->input('cancellation_reason')
            );

            return redirect()
                ->route('invoices.show', $invoice)
                ->with('success', __('invoices.messages.cancelled'));
        } catch (RuntimeException $e) {
            return back()->withErrors(['cancel' => $e->getMessage()]);
        }
    }

    /**
     * Download Invoice as PDF using mPDF natively
     */
    public function pdf(Invoice $invoice)
    {
        $invoice->load(['items.product', 'customer']);

        // Render the view to HTML string
        $html = view('invoices.pdf', compact('invoice'))->render();

        // Initialize native mPDF
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'sans-serif',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);

        $mpdf->SetDirectionality('rtl');
        $mpdf->WriteHTML($html);

        $filename = 'فاتورة-'.$invoice->invoice_number.'.pdf';

        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
