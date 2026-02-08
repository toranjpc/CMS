<?php

namespace Modules\Accounting\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Accounting\Models\Product;
use Modules\Accounting\Models\ProductOption;
use Modules\User\Models\ExtData;
use PhpParser\Node\Stmt\TryCatch;

class InvoiceController extends Controller
{
    public function index()
    {
        $product = Product::select("*");
        if (!empty(request('trashed')) && request('trashed') == "true") $product = $product->onlyTrashed();
        if (!empty(request('title'))) $product = $product->where('title', 'LIKE', '%' . request('title') . '%');
        if (!empty(request('brand_id'))) $product = $product->where('brand_id', request('brand_id'));
        if (!empty(request('category_id'))) $product = $product->where('category_id', request('category_id'));
        if (!empty(request('feature_id'))) $product = $product->where('feature_id', request('feature_id'));
        if (!empty(request('unit_id'))) $product = $product->where('unit_id', request('unit_id'));
        if (!empty(request('price'))) $product = $product->where('price', request('price'));
        if (!empty(request('price_from'))) $product = $product->where('price', '>=', request('price_from'));
        if (!empty(request('price_to'))) $product = $product->where('price', '<=', request('price_to'));
        if (!empty(request('status'))) $product = $product->where('status', request('status'));
        $product = $product->orderBy('id', 'DESC')->paginate(request("limit", 10));

        $product = [
            'items' => $product->items(),
            'total' => $product->total(),
            'per_page' => $product->perPage(),
            'current_page' => $product->currentPage(),
            'last_page' => $product->lastPage(),
            'from' => $product->firstItem(),
            'to' => $product->lastItem(),
        ];

        return response()->json(
            [
                "status" => "success",
                "data" => $product
            ],
            200
        );
    }

    public function show($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        return response()->json(
            [
                "status" => "success",
                "data" => $product
            ],
            200
        );
    }

