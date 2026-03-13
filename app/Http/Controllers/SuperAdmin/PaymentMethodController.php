<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $methods = PaymentMethod::withCount('restaurants')->orderBy('name')->get();
        return view('superadmin.payment-methods', compact('methods'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100', 'icon' => 'nullable|string|max:100']);
        PaymentMethod::create(['name' => $request->name, 'icon' => $request->icon]);
        return back()->with('success', 'Medio de pago creado.');
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $request->validate(['name' => 'required|string|max:100', 'icon' => 'nullable|string|max:100']);
        $paymentMethod->update(['name' => $request->name, 'icon' => $request->icon]);
        return back()->with('success', 'Medio de pago actualizado.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();
        return back()->with('success', 'Medio de pago eliminado.');
    }
}
