<?php

namespace App\Http\Controllers;

use App\Models\Vaccination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class VaccinationController extends Controller
{
    public function complete(Vaccination $vaccination): JsonResponse
    {
        $vaccination->update(['is_completed' => true]);

        return response()->json(['success' => true]);
    }

    public function reschedule(Request $request, Vaccination $vaccination): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'next_dose_date' => ['required', 'date', 'after:today'],
        ], [
            'next_dose_date.required' => 'تاريخ الموعد القادم مطلوب.',
            'next_dose_date.date'     => 'تاريخ الموعد القادم غير صالح.',
            'next_dose_date.after'    => 'يجب أن يكون تاريخ الموعد القادم بعد اليوم.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $validated = $validator->validated();

        // 1. Mark the current vaccination record as completed
        $vaccination->update(['is_completed' => true]);

        // 2. Create a new pending vaccination record for the upcoming dose
        Vaccination::create([
            'customer_id'      => $vaccination->customer_id,
            'product_id'       => $vaccination->product_id,
            'invoice_id'       => $vaccination->invoice_id,
            'vaccination_date' => today(),
            'next_dose_date'   => $validated['next_dose_date'],
            'is_completed'     => false,
        ]);

        return response()->json(['success' => true]);
    }

    public function index(Request $request): View
    {
        $query = Vaccination::query()->with(['customer', 'product', 'invoice']);

        // Search by customer name or phone
        if ($search = trim((string) $request->string('q'))) {
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        // Filter by upcoming
        $filter = $request->string('filter')->toString();
        if ($filter === 'upcoming') {
            $query->whereNotNull('next_dose_date')
                ->whereDate('next_dose_date', '>=', today())
                ->orderBy('next_dose_date', 'asc');
        } elseif ($filter === 'past') {
            $query->where(function ($q) {
                $q->whereNull('next_dose_date')
                    ->orWhereDate('next_dose_date', '<', today());
            })->orderByDesc('vaccination_date');
        } else {
            // Default sort
            $query->orderByDesc('vaccination_date')->orderByDesc('id');
        }

        $vaccinations = $query->paginate(15)->withQueryString();

        return view('vaccinations.index', [
            'vaccinations' => $vaccinations,
            'q' => $search,
            'filter' => $filter,
        ]);
    }
}