    public function store(Request $request)
    {
        return $request->album;

        if ($request->hasFile('album')) {



            $albumFile = $request->file('album');
            $extension = strtolower($albumFile->getClientOriginalExtension());
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
            if (!in_array($extension, $allowedExtensions)) {
                throw new \Exception('فرمت فایل مجاز نیست');
            }
            $albumName = $warehouse->id; //. '.' . $extension;
            $stored = $albumFile->storeAs('products/warehouses', $albumName);
        }

        return $request;

        try {
            $data = $request->validate(
                [
                    'title' => 'required|string|max:255',
                    'barcode' => 'required|string|max:255|unique:products,barcode',
                    'des' => 'nullable|string',
                    'status' => 'nullable|integer',
                    'categories' => 'required|array|min:1',
                    'categories.*' => 'integer|exists:product_options,id',
                    'tax_rate' => 'nullable|integer',
                    'min_buy' => 'nullable|integer',
                    'max_buy' => 'nullable|integer',
                    'alert' => 'nullable|integer',
                    'variants' => 'required|array|min:1',
                    'variants.*.name' => 'required|string|max:255',
                    'variants.*.firstPrice' => 'nullable|numeric',
                    'variants.*.firstWarehouse' => 'nullable|integer',
                    'variants.*.convertUnit' => 'nullable|string',
                    'variants.*.UnitNumber' => 'nullable|integer',
                    'variants.*.selectConvertUnit' => 'nullable|integer',
                    'album' => 'nullable',
                ],
                [
                    'title.required' => 'عنوان محصول الزامی است',

                    'barcode.required' => 'بارکد محصول الزامی است',
                    'barcode.string' => 'بارکد محصول باید متن باشد',
                    'barcode.max' => 'بارکد محصول نباید بیشتر از ۲۵۵ کاراکتر باشد',
                    'barcode.unique' => 'این بارکد قبلاً ثبت شده است',

                    'des.string' => 'توضیحات باید متن باشد',

                    'status.integer' => 'وضعیت محصول معتبر نیست',

                    'categories.required' => 'انتخاب حداقل یک دسته‌بندی الزامی است',
                    'categories.array' => 'دسته‌بندی‌ها باید به صورت آرایه ارسال شوند',
                    'categories.min' => 'حداقل یک دسته‌بندی باید انتخاب شود',
                    'categories.*.integer' => 'شناسه دسته‌بندی معتبر نیست',
                    'categories.*.exists' => 'دسته‌بندی انتخاب شده یافت نشد',

                    'variants.required' => 'حداقل یک تنوع محصول الزامی است',
                    'variants.array' => 'تنوع‌ها باید به صورت آرایه ارسال شوند',
                    'variants.min' => 'حداقل یک تنوع باید تعریف شود',
                    'variants.*.name.required' => 'نام تنوع الزامی است',
                    'variants.*.name.string' => 'نام تنوع باید متن باشد',
                    'variants.*.name.max' => 'نام تنوع نباید بیشتر از ۲۵۵ کاراکتر باشد',

                    'variants.*.firstPrice.numeric' => 'قیمت اولیه باید عدد باشد',
                    'variants.*.sell_price.numeric' => 'قیمت فروش باید عدد باشد',
                    'variants.*.firstWarehouse.integer' => 'انبار اولیه باید عدد صحیح باشد',
                    'variants.*.tax_rate.integer' => 'نرخ مالیات باید عدد صحیح باشد',
                    'variants.*.min_buy.integer' => 'حداقل خرید باید عدد صحیح باشد',
                    'variants.*.max_buy.integer' => 'حداکثر خرید باید عدد صحیح باشد',
                    'variants.*.alert.integer' => 'موجودی هشدار باید عدد صحیح باشد',
                ]
            );
            return $data;

            // Create products for each variant
            $createdProducts = [];
            foreach ($data['variants'] as $variant) {
                $productData = [
                    'f_id' => $data['f_id'] ?? 0,
                    'barcode' => $data['barcode'],
                    'des' => $data['des'],
                    'status' => $data['status'],
                    'title' => $variant['name'], // variant name becomes product title
                    'firstPrice' => $variant['firstPrice'] ?? null,
                    'sell_price' => $variant['sell_price'] ?? null,
                    'firstWarehouse' => $variant['firstWarehouse'] ?? null,
                    'tax_rate' => $variant['tax_rate'] ?? null,
                    'min_buy' => $variant['min_buy'] ?? null,
                    'max_buy' => $variant['max_buy'] ?? null,
                    'alert' => $variant['alert'] ?? null,
                ];

                $dd = [
                    "f_id",
                    "title",
                    "barcode",
                    "album",
                    "tags",
                    "des",
                    "firstWarehouse",
                    "firstPrice",
                    "sell_price",
                    "form",
                    "tax_rate",
                    "min_buy",
                    "max_buy",
                    "alert",
                    "status",
                ];

                $product = Product::create($productData);
                $createdProducts[] = $product;

                // Create category relationships for this product
                foreach ($data['categories'] as $categoryId) {
                    \Modules\User\Models\ExtData::create([
                        'f_id' => $product->id,
                        'm_id' => $categoryId,
                        'kind' => 'UserCategory',
                        'status' => 1,
                    ]);
                }
            }

            return response()->json([
                "status" => "success",
                "data" => $createdProducts,
                "message" => count($createdProducts) . " محصول با موفقیت ایجاد شد"
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                "status" => "validation_error",
                "errors" => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ثبت محصول رخ داد",
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        return $request;

        try {
            $data = $request->validate(
                [
                    'title' => 'required|string|max:255',
                    'des' => 'nullable|string',
                    'tags' => 'nullable|string',
                    'f_id' => 'nullable|integer',
                    'album' => 'nullable|array',
                    'form' => 'nullable|array',
                    'firstWarehouse' => 'nullable|integer',
                    'firstPrice' => 'nullable|numeric',
                    'sell_price' => 'nullable|numeric',
                    'tax_rate' => 'nullable|integer',
                    'min_buy' => 'nullable|integer',
                    'max_buy' => 'nullable|integer',
                    'alert' => 'nullable|integer',
                    'status' => 'nullable|integer',
                ],
                [
                    'title.required' => 'عنوان محصول الزامی است',
                    'title.string' => 'عنوان محصول باید متن باشد',
                    'title.max' => 'عنوان محصول نباید بیشتر از ۲۵۵ کاراکتر باشد',

                    'des.string' => 'توضیحات باید متن باشد',
                    'tags.string' => 'تگ‌ها باید متن باشند',

                    'f_id.integer' => 'شناسه والد معتبر نیست',
                    'album.array' => 'آلبوم باید به صورت آرایه ارسال شود',
                    'form.array' => 'فرم باید به صورت آرایه ارسال شود',

                    'firstWarehouse.integer' => 'انبار اولیه معتبر نیست',
                    'firstPrice.numeric' => 'قیمت اولیه باید عدد باشد',
                    'sell_price.numeric' => 'قیمت فروش باید عدد باشد',

                    'tax_rate.integer' => 'نرخ مالیات معتبر نیست',
                    'min_buy.integer' => 'حداقل خرید معتبر نیست',
                    'max_buy.integer' => 'حداکثر خرید معتبر نیست',

                    'alert.integer' => 'مقدار هشدار معتبر نیست',
                    'status.integer' => 'وضعیت محصول معتبر نیست',
                ]
            );


            $product = Product::findOrFail($id);
            $product->update($data);

            // if ($request->hasFile('album')) {
            //     $albumFile = $request->file('album');
            //     $extension = strtolower($albumFile->getClientOriginalExtension());
            //     $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
            //     if (!in_array($extension, $allowedExtensions)) {
            //         throw new \Exception('فرمت فایل مجاز نیست');
            //     }
            //     $albumName = $product->id; //. '.' . $extension;
            //     $stored = $albumFile->storeAs('products/albums', $albumName);
            // }

            return response()->json(
                [
                    "status" => "success",
                    "data" => $product
                ],
                200
            );
        } catch (ValidationException $e) {
            return response()->json([
                "status" => "validation_error",
                "errors" => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ویرایش محصول رخ داد",
            ], 500);
        }
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json([
            "status" => "success",
            "message" => "نقش با موفقیت حذف شد"
        ], 200);
    }

    public function force_destroy($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->forceDelete();
        return response()->json([
            "status" => "success",
            "message" => "نقش به صورت دائمی حذف شد"
        ], 200);
    }

    public function restore($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();
        return response()->json([
            "status" => "success",
            "message" => "نقش بازیابی شد",
            "data" => $product
        ], 200);
    }
}
