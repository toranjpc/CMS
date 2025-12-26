<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Models\Product;

class ProductController extends Controller
{
    /******* categories *******/
    public function category_index()
    {
        try {
            $Options = Option::select('id', 'f_id', 'title', 'option', 'created_at', 'deleted_at')
                ->where("kind", "category");

            if (!empty(request('father'))) $Options = $Options->where('f_id', request('father'));
            else $Options = $Options->where('f_id', 0);

            if (!empty(request('title'))) $Options = $Options->where('title', 'LIKE', '%' . request('title') . '%');
            if (!empty(request('status')) && request('status') == "deleted") $Options = $Options->onlyTrashed();
            $Options = $Options->orderBy('id', 'DESC')->paginate(request("limit", 5));
            $Options = [
                'items' => $Options->items(),
                'total' => $Options->total(),
                'per_page' => $Options->perPage(),
                'current_page' => $Options->currentPage(),
                'last_page' => $Options->lastPage(),
                'from' => $Options->firstItem(),
                'to' => $Options->lastItem(),
            ];

            return response()->json(
                [
                    "status" => "success",
                    "data" => $Options
                ],
                200
            );
        } catch (\Throwable $th) {
            // throw $th;
            return response()->json(
                [
                    "status" => "error",
                ]
            );
        }
    }

    public function category_show($id)
    {
        return response()->json(Option::findOrFail($id));
    }

    public function category_store(Request $request)
    {
        try {
            $data = $request->validate(
                [
                    'title' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('options', 'title')
                            ->where('f_id', 0)
                            ->where('kind', 'category')
                            ->whereNull('deleted_at'),
                    ],
                    'parent_id' => 'nullable|exists:options,id',
                    'icon' => 'nullable|image|max:1024', // حداکثر 1MB، اختیاری
                ],
                [
                    'title.required' => 'عنوان دسته بندی الزامی است',
                    'title.string' => 'عنوان دسته بندی باید به‌صورت متن باشد',
                    'title.max' => 'عنوان دسته بندی نباید بیشتر از ۲۵۵ کاراکتر باشد',
                    'title.unique'   => 'این عنوان قبلاً ثبت شده است',

                    'parent_id.exists' => 'دسته والد نامعتبر است',
                    'icon.image' => 'فایل آیکون باید تصویر باشد',
                    'icon.max' => 'حجم فایل آیکون نباید بیشتر از 1MB باشد',
                ]
            );

            // ابتدا رکورد بدون فایل ایجاد شود
            $category = Option::create([
                "f_id" => $data['parent_id'] ?? 0,
                "title" => $data['title'],
                "kind" => "category",
                "option" => [], // ابتدا خالی
            ]);

            // سپس فایل آپلود شود و نام آن بر اساس id رکورد قرار گیرد
            $optionData = [];
            if ($request->hasFile('icon')) {
                $ext = $request->file('icon')->getClientOriginalExtension();
                $fileName = $category->id . '.' . $ext;
                $iconPath = $request->file('icon')->storeAs('categories/icons', $fileName, 'public');
                $optionData['icon'] = $iconPath;

                // بروزرسانی رکورد با مسیر فایل
                $category->option = $optionData;
                $category->save();
            }

            return response()->json([
                "status" => "success",
                "data" => $category
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                "status" => "validation_error",
                "errors" => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ثبت دسته بندی رخ داد",
            ], 500);
        }
    }

    public function category_update(Request $request, Option $category)
    {
        try {
            $data = $request->validate(
                [
                    'title' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('options', 'title')
                            ->where('f_id', 0)
                            ->where('kind', 'category')
                            ->whereNull('deleted_at')
                            ->ignore($category->id),
                    ],
                    'parent_id' => 'nullable|exists:options,id',
                    'icon' => 'nullable|image|max:1024', // اختیاری
                ],
                [
                    'title.required' => 'عنوان دسته بندی الزامی است',
                    'title.string' => 'عنوان دسته بندی باید به‌صورت متن باشد',
                    'title.max' => 'عنوان دسته بندی نباید بیشتر از ۲۵۵ کاراکتر باشد',
                    'title.unique'   => 'این عنوان قبلاً ثبت شده است',

                    'parent_id.exists' => 'دسته والد نامعتبر است',
                    'icon.image' => 'فایل آیکون باید تصویر باشد',
                    'icon.max' => 'حجم فایل آیکون نباید بیشتر از 1MB باشد',
                ]
            );

            $category->f_id = $data['parent_id'] ?? 0;
            $category->title = $data['title'];

            $optionData = $category->option ?? [];

            if ($request->hasFile('icon')) {
                $ext = $request->file('icon')->getClientOriginalExtension();
                $fileName = $category->id . '.' . $ext;
                $iconPath = $request->file('icon')->storeAs('categories/icons', $fileName, 'public');
                $optionData['icon'] = $iconPath;
            }

            $category->option = $optionData;
            $category->update();

            return response()->json([
                "status" => "success",
                "data" => $category
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                "status" => "validation_error",
                "errors" => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ویرایش دسته بندی رخ داد",
            ], 500);
        }
    }


    public function category_destroy(Option $category)
    {
        if ($category->childs()->exists()) {
            return response()->json([
                "status" => "error",
                "message" => "این دسته بندی دارای زیرمجموعه است و نمی‌توان آن را حذف کرد"
            ], 422);
        }

        $category->delete();
        return response()->json([
            "status" => "success",
            "message" => "دسته بندی با موفقیت حذف شد"
        ], 200);
    }

    public function category_force_destroy($id)
    {
        $category = Option::withTrashed()->findOrFail($id);

        if ($category->childs()->exists()) {
            return response()->json([
                "status" => "error",
                "message" => "این دسته بندی دارای زیرمجموعه است و نمی‌توان آن را به صورت دائمی حذف کرد"
            ], 422);
        }

        $category->forceDelete();
        return response()->json([
            "status" => "success",
            "message" => "دسته بندی به صورت دائمی حذف شد"
        ], 200);
    }


    public function category_restore($id)
    {
        $category = Option::withTrashed()->findOrFail($id);
        $category->restore();
        return response()->json([
            "status" => "success",
            "message" => "دسته بندی بازیابی شد",
            "data" => $category
        ], 200);
    }
    /******* categories *******/



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
