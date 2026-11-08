<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'integer', 'min:0', 'max:100000000'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function index()
    {
        return view('admin.products.index', ['products' => Product::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('admin.products.form', ['product' => new Product(['is_active' => true, 'stock' => 0])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['is_active'] = $request->boolean('is_active');
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        unset($data['image']);
        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate($this->rules());
        $data['is_active'] = $request->boolean('is_active');
        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        unset($data['image']);
        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        if (BookingProduct::where('product_id', $product->id)->exists()) {
            $product->update(['is_active' => false]);

            return back()->with('success', 'Produk pernah dipesan, jadi hanya dinonaktifkan (riwayat tetap aman).');
        }

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        $product->delete();

        return back()->with('success', 'Produk dihapus.');
    }
}
