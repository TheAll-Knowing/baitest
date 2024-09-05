<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'email' => ['required'],
            'phone' => ['required'],
            'address' => ['nullable'],
        ]);
        $newcustomer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);
        return new CustomerResource($newcustomer);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'id' => ['required'],
            'name' => ['required'],
            'email' => ['required'],
            'phone' => ['required'],
            'address' => ['nullable']
        ]);

        $customer = Customer::find($request->id);

        if ($customer) {
            $customer->update($data);
            $customernew = Customer::find($request->id);
            return new CustomerResource($customernew);
        } else {
            return response()->json([
                'error' => "Can't find customer",
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'id' => ['required'],
        ]);

        $customer = Customer::findOrFail($request->id);

        if ($customer) {
            $customer->delete();
            return new CustomerResource($customer);
        } else {
            return response()->json([
                'error' => "Can't find customer",
            ]);
        }
    }

    public function search(Request $request)
    {
        $searchParam = $request->query('s');
        $customers = Customer::query()->where('name', 'LIKE', "%{$searchParam}%")->get();
        return response()->json([
            'customers' => $customers,
        ]);
    }
}
