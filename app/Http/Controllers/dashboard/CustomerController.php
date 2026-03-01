<?php
// app/Http/Controllers/Dashboard/CustomerController.php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /* ========== list & search ========== */
    public function index(Request $request)
    {
        $permissions = session('permissions');
    if (!isset($permissions['Customer']) || !in_array('read', $permissions['Customer']['actions'])) {
        abort(403, 'Unauthorized action.');
    }
        $query = Customer::query();

        /* search by name OR phone */
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name',  'LIKE', "%{$search}%")
                  ->orWhere('phone','LIKE', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate(15)->withQueryString();

        return view('content.customers.index', compact('customers'));
    }

    /* ========== single record ========== */
    public function show(Customer $customer)
    {
         $permissions = session('permissions');
    if (!isset($permissions['Customer']) || !in_array('read', $permissions['Customer']['actions'])) {
        abort(403, 'Unauthorized action.');
    }
       $reviews = $customer->reviews()
                        ->with('bundle:id,name')
                        ->latest()
                        ->paginate(10);

    return view('content.customers.show', compact('customer', 'reviews'));
    }
}
