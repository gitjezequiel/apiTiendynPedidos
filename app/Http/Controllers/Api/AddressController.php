<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->orderByDesc('is_default')->orderBy('created_at')->get();
        return response()->json(['status' => 'success', 'data' => $addresses]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'label'     => 'required|string|max:50',
            'address'   => 'required|string|max:255',
            'reference' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $user = $request->user();

        $address = UserAddress::create([
            'user_id'    => $user->id,
            'label'      => $request->label,
            'address'    => $request->address,
            'reference'  => $request->reference,
            'is_default' => $user->addresses()->count() === 0,
        ]);

        return response()->json(['status' => 'success', 'data' => $address], 201);
    }

    public function update(Request $request, $id)
    {
        $address = $request->user()->addresses()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'label'     => 'string|max:50',
            'address'   => 'string|max:255',
            'reference' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $address->update($request->only(['label', 'address', 'reference']));

        return response()->json(['status' => 'success', 'data' => $address]);
    }

    public function destroy(Request $request, $id)
    {
        $address = $request->user()->addresses()->findOrFail($id);
        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $request->user()->addresses()->first()?->update(['is_default' => true]);
        }

        return response()->json(['status' => 'success', 'message' => 'Dirección eliminada']);
    }

    public function setDefault(Request $request, $id)
    {
        $user = $request->user();
        $user->addresses()->update(['is_default' => false]);
        $user->addresses()->findOrFail($id)->update(['is_default' => true]);

        return response()->json(['status' => 'success', 'message' => 'Dirección predeterminada actualizada']);
    }
}
