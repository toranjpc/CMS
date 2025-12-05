<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        return view('product::index');

        return response()->json(Product::all());
    }

    public function show($id)
    {
        return response()->json(Product::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:products,email',
            'password' => 'required|string|min:8',
        ]);

        $data['password'] = bcrypt($data['password']);

        $product = Product::create($data);
        return response()->json($product, 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'name'     => 'nullable|string|max:255',
            'email'    => 'nullable|email|unique:products,email,' . $product->id,
            'password' => 'nullable|string|min:8',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $product->update($data);
        return response()->json($product);
    }

    public function destroy($id)
    {
        Product::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
