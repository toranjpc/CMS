<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductOption;
use PhpParser\Node\Stmt\TryCatch;

class ProductController extends Controller
{
    /******* categories *******/
    public function category_index()
    {
        try {
            $Options = ProductOption::select('id', 'f_id', 'title', 'option', 'created_at', 'updated_at', 'deleted_at')
                ->where("kind", "category");

            $f_id = 0;
            if (!empty(request('father')))
                $Options = $Options->where('f_id', request('father'));

            if (!empty(request('title'))) $Options = $Options->where('title', 'LIKE', '%' . request('title') . '%');
            if (!empty(request('status')) && request('status') == "deleted") $Options = $Options->onlyTrashed();
            $Options = $Options->orderBy('id', 'DESC')->paginate(request("limit", 10));
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
        return response()->json(ProductOption::findOrFail($id));
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
                        Rule::unique(ProductOption::class, 'title')
                            ->where('f_id', 0)
                            ->where('kind', 'category')
                            ->whereNull('deleted_at'),
                    ],
                    'parent_id' => 'nullable|intiger',
                    'parent_id' => ['nullable', 'integer', Rule::exists(ProductOption::class, 'id')->where(function ($query) {
                        $query->where('kind', 'category');
                    })],
                    'icon' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',

                ],
                [
                    'parent_id.integer' => 'شناسه والد باید عددی باشد',
                    'parent_id.exists' => 'شناسه والد در دسته بندی‌ها موجود نیست',
                    'title.required' => 'عنوان دسته بندی الزامی است',
                    'title.string' => 'عنوان دسته بندی باید به‌صورت متن باشد',
                    'title.max' => 'عنوان دسته بندی نباید بیشتر از ۲۵۵ کاراکتر باشد',
                    'title.unique'   => 'این عنوان قبلاً ثبت شده است',
                    'icon.file' => 'فایل باید معتبر باشد',
                    'icon.mimes' => 'فرمت فایل باید jpeg، png، jpg، gif یا svg باشد',
                    'icon.max' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد',
                ]
            );

            $category = ProductOption::create([
                "f_id" => $data['parent_id'] ?? 0,
                "title" => $data['title'],
                "kind" => "category",
                "option" => [],
            ]);


            if ($request->hasFile('icon')) {
                $iconFile = $request->file('icon');
                $extension = strtolower($iconFile->getClientOriginalExtension());
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
                if (!in_array($extension, $allowedExtensions)) {
                    throw new \Exception('فرمت فایل مجاز نیست');
                }
                $iconName = $category->id; //. '.' . $extension;
                $stored = $iconFile->storeAs('products/categories', $iconName);
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

    public function category_update(Request $request, ProductOption $category)
    {
        try {
            $data = $request->validate(
                [
                    'title' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique(ProductOption::class, 'title')
                            ->where('f_id', 0)
                            ->where('kind', 'category')
                            ->whereNull('deleted_at')
                            ->ignore($category->id),
                    ],
                    'parent_id' => ['nullable', 'integer', Rule::exists(ProductOption::class, 'id')->where(function ($query) {
                        $query->where('kind', 'category');
                    })],
                    'icon' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ],
                [
                    'parent_id.integer' => 'شناسه والد باید عددی باشد',
                    'parent_id.exists' => 'شناسه والد در دسته بندی‌ها موجود نیست',
                    'title.required' => 'عنوان دسته بندی الزامی است',
                    'title.string' => 'عنوان دسته بندی باید به‌صورت متن باشد',
                    'title.max' => 'عنوان دسته بندی نباید بیشتر از ۲۵۵ کاراکتر باشد',
                    'title.unique'   => 'این عنوان قبلاً ثبت شده است',
                    'icon.file' => 'فایل باید معتبر باشد',
                    'icon.mimes' => 'فرمت فایل باید jpeg، png، jpg، gif یا svg باشد',
                    'icon.max' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد',
                ]
            );

            $category->f_id = $data['parent_id'] ?? 0;
            $category->title = $data['title'];
            $category->updated_at = now();
            $category->update();

            if ($request->hasFile('icon')) {
                $oldIconPath = storage_path('app/public/products/categories/' . $category->id . '.*');
                $oldFiles = glob($oldIconPath);
                foreach ($oldFiles as $oldFile) {
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                $iconFile = $request->file('icon');
                $extension = strtolower($iconFile->getClientOriginalExtension());


                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
                if (!in_array($extension, $allowedExtensions)) {
                    throw new \Exception('فرمت فایل مجاز نیست');
                }
                $iconName = $category->id; //. '.' . $extension;
                $stored = $iconFile->storeAs('products/categories', $iconName);
            }


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


    public function category_destroy(ProductOption $category)
    {
        $category->delete();
        return response()->json([
            "status" => "success",
            "message" => "دسته بندی با موفقیت حذف شد"
        ], 200);
    }

    public function category_force_destroy($id)
    {
        $category = ProductOption::withTrashed()->findOrFail($id);

        // پاک کردن فایل آیکون
        $oldIconPath = storage_path('app/public/products/categories/' . $category->id . '.*');
        $oldFiles = glob($oldIconPath);
        foreach ($oldFiles as $oldFile) {
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        $category->forceDelete();
        return response()->json([
            "status" => "success",
            "message" => "دسته بندی به صورت دائمی حذف شد"
        ], 200);
    }


    public function category_restore($id)
    {
        $category = ProductOption::withTrashed()->findOrFail($id);
        $category->restore();
        return response()->json([
            "status" => "success",
            "message" => "دسته بندی بازیابی شد",
            "data" => $category
        ], 200);
    }
    /******* categories *******/


    /******* features *******/
    public function feature_index()
    {
        try {
            $Options = ProductOption::select('id', 'f_id', 'title', 'option', 'created_at', 'updated_at', 'deleted_at')
                ->where("kind", "option");

            if (!empty(request('title'))) $Options = $Options->where('title', 'LIKE', '%' . request('title') . '%');
            if (!empty(request('status')) && request('status') == "deleted") $Options = $Options->onlyTrashed();
            $Options = $Options->orderBy('id', 'DESC')->paginate(request("limit", 10));
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

    public function feature_show($id)
    {
        return response()->json(ProductOption::findOrFail($id));
    }

    public function feature_store(Request $request)
    {
        try {
            $data = $request->validate(
                [
                    'title' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique(ProductOption::class, 'title')
                            ->where('f_id', 0)
                            ->where('kind', 'option')
                            ->whereNull('deleted_at'),
                    ],
                    'parent_id' => ['nullable', 'integer', Rule::exists(ProductOption::class, 'id')->where(function ($query) {
                        $query->where('kind', 'option');
                    })],
                    'icon' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',

                ],
                [
                    'parent_id.integer' => 'شناسه والد باید عددی باشد',
                    'parent_id.exists' => 'شناسه والد در ویژگی‌ها موجود نیست',
                    'title.required' => 'عنوان ویژگی الزامی است',
                    'title.string' => 'عنوان ویژگی باید به‌صورت متن باشد',
                    'title.max' => 'عنوان ویژگی نباید بیشتر از ۲۵۵ کاراکتر باشد',
                    'title.unique'   => 'این عنوان قبلاً ثبت شده است',
                    'icon.file' => 'فایل باید معتبر باشد',
                    'icon.mimes' => 'فرمت فایل باید jpeg، png، jpg، gif یا svg باشد',
                    'icon.max' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد',
                ]
            );

            $feature = ProductOption::create([
                "f_id" => $data['parent_id'] ?? 0,
                "title" => $data['title'],
                "kind" => "option",
                "option" => [],
            ]);


            if ($request->hasFile('icon')) {
                $iconFile = $request->file('icon');
                $extension = strtolower($iconFile->getClientOriginalExtension());
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
                if (!in_array($extension, $allowedExtensions)) {
                    throw new \Exception('فرمت فایل مجاز نیست');
                }
                $iconName = $feature->id; //. '.' . $extension;
                $stored = $iconFile->storeAs('products/features', $iconName);
            }

            return response()->json([
                "status" => "success",
                "data" => $feature
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                "status" => "validation_error",
                "errors" => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ثبت ویژگی رخ داد",
            ], 500);
        }
    }

    public function feature_update(ProductOption $feature, Request $request)
    {
        try {
            $data = $request->validate(
                [
                    'title' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique(ProductOption::class, 'title')
                            ->where('f_id', 0)
                            ->where('kind', 'option')
                            ->whereNull('deleted_at')
                            ->ignore($feature->id),
                    ],
                    'parent_id' => ['nullable', 'integer', Rule::exists(ProductOption::class, 'id')->where(function ($query) {
                        $query->where('kind', 'option');
                    })],
                    'icon' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ],
                [
                    'parent_id.integer' => 'شناسه والد باید عددی باشد',
                    'parent_id.exists' => 'شناسه والد در ویژگی‌ها موجود نیست',
                    'title.required' => 'عنوان ویژگی الزامی است',
                    'title.string' => 'عنوان ویژگی باید به‌صورت متن باشد',
                    'title.max' => 'عنوان ویژگی نباید بیشتر از ۲۵۵ کاراکتر باشد',
                    'title.unique'   => 'این عنوان قبلاً ثبت شده است',
                    'icon.file' => 'فایل باید معتبر باشد',
                    'icon.mimes' => 'فرمت فایل باید jpeg، png، jpg، gif یا svg باشد',
                    'icon.max' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد',
                ]
            );

            $feature->f_id = $data['parent_id'] ?? 0;
            $feature->title = $data['title'];
            $feature->updated_at = now();
            $feature->update();

            if ($request->hasFile('icon')) {
                $oldIconPath = storage_path('app/public/products/features/' . $feature->id . '.*');
                $oldFiles = glob($oldIconPath);
                foreach ($oldFiles as $oldFile) {
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                $iconFile = $request->file('icon');
                $extension = strtolower($iconFile->getClientOriginalExtension());


                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
                if (!in_array($extension, $allowedExtensions)) {
                    throw new \Exception('فرمت فایل مجاز نیست');
                }
                $iconName = $feature->id; //. '.' . $extension;
                $stored = $iconFile->storeAs('products/features', $iconName);
            }


            return response()->json([
                "status" => "success",
                "data" => $feature
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                "status" => "validation_error",
                "errors" => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ویرایش ویژگی رخ داد",
            ], 500);
        }
    }


    public function feature_destroy(ProductOption $feature)
    {
        $feature->delete();
        return response()->json([
            "status" => "success",
            "message" => "ویژگی با موفقیت حذف شد"
        ], 200);
    }

    public function feature_force_destroy($id)
    {
        $feature = ProductOption::withTrashed()->findOrFail($id);

        // پاک کردن فایل آیکون
        $oldIconPath = storage_path('app/public/products/features/' . $feature->id . '.*');
        $oldFiles = glob($oldIconPath);
        foreach ($oldFiles as $oldFile) {
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        $feature->forceDelete();
        return response()->json([
            "status" => "success",
            "message" => "ویژگی به صورت دائمی حذف شد"
        ], 200);
    }


    public function feature_restore($id)
    {
        $feature = ProductOption::withTrashed()->findOrFail($id);
        $feature->restore();
        return response()->json([
            "status" => "success",
            "message" => "ویژگی بازیابی شد",
            "data" => $feature
        ], 200);
    }
    /******* features *******/


    /******* brands *******/
    public function brand_index()
    {
        try {
            $Options = ProductOption::select('id', 'f_id', 'title', 'option', 'created_at', 'updated_at', 'deleted_at')
                ->where("kind", "brand");

            if (!empty(request('title'))) $Options = $Options->where('title', 'LIKE', '%' . request('title') . '%');
            if (!empty(request('status')) && request('status') == "deleted") $Options = $Options->onlyTrashed();
            $Options = $Options->orderBy('id', 'DESC')->paginate(request("limit", 10));
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

    public function brand_show($id)
    {
        return response()->json(ProductOption::findOrFail($id));
    }

    public function brand_store(Request $request)
    {
        try {
            $data = $request->validate(
                [
                    'title' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique(ProductOption::class, 'title')
                            ->where('f_id', 0)
                            ->where('kind', 'brand')
                            ->whereNull('deleted_at'),
                    ],
                    'parent_id' => ['nullable', 'integer', Rule::exists(ProductOption::class, 'id')->where(function ($query) {
                        $query->where('kind', 'brand');
                    })],
                    'icon' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',

                ],
                [
                    'parent_id.integer' => 'شناسه والد باید عددی باشد',
                    'parent_id.exists' => 'شناسه والد در برندها موجود نیست',
                    'title.required' => 'عنوان برند الزامی است',
                    'title.string' => 'عنوان برند باید به‌صورت متن باشد',
                    'title.max' => 'عنوان برند نباید بیشتر از ۲۵۵ کاراکتر باشد',
                    'title.unique'   => 'این عنوان قبلاً ثبت شده است',
                    'icon.file' => 'فایل باید معتبر باشد',
                    'icon.mimes' => 'فرمت فایل باید jpeg، png، jpg، gif یا svg باشد',
                    'icon.max' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد',
                ]
            );

            $brand = ProductOption::create([
                "f_id" => $data['parent_id'] ?? 0,
                "title" => $data['title'],
                "kind" => "brand",
                "option" => [],
            ]);


            if ($request->hasFile('icon')) {
                $iconFile = $request->file('icon');
                $extension = strtolower($iconFile->getClientOriginalExtension());
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
                if (!in_array($extension, $allowedExtensions)) {
                    throw new \Exception('فرمت فایل مجاز نیست');
                }
                $iconName = $brand->id; //. '.' . $extension;
                $stored = $iconFile->storeAs('products/brands', $iconName);
            }

            return response()->json([
                "status" => "success",
                "data" => $brand
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                "status" => "validation_error",
                "errors" => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ثبت برند رخ داد",
            ], 500);
        }
    }

    public function brand_update(Request $request, ProductOption $brand)
    {
        try {
            $data = $request->validate(
                [
                    'title' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique(ProductOption::class, 'title')
                            ->where('f_id', 0)
                            ->where('kind', 'brand')
                            ->whereNull('deleted_at')
                            ->ignore($brand->id),
                    ],
                    'parent_id' => ['nullable', 'integer', Rule::exists(ProductOption::class, 'id')->where(function ($query) {
                        $query->where('kind', 'brand');
                    })],
                    'icon' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ],
                [
                    'parent_id.integer' => 'شناسه والد باید عددی باشد',
                    'parent_id.exists' => 'شناسه والد در برندها موجود نیست',
                    'title.required' => 'عنوان برند الزامی است',
                    'title.string' => 'عنوان برند باید به‌صورت متن باشد',
                    'title.max' => 'عنوان برند نباید بیشتر از ۲۵۵ کاراکتر باشد',
                    'title.unique'   => 'این عنوان قبلاً ثبت شده است',
                    'icon.file' => 'فایل باید معتبر باشد',
                    'icon.mimes' => 'فرمت فایل باید jpeg، png، jpg، gif یا svg باشد',
                    'icon.max' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد',
                ]
            );

            $brand->f_id = $data['parent_id'] ?? 0;
            $brand->title = $data['title'];
            $brand->updated_at = now();
            $brand->update();

            if ($request->hasFile('icon')) {
                $oldIconPath = storage_path('app/public/products/brands/' . $brand->id . '.*');
                $oldFiles = glob($oldIconPath);
                foreach ($oldFiles as $oldFile) {
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                $iconFile = $request->file('icon');
                $extension = strtolower($iconFile->getClientOriginalExtension());


                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
                if (!in_array($extension, $allowedExtensions)) {
                    throw new \Exception('فرمت فایل مجاز نیست');
                }
                $iconName = $brand->id; //. '.' . $extension;
                $stored = $iconFile->storeAs('products/brands', $iconName);
            }


            return response()->json([
                "status" => "success",
                "data" => $brand
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                "status" => "validation_error",
                "errors" => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ویرایش برند رخ داد",
            ], 500);
        }
    }


    public function brand_destroy(ProductOption $brand)
    {
        $brand->delete();
        return response()->json([
            "status" => "success",
            "message" => "برند با موفقیت حذف شد"
        ], 200);
    }

    public function brand_force_destroy($id)
    {
        $brand = ProductOption::withTrashed()->findOrFail($id);

        // پاک کردن فایل آیکون
        $oldIconPath = storage_path('app/public/products/brands/' . $brand->id . '.*');
        $oldFiles = glob($oldIconPath);
        foreach ($oldFiles as $oldFile) {
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        $brand->forceDelete();
        return response()->json([
            "status" => "success",
            "message" => "برند به صورت دائمی حذف شد"
        ], 200);
    }


    public function brand_restore($id)
    {
        $brand = ProductOption::withTrashed()->findOrFail($id);
        $brand->restore();
        return response()->json([
            "status" => "success",
            "message" => "برند بازیابی شد",
            "data" => $brand
        ], 200);
    }
    /******* brands *******/

    /******* units *******/
    public function unit_index()
    {
        try {
            $Options = ProductOption::select('id', 'f_id', 'title', 'option', 'created_at', 'updated_at', 'deleted_at')
                ->where("kind", "unit");

            if (!empty(request('title'))) $Options = $Options->where('title', 'LIKE', '%' . request('title') . '%');
            if (!empty(request('status')) && request('status') == "deleted") $Options = $Options->onlyTrashed();
            $Options = $Options->orderBy('id', 'DESC')->paginate(request("limit", 10));
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

    public function unit_show($id)
    {
        return response()->json(ProductOption::findOrFail($id));
    }

    public function unit_store(Request $request)
    {
        try {
            $data = $request->validate(
                [
                    'title' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique(ProductOption::class, 'title')
                            ->where('f_id', 0)
                            ->where('kind', 'unit')
                            ->whereNull('deleted_at'),
                    ],
                    'parent_id' => ['nullable', 'integer', Rule::exists(ProductOption::class, 'id')->where(function ($query) {
                        $query->where('kind', 'unit');
                    })],
                    'icon' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',

                ],
                [
                    'parent_id.integer' => 'شناسه والد باید عددی باشد',
                    'parent_id.exists' => 'شناسه والد در برندها موجود نیست',
                    'title.required' => 'عنوان برند الزامی است',
                    'title.string' => 'عنوان برند باید به‌صورت متن باشد',
                    'title.max' => 'عنوان برند نباید بیشتر از ۲۵۵ کاراکتر باشد',
                    'title.unique'   => 'این عنوان قبلاً ثبت شده است',
                    'icon.file' => 'فایل باید معتبر باشد',
                    'icon.mimes' => 'فرمت فایل باید jpeg، png، jpg، gif یا svg باشد',
                    'icon.max' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد',
                ]
            );

            $unit = ProductOption::create([
                "f_id" => $data['parent_id'] ?? 0,
                "title" => $data['title'],
                "kind" => "unit",
                "option" => [],
            ]);


            if ($request->hasFile('icon')) {
                $iconFile = $request->file('icon');
                $extension = strtolower($iconFile->getClientOriginalExtension());
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
                if (!in_array($extension, $allowedExtensions)) {
                    throw new \Exception('فرمت فایل مجاز نیست');
                }
                $iconName = $unit->id; //. '.' . $extension;
                $stored = $iconFile->storeAs('products/units', $iconName);
            }

            return response()->json([
                "status" => "success",
                "data" => $unit
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                "status" => "validation_error",
                "errors" => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ثبت برند رخ داد",
            ], 500);
        }
    }

    public function unit_update(Request $request, ProductOption $unit)
    {
        try {
            $data = $request->validate(
                [
                    'title' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique(ProductOption::class, 'title')
                            ->where('f_id', 0)
                            ->where('kind', 'unit')
                            ->whereNull('deleted_at')
                            ->ignore($unit->id),
                    ],
                    'parent_id' => ['nullable', 'integer', Rule::exists(ProductOption::class, 'id')->where(function ($query) {
                        $query->where('kind', 'unit');
                    })],
                    'icon' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ],
                [
                    'parent_id.integer' => 'شناسه والد باید عددی باشد',
                    'parent_id.exists' => 'شناسه والد در برندها موجود نیست',
                    'title.required' => 'عنوان برند الزامی است',
                    'title.string' => 'عنوان برند باید به‌صورت متن باشد',
                    'title.max' => 'عنوان برند نباید بیشتر از ۲۵۵ کاراکتر باشد',
                    'title.unique'   => 'این عنوان قبلاً ثبت شده است',
                    'icon.file' => 'فایل باید معتبر باشد',
                    'icon.mimes' => 'فرمت فایل باید jpeg، png، jpg، gif یا svg باشد',
                    'icon.max' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد',
                ]
            );

            $unit->f_id = $data['parent_id'] ?? 0;
            $unit->title = $data['title'];
            $unit->updated_at = now();
            $unit->update();

            if ($request->hasFile('icon')) {
                $oldIconPath = storage_path('app/public/products/units/' . $unit->id . '.*');
                $oldFiles = glob($oldIconPath);
                foreach ($oldFiles as $oldFile) {
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                $iconFile = $request->file('icon');
                $extension = strtolower($iconFile->getClientOriginalExtension());


                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
                if (!in_array($extension, $allowedExtensions)) {
                    throw new \Exception('فرمت فایل مجاز نیست');
                }
                $iconName = $unit->id; //. '.' . $extension;
                $stored = $iconFile->storeAs('products/units', $iconName);
            }


            return response()->json([
                "status" => "success",
                "data" => $unit
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                "status" => "validation_error",
                "errors" => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ویرایش برند رخ داد",
            ], 500);
        }
    }


    public function unit_destroy(ProductOption $unit)
    {
        $unit->delete();
        return response()->json([
            "status" => "success",
            "message" => "برند با موفقیت حذف شد"
        ], 200);
    }

    public function unit_force_destroy($id)
    {
        $unit = ProductOption::withTrashed()->findOrFail($id);

        // پاک کردن فایل آیکون
        $oldIconPath = storage_path('app/public/products/units/' . $unit->id . '.*');
        $oldFiles = glob($oldIconPath);
        foreach ($oldFiles as $oldFile) {
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        $unit->forceDelete();
        return response()->json([
            "status" => "success",
            "message" => "برند به صورت دائمی حذف شد"
        ], 200);
    }


    public function unit_restore($id)
    {
        $unit = ProductOption::withTrashed()->findOrFail($id);
        $unit->restore();
        return response()->json([
            "status" => "success",
            "message" => "برند بازیابی شد",
            "data" => $unit
        ], 200);
    }
    /******* units *******/


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
        try {
            $data = $request->validate(
                [
                    'title' => 'required|string|max:255',
                    'des' => 'nullable|string',
                    'tags' => 'nullable|string',
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
                    'tags.string' => 'تگ‌ها باید به صورت متن ارسال شوند',

                    'firstWarehouse.integer' => 'انبار اولیه معتبر نیست',
                    'firstPrice.numeric' => 'قیمت اولیه باید عدد باشد',
                    'sell_price.numeric' => 'قیمت فروش باید عدد باشد',

                    'tax_rate.integer' => 'نرخ مالیات معتبر نیست',
                    'min_buy.integer' => 'حداقل خرید باید عدد صحیح باشد',
                    'max_buy.integer' => 'حداکثر خرید باید عدد صحیح باشد',

                    'alert.integer' => 'موجودی هشدار باید عدد باشد',
                    'status.integer' => 'وضعیت محصول معتبر نیست',
                ]
            );


            $product = Product::create($data);
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
            // if ($request->hasFile('form')) {
            //     $formFile = $request->file('form');
            //     $extension = strtolower($formFile->getClientOriginalExtension());
            //     $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
            //     if (!in_array($extension, $allowedExtensions)) {
            //         throw new \Exception('فرمت فایل مجاز نیست');
            //     }
            //     $formName = $product->id; //. '.' . $extension;
            //     $stored = $formFile->storeAs('products/forms', $formName);
            // }
            // $product->album = $stored;
            // $product->form = $stored;
            // $product->update();
            return response()->json([
                "status" => "success",
                "data" => $product
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
