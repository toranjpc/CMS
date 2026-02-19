<?php

namespace Modules\Accounting\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Accounting\Models\Product;
use Modules\Accounting\Models\ProductItem;
use Modules\Accounting\Models\InvoiceItem;
use Modules\Accounting\Models\ProductOption;
use Modules\Accounting\Models\Invoice;
use Modules\User\Models\ExtData;
use PhpParser\Node\Stmt\TryCatch;

class ProductController extends Controller
{
    /******* categories *******/
    public function category_index()
    {
        try {
            $Options = ProductOption::where("kind", "category");

            $parentId = request('father');
            $parentId = ($parentId === '0' || $parentId === 0 || $parentId === '') ? null : $parentId;

            if ($parentId === null) {
                $Options = $Options->whereNull('f_id');
            } else {
                $Options = $Options->where('f_id', $parentId);
            }

            if (!empty(request('values'))) {
                $values = request('values');
                $Options = $Options->select('id', 'title')->where('title', 'LIKE', '%' . $values . '%');
            } else {
                $Options = $Options->select('id', 'f_id', 'title', 'option', 'created_at', 'updated_at', 'deleted_at');
                if (!empty(request('title'))) $Options = $Options->where('title', 'LIKE', '%' . request('title') . '%');
                if (!empty(request('status')) && request('status') == "deleted") $Options = $Options->onlyTrashed();
            }
            $Options = $Options->orderBy('id', 'DESC')->paginate(request("limit", 10));

            return response()->json(
                [
                    "status" => "success",
                    "items" => $Options
                ],
                200
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    "status" => "error",
                ],
                500
            );
        }
    }

    public function category_show($id = 0)
    {
        if ($id) {
            $parentId = request('father');
            $parentId = ($parentId === '0' || $parentId === 0 || $parentId === '') ? null : $parentId;
            $category = ProductOption::select("id", "title")
                ->where('kind', 'category')
                ->when($parentId === null, fn($q) => $q->whereNull('f_id'), fn($q) => $q->where('f_id', $parentId))
                ->find($id);
            if (!$category) {
                return response()->json([
                    "status" => "unsuccess",
                    "message" => "دسته‌بندی یافت نشد"
                ], 404);
            }
            return response()->json([
                "status" => "success",
                "data" => $category
            ], 200);
        }
        return response()->json([
            "status" => "unsuccess",
            "message" => "دسته‌بندی یافت نشد"
        ], 404);
    }

    public function category_store(Request $request)
    {
        try {
            $parentId = $request->input('parent_id');
            $parentId = ($parentId === '0' || $parentId === 0 || $parentId === '') ? null : $parentId;

            $data = $request->validate(
                [
                    'title' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique(ProductOption::class, 'title')
                            ->where(function ($query) use ($parentId) {
                                if ($parentId === null) {
                                    $query->whereNull('f_id');
                                } else {
                                    $query->where('f_id', $parentId);
                                }
                                $query->where('kind', 'category')
                                    ->whereNull('deleted_at');
                            }),
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

            $category = ProductOption::create([
                "f_id" => $parentId,
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
            Log::error(__METHOD__ . ' error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ثبت دسته بندی رخ داد",
            ], 500);
        }
    }

    public function category_update(Request $request, ProductOption $category)
    {
        try {
            $parentId = $request->input('parent_id', $category->f_id);
            $parentId = ($parentId === '0' || $parentId === 0 || $parentId === '') ? null : $parentId;

            $data = $request->validate(
                [
                    'title' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique(ProductOption::class, 'title')
                            ->where(function ($query) use ($parentId) {
                                if ($parentId === null) {
                                    $query->whereNull('f_id');
                                } else {
                                    $query->where('f_id', $parentId);
                                }
                                $query->where('kind', 'category')
                                    ->whereNull('deleted_at');
                            })
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

            $category->f_id = $parentId;
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
            Log::error(__METHOD__ . ' error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
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
            $Options = ProductOption::select('id', 'f_id', 'title', 'option->values as values', 'created_at', 'updated_at', 'deleted_at')
                ->where("kind", "option");

            if (!empty(request('values'))) {
                $values = request('values');
                $Options = $Options->select('id', 'title')->where('title', 'LIKE', '%' . $values . '%');
            } else {
                if (!empty(request('title'))) $Options = $Options->where('title', 'LIKE', '%' . request('title') . '%');
                if (!empty(request('status')) && request('status') == "deleted") $Options = $Options->onlyTrashed();
            }
            $Options = $Options->orderBy('id', 'DESC')->paginate(request("limit", 10));

            return response()->json(
                [
                    "status" => "success",
                    "items" => $Options
                ],
                200
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    "status" => "error",
                ],
                500
            );
        }
    }

    public function feature_show($id = 0)
    {
        if ($id) {
            $feature = ProductOption::select("id", "title")->where('kind', 'option')->find($id);
            if (!$feature) {
                return response()->json([
                    "status" => "unsuccess",
                    "message" => "ویژگی یافت نشد"
                ], 404);
            }
            return response()->json([
                "status" => "success",
                "data" => $feature
            ], 200);
        }
        return response()->json([
            "status" => "unsuccess",
            "message" => "ویژگی یافت نشد"
        ], 404);
    }

    public function feature_store(Request $request)
    {
        try {
            $parentId = $request->input('parent_id');
            $parentId = ($parentId === '0' || $parentId === 0 || $parentId === '') ? null : $parentId;

            $data = $request->validate(
                [
                    'title' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique(ProductOption::class, 'title')
                            ->where(function ($query) use ($parentId) {
                                if ($parentId === null) {
                                    $query->whereNull('f_id');
                                } else {
                                    $query->where('f_id', $parentId);
                                }
                                $query->where('kind', 'option')
                                    ->whereNull('deleted_at');
                            }),
                    ],
                    'parent_id' => ['nullable', 'integer', Rule::exists(ProductOption::class, 'id')->where(function ($query) {
                        $query->where('kind', 'option');
                    })],
                    'values' => 'nullable',
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

            $option = ["values" => $data['values'] ?? []];
            $feature = ProductOption::create([
                "f_id" => $parentId,
                "title" => $data['title'],
                "kind" => "option",
                "option" => $option,
            ]);

            $feature->values = $option["values"];

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
            Log::error(__METHOD__ . ' error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ثبت ویژگی رخ داد",
            ], 500);
        }
    }

    public function feature_update(ProductOption $feature, Request $request)
    {
        try {
            $parentId = $request->input('parent_id', $feature->f_id);
            $parentId = ($parentId === '0' || $parentId === 0 || $parentId === '') ? null : $parentId;

            $data = $request->validate(
                [
                    'title' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique(ProductOption::class, 'title')
                            ->where(function ($query) use ($parentId) {
                                if ($parentId === null) {
                                    $query->whereNull('f_id');
                                } else {
                                    $query->where('f_id', $parentId);
                                }
                                $query->where('kind', 'option')
                                    ->whereNull('deleted_at');
                            })
                            ->ignore($feature->id),
                    ],
                    'parent_id' => ['nullable', 'integer', Rule::exists(ProductOption::class, 'id')->where(function ($query) {
                        $query->where('kind', 'option');
                    })],
                    'values' => 'nullable',
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

            $feature->f_id = $parentId;
            $feature->title = $data['title'];

            $option = $feature->option;
            $option["values"] = $data['values'] ?? [];
            $feature->option = $option;

            $feature->updated_at = now();
            $feature->update();

            $feature->values = $option["values"];

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
            Log::error(__METHOD__ . ' error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
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

            if (!empty(request('values'))) {
                $values = request('values');
                $Options = $Options->select('id', 'title')->where('title', 'LIKE', '%' . $values . '%');
            } else {
                if (!empty(request('title'))) $Options = $Options->where('title', 'LIKE', '%' . request('title') . '%');
                if (!empty(request('status')) && request('status') == "deleted") $Options = $Options->onlyTrashed();
            }
            $Options = $Options->orderBy('id', 'DESC')->paginate(request("limit", 10));

            return response()->json(
                [
                    "status" => "success",
                    "items" => $Options
                ],
                200
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    "status" => "error",
                ],
                500
            );
        }
    }

    public function brand_show($id = 0)
    {
        if ($id) {
            $brand = ProductOption::select("id", "title")->where('kind', 'brand')->find($id);
            if (!$brand) {
                return response()->json([
                    "status" => "unsuccess",
                    "message" => "برند یافت نشد"
                ], 404);
            }
            return response()->json([
                "status" => "success",
                "data" => $brand
            ], 200);
        }
        return response()->json([
            "status" => "unsuccess",
            "message" => "برند یافت نشد"
        ], 404);
    }

    public function brand_store(Request $request)
    {
        try {
            $parentId = $request->input('parent_id');
            $parentId = ($parentId === '0' || $parentId === 0 || $parentId === '') ? null : $parentId;

            $data = $request->validate(
                [
                    'title' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique(ProductOption::class, 'title')
                            ->where(function ($query) use ($parentId) {
                                if ($parentId === null) {
                                    $query->whereNull('f_id');
                                } else {
                                    $query->where('f_id', $parentId);
                                }
                                $query->where('kind', 'brand')
                                    ->whereNull('deleted_at');
                            }),
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
                "f_id" => $parentId,
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
            Log::error(__METHOD__ . ' error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ثبت برند رخ داد",
            ], 500);
        }
    }

    public function brand_update(Request $request, ProductOption $brand)
    {
        try {
            $parentId = $request->input('parent_id', $brand->f_id);
            $parentId = ($parentId === '0' || $parentId === 0 || $parentId === '') ? null : $parentId;

            $data = $request->validate(
                [
                    'title' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique(ProductOption::class, 'title')
                            ->where(function ($query) use ($parentId) {
                                if ($parentId === null) {
                                    $query->whereNull('f_id');
                                } else {
                                    $query->where('f_id', $parentId);
                                }
                                $query->where('kind', 'brand')
                                    ->whereNull('deleted_at');
                            })
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

            $brand->f_id = $parentId;
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
            Log::error(__METHOD__ . ' error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
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

            if (!empty(request('values'))) {
                $values = request('values');
                $Options = $Options->select('id', 'title')->where('title', 'LIKE', '%' . $values . '%');
            } else {
                if (!empty(request('title'))) $Options = $Options->where('title', 'LIKE', '%' . request('title') . '%');
                if (!empty(request('status')) && request('status') == "deleted") $Options = $Options->onlyTrashed();
            }
            $Options = $Options->orderBy('id', 'DESC')->paginate(request("limit", 10));

            return response()->json(
                [
                    "status" => "success",
                    "items" => $Options
                ],
                200
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    "status" => "error",
                ],
                500
            );
        }
    }

    public function unit_show($id = 0)
    {
        if ($id) {
            $unit = ProductOption::select("id", "title")->where('kind', 'unit')->find($id);
            if (!$unit) {
                return response()->json([
                    "status" => "unsuccess",
                    "message" => "واحد یافت نشد"
                ], 404);
            }
            return response()->json([
                "status" => "success",
                "data" => $unit
            ], 200);
        }
        return response()->json([
            "status" => "unsuccess",
            "message" => "واحد یافت نشد"
        ], 404);
    }

    public function unit_store(Request $request)
    {
        try {
            $parentId = $request->input('parent_id');
            $parentId = ($parentId === '0' || $parentId === 0 || $parentId === '') ? null : $parentId;

            $data = $request->validate(
                [
                    'title' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique(ProductOption::class, 'title')
                            ->where(function ($query) use ($parentId) {
                                if ($parentId === null) {
                                    $query->whereNull('f_id');
                                } else {
                                    $query->where('f_id', $parentId);
                                }
                                $query->where('kind', 'unit')
                                    ->whereNull('deleted_at');
                            }),
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
                "f_id" => $parentId,
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
            Log::error(__METHOD__ . ' error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ثبت برند رخ داد",
            ], 500);
        }
    }

    public function unit_update(Request $request, ProductOption $unit)
    {
        try {
            $parentId = $request->input('parent_id', $unit->f_id);
            $parentId = ($parentId === '0' || $parentId === 0 || $parentId === '') ? null : $parentId;

            $data = $request->validate(
                [
                    'title' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique(ProductOption::class, 'title')
                            ->where(function ($query) use ($parentId) {
                                if ($parentId === null) {
                                    $query->whereNull('f_id');
                                } else {
                                    $query->where('f_id', $parentId);
                                }
                                $query->where('kind', 'unit')
                                    ->whereNull('deleted_at');
                            })
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

            $unit->f_id = $parentId;
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
            Log::error(__METHOD__ . ' error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
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

    /******* warehouses *******/
    public function warehouse_index()
    {
        try {
            $Options = ProductOption::select('id', 'f_id', 'title', 'option', 'created_at', 'updated_at', 'deleted_at')
                ->where("kind", "warehouse");

            if (!empty(request('values'))) {
                $values = request('values');
                $Options = $Options->select('id', 'title')->where('title', 'LIKE', '%' . $values . '%');
            } else {
                if (!empty(request('title'))) $Options = $Options->where('title', 'LIKE', '%' . request('title') . '%');
                if (!empty(request('status')) && request('status') == "deleted") $Options = $Options->onlyTrashed();
            }
            $Options = $Options->orderBy('id', 'DESC')->paginate(request("limit", 10));

            return response()->json(
                [
                    "status" => "success",
                    "items" => $Options
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

    public function warehouse_show($id = 0)
    {
        if ($id) {
            $warehouse = ProductOption::select("id", "title")->where('kind', 'warehouse')->find($id);
            if (!$warehouse) {
                return response()->json([
                    "status" => "unsuccess",
                    "message" => "انبار یافت نشد"
                ], 201);
            }
            return response()->json([
                "status" => "success",
                "data" => $warehouse
            ], 200);
        }
        return response()->json([
            "status" => "unsuccess",
            "message" => "انبار یافت نشد"
        ], 201);
    }


    public function warehouse_store(Request $request)
    {
        try {
            $parentId = $request->input('parent_id');
            $parentId = ($parentId === '0' || $parentId === 0 || $parentId === '') ? null : $parentId;

            $data = $request->validate(
                [
                    'title' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique(ProductOption::class, 'title')
                            ->where(function ($query) use ($parentId) {
                                if ($parentId === null) {
                                    $query->whereNull('f_id');
                                } else {
                                    $query->where('f_id', $parentId);
                                }
                                $query->where('kind', 'warehouse')
                                    ->whereNull('deleted_at');
                            }),
                    ],
                    'parent_id' => ['nullable', 'integer', Rule::exists(ProductOption::class, 'id')->where(function ($query) {
                        $query->where('kind', 'warehouse');
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

            $warehouse = ProductOption::create([
                "f_id" => $parentId,
                "title" => $data['title'],
                "kind" => "warehouse",
                "option" => [],
            ]);


            if ($request->hasFile('icon')) {
                $iconFile = $request->file('icon');
                $extension = strtolower($iconFile->getClientOriginalExtension());
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
                if (!in_array($extension, $allowedExtensions)) {
                    throw new \Exception('فرمت فایل مجاز نیست');
                }
                $iconName = $warehouse->id; //. '.' . $extension;
                $stored = $iconFile->storeAs('products/warehouses', $iconName);
            }

            return response()->json([
                "status" => "success",
                "data" => $warehouse
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                "status" => "validation_error",
                "errors" => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ثبت برند رخ داد",
            ], 500);
        }
    }

    public function warehouse_update(Request $request, ProductOption $warehouse)
    {
        try {
            $parentId = $request->input('parent_id', $warehouse->f_id);
            $parentId = ($parentId === '0' || $parentId === 0 || $parentId === '') ? null : $parentId;

            $data = $request->validate(
                [
                    'title' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique(ProductOption::class, 'title')
                            ->where(function ($query) use ($parentId) {
                                if ($parentId === null) {
                                    $query->whereNull('f_id');
                                } else {
                                    $query->where('f_id', $parentId);
                                }
                                $query->where('kind', 'warehouse')
                                    ->whereNull('deleted_at');
                            })
                            ->ignore($warehouse->id),
                    ],
                    'parent_id' => ['nullable', 'integer', Rule::exists(ProductOption::class, 'id')->where(function ($query) {
                        $query->where('kind', 'warehouse');
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

            $warehouse->f_id = $parentId;
            $warehouse->title = $data['title'];
            $warehouse->updated_at = now();
            $warehouse->update();

            if ($request->hasFile('icon')) {
                $oldIconPath = storage_path('app/public/products/warehouses/' . $warehouse->id . '.*');
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
                $iconName = $warehouse->id; //. '.' . $extension;
                $stored = $iconFile->storeAs('products/warehouses', $iconName);
            }


            return response()->json([
                "status" => "success",
                "data" => $warehouse
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                "status" => "validation_error",
                "errors" => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ویرایش برند رخ داد",
            ], 500);
        }
    }


    public function warehouse_destroy(ProductOption $warehouse)
    {
        $warehouse->delete();
        return response()->json([
            "status" => "success",
            "message" => "برند با موفقیت حذف شد"
        ], 200);
    }

    public function warehouse_force_destroy($id)
    {
        $warehouse = ProductOption::withTrashed()->findOrFail($id);

        // پاک کردن فایل آیکون
        $oldIconPath = storage_path('app/public/products/warehouses/' . $warehouse->id . '.*');
        $oldFiles = glob($oldIconPath);
        foreach ($oldFiles as $oldFile) {
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        $warehouse->forceDelete();
        return response()->json([
            "status" => "success",
            "message" => "برند به صورت دائمی حذف شد"
        ], 200);
    }

    public function warehouse_restore($id)
    {
        $warehouse = ProductOption::withTrashed()->findOrFail($id);
        $warehouse->restore();
        return response()->json([
            "status" => "success",
            "message" => "برند بازیابی شد",
            "data" => $warehouse
        ], 200);
    }
    /******* warehouses *******/

    /******* banks *******/
    public function bank_index()
    {
        try {
            $Options = ProductOption::select('id', 'f_id', 'title', 'option', 'des', 'created_at', 'updated_at', 'deleted_at')
                ->where("kind", "bank");

            if (!empty(request('values'))) {
                $values = request('values');
                $Options = $Options->select('id', 'title')->where('title', 'LIKE', '%' . $values . '%');
            } else {
                if (!empty(request('title'))) $Options = $Options->where('title', 'LIKE', '%' . request('title') . '%');
                if (!empty(request('status')) && request('status') == "deleted") $Options = $Options->onlyTrashed();
            }
            $Options = $Options->orderBy('id', 'DESC')->paginate(request("limit", 10));

            return response()->json(
                [
                    "status" => "success",
                    "items" => $Options
                ],
                200
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    "status" => "error",
                ],
                500
            );
        }
    }

    public function bank_show($id = 0)
    {
        if ($id) {
            $bank = ProductOption::select("id", "title", "option", "des")->where('kind', 'bank')->find($id);
            if (!$bank) {
                return response()->json([
                    "status" => "unsuccess",
                    "message" => "بانک یافت نشد"
                ], 404);
            }
            return response()->json([
                "status" => "success",
                "data" => $bank
            ], 200);
        }
        return response()->json([
            "status" => "unsuccess",
            "message" => "بانک یافت نشد"
        ], 404);
    }

    public function bank_store(Request $request)
    {
        try {
            $data = $request->validate(
                [
                    'title' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique(ProductOption::class, 'title')
                            ->where(function ($query) {
                                $query->where('kind', 'bank')
                                    ->whereNull('deleted_at');
                            }),
                    ],
                    'sheba' => 'nullable|string|max:26',
                    'account_number' => 'nullable|string|max:255',
                    'card_number' => 'nullable|string|max:16',
                    'has_pos' => 'nullable|boolean',
                    'initial_balance' => 'nullable|numeric',
                ],
                [
                    'title.required' => 'نام بانک الزامی است',
                    'title.string' => 'نام بانک باید به‌صورت متن باشد',
                    'title.max' => 'نام بانک نباید بیشتر از ۲۵۵ کاراکتر باشد',
                    'title.unique'   => 'این نام بانک قبلاً ثبت شده است',
                    'sheba.string' => 'شماره شبا باید متن باشد',
                    'sheba.max' => 'شماره شبا نباید بیشتر از ۲۶ کاراکتر باشد',
                    'account_number.string' => 'شماره حساب باید متن باشد',
                    'account_number.max' => 'شماره حساب نباید بیشتر از ۲۵۵ کاراکتر باشد',
                    'card_number.string' => 'شماره کارت باید متن باشد',
                    'card_number.max' => 'شماره کارت نباید بیشتر از ۱۶ کاراکتر باشد',
                    'has_pos.boolean' => 'فیلد کارتخوان باید مقدار بولی داشته باشد',
                    'initial_balance.numeric' => 'موجودی اولیه باید عدد باشد',
                ]
            );

            $option = [
                'sheba' => $data['sheba'] ?? null,
                'account_number' => $data['account_number'] ?? null,
                'card_number' => $data['card_number'] ?? null,
                'has_pos' => $data['has_pos'] ?? false,
            ];

            $bank = ProductOption::create([
                "f_id" => null,
                "title" => $data['title'],
                "kind" => "bank",
                "option" => $option,
                "des" => $data['initial_balance'] ?? null,
            ]);

            return response()->json([
                "status" => "success",
                "data" => $bank
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                "status" => "validation_error",
                "errors" => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ثبت بانک رخ داد",
            ], 500);
        }
    }

    public function bank_update(Request $request, ProductOption $bank)
    {
        try {
            $data = $request->validate(
                [
                    'title' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique(ProductOption::class, 'title')
                            ->where(function ($query) {
                                $query->where('kind', 'bank')
                                    ->whereNull('deleted_at');
                            })
                            ->ignore($bank->id),
                    ],
                    'sheba' => 'nullable|string|max:26',
                    'account_number' => 'nullable|string|max:255',
                    'card_number' => 'nullable|string|max:16',
                    'has_pos' => 'nullable|boolean',
                    'initial_balance' => 'nullable|numeric',
                ],
                [
                    'title.required' => 'نام بانک الزامی است',
                    'title.string' => 'نام بانک باید به‌صورت متن باشد',
                    'title.max' => 'نام بانک نباید بیشتر از ۲۵۵ کاراکتر باشد',
                    'title.unique'   => 'این نام بانک قبلاً ثبت شده است',
                    'sheba.string' => 'شماره شبا باید متن باشد',
                    'sheba.max' => 'شماره شبا نباید بیشتر از ۲۶ کاراکتر باشد',
                    'account_number.string' => 'شماره حساب باید متن باشد',
                    'account_number.max' => 'شماره حساب نباید بیشتر از ۲۵۵ کاراکتر باشد',
                    'card_number.string' => 'شماره کارت باید متن باشد',
                    'card_number.max' => 'شماره کارت نباید بیشتر از ۱۶ کاراکتر باشد',
                    'has_pos.boolean' => 'فیلد کارتخوان باید مقدار بولی داشته باشد',
                    'initial_balance.numeric' => 'موجودی اولیه باید عدد باشد',
                ]
            );

            $option = $bank->option ?? [];
            $option['sheba'] = $data['sheba'] ?? null;
            $option['account_number'] = $data['account_number'] ?? null;
            $option['card_number'] = $data['card_number'] ?? null;
            $option['has_pos'] = $data['has_pos'] ?? false;

            $bank->title = $data['title'];
            $bank->option = $option;
            $bank->des = $data['initial_balance'] ?? null;
            $bank->updated_at = now();
            $bank->update();

            return response()->json([
                "status" => "success",
                "data" => $bank
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                "status" => "validation_error",
                "errors" => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ویرایش بانک رخ داد",
            ], 500);
        }
    }

    public function bank_destroy(ProductOption $bank)
    {
        $bank->delete();
        return response()->json([
            "status" => "success",
            "message" => "بانک با موفقیت حذف شد"
        ], 200);
    }

    public function bank_force_destroy($id)
    {
        $bank = ProductOption::withTrashed()->findOrFail($id);
        $bank->forceDelete();
        return response()->json([
            "status" => "success",
            "message" => "بانک به صورت دائمی حذف شد"
        ], 200);
    }

    public function bank_restore($id)
    {
        $bank = ProductOption::withTrashed()->findOrFail($id);
        $bank->restore();
        return response()->json([
            "status" => "success",
            "message" => "بانک بازیابی شد",
            "data" => $bank
        ], 200);
    }
    /******* banks *******/



    public function lastid()
    {
        return Invoice::max("id") + 1;
    }
    public function index()
    {
        $products = Product::with([
            'variants',
            // 'brand:id,title',
            // 'unit:id,title',
            // 'warehouse:id,title'
        ]); // Only main products, not variants

        if (!empty(request('values'))) {
            $values = request('values');
            $products = $products->select('id', 'title', 'barcode', 'status');
            $products = $products->where(function ($q) use ($values) {
                $q->where('title', 'LIKE', '%' . $values . '%')
                    ->orWhere('barcode', 'LIKE', '%' . $values . '%');
            });
        } else {
            if (!empty(request('trashed')) && request('trashed') == "true") $products = $products->onlyTrashed();
            if (!empty(request('title'))) $products = $products->where('title', 'LIKE', '%' . request('title') . '%');
            if (!empty(request('barcode'))) $products = $products->where('barcode', 'LIKE', '%' . request('barcode') . '%');
            if (!empty(request('status'))) $products = $products->where('status', request('status'));

            // if (!empty(request('brand_id'))) $products = $products->where('brand_id', request('brand_id'));
            // if (!empty(request('category_id'))) $products = $products->where('category_id', request('category_id'));
            // if (!empty(request('feature_id'))) $products = $products->where('feature_id', request('feature_id'));
            // if (!empty(request('unit_id'))) $products = $products->where('unit_id', request('unit_id'));
            // if (!empty(request('price'))) $products = $products->where('price', request('price'));
            // if (!empty(request('price_from'))) $products = $products->where('price', '>=', request('price_from'));
            // if (!empty(request('price_to'))) $products = $products->where('price', '<=', request('price_to'));

            $products = $products->select("*");
        }

        $products = $products->orderByDesc('id')->paginate(request("limit", 10));

        // موجودی لیست محصول: موجودی اول دوره + جمع خرید - جمع فروش
        $productIds = collect($products->items())->pluck('id')->filter()->values()->toArray();
        if (!empty($productIds)) {
            $initialStocks = ProductItem::query()
                ->selectRaw('f_id as product_id, COALESCE(SUM(firstWarehouse), 0) as initial_stock')
                ->whereIn('f_id', $productIds)
                ->groupBy('f_id')
                ->pluck('initial_stock', 'product_id');

            $buyQuantities = InvoiceItem::query()
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->selectRaw('product_id, COALESCE(SUM(quantity), 0) as total_qty')
                ->whereIn('product_id', $productIds)
                ->where('invoices.type', 'buy')
                ->whereNull('invoices.deleted_at')
                ->groupBy('product_id')
                ->pluck('total_qty', 'product_id');

            $sellQuantities = InvoiceItem::query()
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->selectRaw('product_id, COALESCE(SUM(quantity), 0) as total_qty')
                ->whereIn('product_id', $productIds)
                ->where('invoices.type', 'sell')
                ->whereNull('invoices.deleted_at')
                ->groupBy('product_id')
                ->pluck('total_qty', 'product_id');

            $products->getCollection()->transform(function ($product) use ($initialStocks, $buyQuantities, $sellQuantities) {
                $initialStock = (int) ($initialStocks[$product->id] ?? 0);
                $buyQty = (int) ($buyQuantities[$product->id] ?? 0);
                $sellQty = (int) ($sellQuantities[$product->id] ?? 0);

                $stockBalance = $initialStock + $buyQty - $sellQty;
                // Keep backward compatibility for current front and also expose a clear key.
                $product->firstWarehouse = $stockBalance;
                $product->stock_balance = $stockBalance;

                return $product;
            });
        }

        return response()->json([
            "status" => "success",
            "items" => $products
        ], 200);
    }

    public function searchForInvoice()
    {
        $productItems = ProductItem::with([
            'mainProduct' => function($query) {
                $query->select('id', 'title', 'barcode');
            }
        ]);

        if (!empty(request('values'))) {
            $values = request('values');
            $productItems = $productItems->where(function ($q) use ($values) {
                $q->where('title', 'LIKE', '%' . $values . '%')
                    ->orWhereHas('mainProduct', function($pq) use ($values) {
                        $pq->where('title', 'LIKE', '%' . $values . '%')
                            ->orWhere('barcode', 'LIKE', '%' . $values . '%');
                    });
            });
        }

        $productItems = $productItems->orderByDesc('id')->paginate(request("limit", 10));

        // Add last used prices from invoices
        $productItemIds = $productItems->pluck('id')->toArray();
        $invoiceType = request('invoice_type', 'sell');

        if (!empty($productItemIds)) {
            $lastPrices = DB::table('invoice_items')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->whereIn('invoice_items.product_item_id', $productItemIds)
                ->where('invoices.type', $invoiceType)
                ->select('invoice_items.product_item_id', 'invoice_items.unit_price')
                ->orderBy('invoices.date', 'desc')
                ->get()
                ->groupBy('product_item_id')
                ->map(function($items) {
                    return $items->first()->unit_price;
                });

            // Stock per variant: initial stock + buy quantities - sell quantities
            $buyQuantities = DB::table('invoice_items')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->whereIn('product_item_id', $productItemIds)
                ->where('invoices.type', 'buy')
                ->whereNull('invoices.deleted_at')
                ->selectRaw('product_item_id, COALESCE(SUM(quantity), 0) as total_qty')
                ->groupBy('product_item_id')
                ->pluck('total_qty', 'product_item_id');

            $sellQuantities = DB::table('invoice_items')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->whereIn('product_item_id', $productItemIds)
                ->where('invoices.type', 'sell')
                ->whereNull('invoices.deleted_at')
                ->selectRaw('product_item_id, COALESCE(SUM(quantity), 0) as total_qty')
                ->groupBy('product_item_id')
                ->pluck('total_qty', 'product_item_id');

            // Add last prices and display names to product items
            $productItems->getCollection()->transform(function($item) use ($lastPrices, $invoiceType, $buyQuantities, $sellQuantities) {
                $item->last_used_price = $lastPrices->get($item->id);

                // Add default price based on invoice type
                if ($invoiceType === 'sell') {
                    $item->default_price = $item->sell_price;
                } else {
                    $item->default_price = $item->firstPrice ?? $item->sell_price;
                }

                $initialStock = (int) ($item->firstWarehouse ?? 0);
                $buyQty = (int) ($buyQuantities[$item->id] ?? 0);
                $sellQty = (int) ($sellQuantities[$item->id] ?? 0);
                $stockBalance = $initialStock + $buyQty - $sellQty;
                $item->current_stock = $stockBalance;
                $item->stock_balance = $stockBalance;

                // Create display name: always "Product Name - Variant Name"
                $mainProductTitle = $item->mainProduct ? $item->mainProduct->title : '';
                $variantTitle = $item->title;

                $item->display_name = $mainProductTitle . ' - ' . $variantTitle;

                return $item;
            });
        }

        return response()->json([
            "status" => "success",
            "items" => $productItems
        ], 200);
    }

    public function show($id)
    {
        $Product = Product::with([
                'variants' => function($query) {
                    $query->with('convertUnitRelation');
                },
                'categores',
                'option',
                'brand',
                'unit',
                'warehouse'
            ])
            ->where('id', $id)
            ->withTrashed()
            ->first();


        return response()->json([
            "status" => "success",
            "data" => $Product
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate(
                [
                    // Main product fields
                    'status' => 'nullable|integer',
                    'title' => 'required|string|max:255',
                    'barcode' => 'required|string|max:255|unique:products,barcode',
                    'tax_rate' => 'nullable|integer',
                    'min_buy' => 'nullable|integer',
                    'max_buy' => 'nullable|integer',
                    'alert' => 'nullable|integer',
                    'des' => 'nullable|string',
                    'selectedUnit' => 'nullable|integer|exists:product_options,id',
                    'selectedBrand' => 'nullable|integer|exists:product_options,id',
                    'selectedWarehouse' => 'nullable|integer|exists:product_options,id',

                    // Categories
                    'Categores' => 'nullable|array',
                    'Categores.*' => 'integer|exists:product_options,id',

                    // Variants
                    'variants' => 'required|array|min:1',
                    'variants.*.title' => 'required|string|max:255',
                    'variants.*.firstWarehouse' => 'nullable|integer',
                    'variants.*.firstPrice' => 'nullable|decimal:0,2',
                    'variants.*.current_stock' => 'nullable|integer',
                    'variants.*.sell_price' => 'nullable|decimal:0,2',
                    'variants.*.status' => 'nullable|integer',
                    'variants.*.convertUnit' => 'nullable|boolean',
                    'variants.*.UnitNumber' => 'nullable|integer',
                    'variants.*.selectConvertUnit' => 'nullable|integer|exists:product_options,id',

                    // Form data
                    'form' => 'nullable|string',

                    // Images
                    'album' => 'nullable|string',
                    'images' => 'nullable|array',
                    'images.*' => 'file|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ],
                [
                    'title.required' => 'عنوان محصول الزامی است',
                    'title.string' => 'عنوان محصول باید متن باشد',
                    'title.max' => 'عنوان محصول نباید بیشتر از ۲۵۵ کاراکتر باشد',

                    'barcode.required' => 'بارکد محصول الزامی است',
                    'barcode.string' => 'بارکد محصول باید متن باشد',
                    'barcode.max' => 'بارکد محصول نباید بیشتر از ۲۵۵ کاراکتر باشد',
                    'barcode.unique' => 'این بارکد قبلاً ثبت شده است',

                    'status.integer' => 'وضعیت محصول معتبر نیست',
                    'tax_rate.integer' => 'نرخ مالیات معتبر نیست',
                    'min_buy.integer' => 'حداقل خرید معتبر نیست',
                    'max_buy.integer' => 'حداکثر خرید معتبر نیست',
                    'alert.integer' => 'موجودی هشدار معتبر نیست',
                    'des.string' => 'توضیحات باید متن باشد',

                    'selectedUnit.integer' => 'واحد انتخاب شده معتبر نیست',
                    'selectedUnit.exists' => 'واحد انتخاب شده یافت نشد',
                    'selectedBrand.integer' => 'برند انتخاب شده معتبر نیست',
                    'selectedBrand.exists' => 'برند انتخاب شده یافت نشد',
                    'selectedWarehouse.integer' => 'انبار انتخاب شده معتبر نیست',
                    'selectedWarehouse.exists' => 'انبار انتخاب شده یافت نشد',

                    'Categores.array' => 'دسته‌بندی‌ها باید به صورت آرایه ارسال شوند',
                    'Categores.*.integer' => 'شناسه دسته‌بندی معتبر نیست',
                    'Categores.*.exists' => 'دسته‌بندی انتخاب شده یافت نشد',

                    'variants.required' => 'حداقل یک تنوع محصول الزامی است',
                    'variants.array' => 'تنوع‌ها باید به صورت آرایه ارسال شوند',
                    'variants.min' => 'حداقل یک تنوع باید تعریف شود',
                    'variants.*.title.required' => 'عنوان تنوع الزامی است',
                    'variants.*.title.string' => 'عنوان تنوع باید متن باشد',
                    'variants.*.title.max' => 'عنوان تنوع نباید بیشتر از ۲۵۵ کاراکتر باشد',

                    'variants.*.firstWarehouse.integer' => 'انبار اولیه باید عدد صحیح باشد',
                    'variants.*.firstPrice.decimal' => 'قیمت اولیه باید عدد باشد',
                    'variants.*.current_stock.integer' => 'موجودی فعلی باید عدد صحیح باشد',
                    'variants.*.sell_price.decimal' => 'قیمت فروش باید عدد باشد',
                    'variants.*.status.integer' => 'وضعیت تنوع معتبر نیست',
                    'variants.*.convertUnit.boolean' => 'فیلد تبدیل واحد باید مقدار بولی داشته باشد',
                    'variants.*.UnitNumber.integer' => 'تعداد واحد باید عدد صحیح باشد',
                    'variants.*.selectConvertUnit.integer' => 'واحد تبدیل انتخاب شده معتبر نیست',
                    'variants.*.selectConvertUnit.exists' => 'واحد تبدیل انتخاب شده یافت نشد',

                    'form.string' => 'فرم باید به صورت متن JSON ارسال شود',

                    'album.string' => 'آلبوم باید به صورت متن JSON ارسال شود',
                    'images.array' => 'تصاویر باید به صورت آرایه ارسال شوند',
                    'images.*.file' => 'هر تصویر باید فایل معتبر باشد',
                    'images.*.mimes' => 'فرمت تصویر باید jpeg، png، jpg، gif یا svg باشد',
                    'images.*.max' => 'حجم هر تصویر نباید بیشتر از ۲ مگابایت باشد',
                ]
            );

            // Handle images
            $albumData = null;

            // اول album JSON رو پردازش کن
            if ($request->has('album')) {
                $albumPayload = json_decode($request->input('album'), true);
                $albumData = $this->processAlbumPayload($albumPayload, $albumData);
            }

            // بعد تصاویر جدید رو آپلود کن
            if ($request->hasFile('images')) {
                $albumData = $this->handleImageUploads($request->file('images'), $albumData);
            }

            // Create main product
            $mainProduct = Product::create([
                'user_id' => Auth::id() ?? 1,
                'title' => $data['title'],
                'barcode' => $data['barcode'],
                'album' => $albumData,
                'tags' => null,
                'des' => $data['des'] ?? null,
                'form' => isset($data['form']) ? json_decode($data['form'], true) : null,
                'tax_rate' => $data['tax_rate'] ?? 10,
                'min_buy' => $data['min_buy'] ?? 0,
                'max_buy' => $data['max_buy'] ?? 0,
                'alert' => $data['alert'] ?? 0,
                'status' => $data['status'] ?? 1,
            ]);

            // Create product variants
            foreach ($data['variants'] as $variant) {
                ProductItem::create([
                    'user_id' => Auth::id() ?? 1,
                    'f_id' => $mainProduct->id,
                    'title' => $variant['title'] ?: $data['title'],
                    'firstWarehouse' => $variant['firstWarehouse'] ?? 0,
                    'current_stock' => $variant['current_stock'] ?? 0,
                    'firstPrice' => $variant['firstPrice'] ?? 0,
                    'sell_price' => $variant['sell_price'] ?? 0,
                    'status' => $variant['status'] ?? 1,
                    'convertUnit' => $variant['convertUnit'] ?? false,
                    'UnitNumber' => $variant['UnitNumber'] ?? 0,
                    'selectConvertUnit' => $variant['selectConvertUnit'] ?? null,
                ]);
            }

            // Create relationships using ExtData
            if (!empty($data['Categores'])) {
                foreach ($data['Categores'] as $categoryId) {
                    if ($categoryId > 0) {
                        ExtData::create([
                            'f_id' => $mainProduct->id,
                            'm_id' => $categoryId,
                            'kind' => 'ProductCategory',
                            'status' => 1,
                        ]);
                    }
                }
            }

            if (!empty($data['selectedUnit'])) {
                ExtData::create([
                    'f_id' => $mainProduct->id,
                    'm_id' => $data['selectedUnit'],
                    'kind' => 'ProductUnit',
                    'status' => 1,
                ]);
            }

            if (!empty($data['selectedBrand'])) {
                ExtData::create([
                    'f_id' => $mainProduct->id,
                    'm_id' => $data['selectedBrand'],
                    'kind' => 'ProductBrand',
                    'status' => 1,
                ]);
            }

            if (!empty($data['selectedWarehouse'])) {
                ExtData::create([
                    'f_id' => $mainProduct->id,
                    'm_id' => $data['selectedWarehouse'],
                    'kind' => 'ProductWarehouse',
                    'status' => 1,
                ]);
            }

            return response()->json([
                "status" => "success",
                "data" => $mainProduct->load([
                    'variants' => function($query) {
                        $query->with('convertUnitRelation');
                    },
                    'categores',
                    'option',
                    'brand',
                    'unit',
                    'warehouse'
                ]),
                "message" => "محصول با موفقیت ایجاد شد"
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                "status" => "validation_error",
                "errors" => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ثبت محصول رخ داد: " . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        // return $request;
        try {
            $data = $request->validate(
                [
                    // Main product fields
                    'status' => 'nullable|integer',
                    'title' => 'required|string|max:255',
                    'barcode' => ['required', 'string', 'max:255', Rule::unique('products', 'barcode')->ignore($id ?? null)],
                    'tax_rate' => 'nullable|integer',
                    'min_buy' => 'nullable|integer',
                    'max_buy' => 'nullable|integer',
                    'alert' => 'nullable|integer',
                    'des' => 'nullable|string',
                    'selectedUnit' => 'nullable|integer|exists:product_options,id',
                    'selectedBrand' => 'nullable|integer|exists:product_options,id',
                    'selectedWarehouse' => 'nullable|integer|exists:product_options,id',

                    // Categories
                    'Categores' => 'nullable|array',
                    'Categores.*' => 'integer|exists:product_options,id',

                    // Variants
                    'variants' => 'required|array|min:1',
                    'variants.*.id' => 'nullable|integer|exists:product_items,id',
                    'variants.*.title' => 'required|string|max:255',
                    'variants.*.firstWarehouse' => 'nullable|integer',
                    'variants.*.firstPrice' => 'nullable|decimal:0,2',
                    'variants.*.current_stock' => 'nullable|integer',
                    'variants.*.sell_price' => 'nullable|decimal:0,2',
                    'variants.*.status' => 'nullable|integer',
                    'variants.*.convertUnit' => 'nullable|boolean',
                    'variants.*.UnitNumber' => 'nullable|integer',
                    'variants.*.selectConvertUnit' => 'nullable|integer|exists:product_options,id',

                    // Form data
                    'form' => 'nullable|string',

                    // Images
                    'album' => 'nullable|string',
                    'images' => 'nullable|array',
                    'images.*' => 'file|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ],
                [
                    'title.required' => 'عنوان محصول الزامی است',
                    'title.string' => 'عنوان محصول باید متن باشد',
                    'title.max' => 'عنوان محصول نباید بیشتر از ۲۵۵ کاراکتر باشد',

                    'barcode.required' => 'بارکد محصول الزامی است',
                    'barcode.string' => 'بارکد محصول باید متن باشد',
                    'barcode.max' => 'بارکد محصول نباید بیشتر از ۲۵۵ کاراکتر باشد',
                    'barcode.unique' => 'این بارکد قبلاً ثبت شده است',

                    'status.integer' => 'وضعیت محصول معتبر نیست',
                    'tax_rate.integer' => 'نرخ مالیات معتبر نیست',
                    'min_buy.integer' => 'حداقل خرید معتبر نیست',
                    'max_buy.integer' => 'حداکثر خرید معتبر نیست',
                    'alert.integer' => 'موجودی هشدار معتبر نیست',
                    'des.string' => 'توضیحات باید متن باشد',

                    'selectedUnit.integer' => 'واحد انتخاب شده معتبر نیست',
                    'selectedUnit.exists' => 'واحد انتخاب شده یافت نشد',
                    'selectedBrand.integer' => 'برند انتخاب شده معتبر نیست',
                    'selectedBrand.exists' => 'برند انتخاب شده یافت نشد',
                    'selectedWarehouse.integer' => 'انبار انتخاب شده معتبر نیست',
                    'selectedWarehouse.exists' => 'انبار انتخاب شده یافت نشد',

                    'Categores.array' => 'دسته‌بندی‌ها باید به صورت آرایه ارسال شوند',
                    'Categores.*.integer' => 'شناسه دسته‌بندی معتبر نیست',
                    'Categores.*.exists' => 'دسته‌بندی انتخاب شده یافت نشد',

                    'variants.required' => 'حداقل یک تنوع محصول الزامی است',
                    'variants.array' => 'تنوع‌ها باید به صورت آرایه ارسال شوند',
                    'variants.min' => 'حداقل یک تنوع باید تعریف شود',
                    'variants.*.id.integer' => 'شناسه تنوع معتبر نیست',
                    'variants.*.id.exists' => 'تنوع مورد نظر یافت نشد',
                    'variants.*.title.required' => 'عنوان تنوع الزامی است',
                    'variants.*.title.string' => 'عنوان تنوع باید متن باشد',
                    'variants.*.title.max' => 'عنوان تنوع نباید بیشتر از ۲۵۵ کاراکتر باشد',

                    'variants.*.firstWarehouse.integer' => 'انبار اولیه باید عدد صحیح باشد',
                    'variants.*.firstPrice.decimal' => 'قیمت اولیه باید عدد باشد',
                    'variants.*.current_stock.integer' => 'موجودی فعلی باید عدد صحیح باشد',
                    'variants.*.sell_price.decimal' => 'قیمت فروش باید عدد باشد',
                    'variants.*.status.integer' => 'وضعیت تنوع معتبر نیست',
                    'variants.*.convertUnit.boolean' => 'فیلد تبدیل واحد باید مقدار بولی داشته باشد',
                    'variants.*.UnitNumber.integer' => 'تعداد واحد باید عدد صحیح باشد',
                    'variants.*.selectConvertUnit.integer' => 'واحد تبدیل انتخاب شده معتبر نیست',
                    'variants.*.selectConvertUnit.exists' => 'واحد تبدیل انتخاب شده یافت نشد',

                    'form.string' => 'فرم باید به صورت متن JSON ارسال شود',

                    'album.string' => 'آلبوم باید به صورت متن JSON ارسال شود',
                    'images.array' => 'تصاویر باید به صورت آرایه ارسال شوند',
                    'images.*.file' => 'هر تصویر باید فایل معتبر باشد',
                    'images.*.mimes' => 'فرمت تصویر باید jpeg، png، jpg، gif یا svg باشد',
                    'images.*.max' => 'حجم هر تصویر نباید بیشتر از ۲ مگابایت باشد',
                ]
            );

            // Find the product
            $mainProduct = Product::findOrFail($id);

            // Handle images
            $albumData = $mainProduct->album; // Keep existing album

            // اول album JSON رو پردازش کن (حذف‌ها و تصاویر موجود)
            if ($request->has('album')) {
                $albumPayload = json_decode($request->input('album'), true);
                $albumData = $this->processAlbumPayload($albumPayload, $albumData);
            }

            // بعد تصاویر جدید رو آپلود کن (اضافه به آلبوم)
            if ($request->hasFile('images')) {
                $albumData = $this->handleImageUploads($request->file('images'), $albumData);
            }

            // Update main product
            $mainProduct->update([
                'title' => $data['title'],
                'barcode' => $data['barcode'],
                'album' => $albumData,
                'des' => $data['des'] ?? null,
                'form' => isset($data['form']) ? json_decode($data['form'], true) : $mainProduct->form,
                'tax_rate' => $data['tax_rate'] ?? 10,
                'min_buy' => $data['min_buy'] ?? 0,
                'max_buy' => $data['max_buy'] ?? 0,
                'alert' => $data['alert'] ?? 0,
                'status' => $data['status'] ?? 1,
            ]);

            // مدیریت تنوع‌ها: آپدیت موجود، ساخت جدید، حذف اضافی
            $sentVariantIds = collect($data['variants'])
                ->pluck('id')
                ->filter()
                ->toArray();

            // حذف تنوع‌هایی که دیگه ارسال نشدن
            ProductItem::where('f_id', $mainProduct->id)
                ->whereNotIn('id', $sentVariantIds)
                ->delete();

            foreach ($data['variants'] as $variant) {
                $variantData = [
                    'user_id' => Auth::id() ?? 1,
                    'f_id' => $mainProduct->id,
                    'title' => $variant['title'] ?: $data['title'],
                    'firstWarehouse' => $variant['firstWarehouse'] ?? 0,
                    'current_stock' => $variant['current_stock'] ?? 0,
                    'firstPrice' => $variant['firstPrice'] ?? 0,
                    'sell_price' => $variant['sell_price'] ?? 0,
                    'status' => $variant['status'] ?? 1,
                    'convertUnit' => $variant['convertUnit'] ?? false,
                    'UnitNumber' => $variant['UnitNumber'] ?? 0,
                    'selectConvertUnit' => $variant['selectConvertUnit'] ?? null,
                ];

                if (!empty($variant['id'])) {
                    // آپدیت تنوع موجود
                    ProductItem::where('id', $variant['id'])->update($variantData);
                } else {
                    // ساخت تنوع جدید
                    ProductItem::create($variantData);
                }
            }

            // Update relationships
            // Delete existing relationships
            ExtData::where('f_id', $mainProduct->id)
                ->whereIn('kind', ['ProductCategory', 'ProductUnit', 'ProductBrand', 'ProductWarehouse'])
                ->delete();

            // Create new category relationships
            if (!empty($data['Categores'])) {
                foreach ($data['Categores'] as $categoryId) {
                    if ($categoryId > 0) {
                        ExtData::create([
                            'f_id' => $mainProduct->id,
                            'm_id' => $categoryId,
                            'kind' => 'ProductCategory',
                            'status' => 1,
                        ]);
                    }
                }
            }

            // Create unit relationship
            if (!empty($data['selectedUnit'])) {
                ExtData::create([
                    'f_id' => $mainProduct->id,
                    'm_id' => $data['selectedUnit'],
                    'kind' => 'ProductUnit',
                    'status' => 1,
                ]);
            }

            // Create brand relationship
            if (!empty($data['selectedBrand'])) {
                ExtData::create([
                    'f_id' => $mainProduct->id,
                    'm_id' => $data['selectedBrand'],
                    'kind' => 'ProductBrand',
                    'status' => 1,
                ]);
            }

            // Create warehouse relationship
            if (!empty($data['selectedWarehouse'])) {
                ExtData::create([
                    'f_id' => $mainProduct->id,
                    'm_id' => $data['selectedWarehouse'],
                    'kind' => 'ProductWarehouse',
                    'status' => 1,
                ]);
            }

            return response()->json([
                "status" => "success",
                "data" => $mainProduct->load([
                    'variants' => function($query) {
                        $query->with('convertUnitRelation');
                    },
                    'categores',
                    'option',
                    'brand',
                    'unit',
                    'warehouse'
                ]),
                "message" => "محصول با موفقیت ویرایش شد"
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                "status" => "validation_error",
                "errors" => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ویرایش محصول رخ داد: " . $e->getMessage(),
            ], 500);
        }
    }

    private function handleImageUploads(array $images, array $existingAlbum = null)
    {
        $album = $existingAlbum ?: [];

        foreach ($images as $image) {
            $extension = strtolower($image->getClientOriginalExtension());
            $filename = uniqid() . '.' . $extension;
            $image->storeAs('products', $filename, 'public');

            $album[] = [
                'url' => asset('storage/products/' . $filename),
                'thumb' => asset('storage/products/' . $filename),
            ];
        }

        return $album;
    }

    private function processAlbumPayload(array $albumPayload, array $existingAlbum = null)
    {
        $album = [];

        // نگه داشتن تصاویر موجود
        if (isset($albumPayload['existing'])) {
            foreach ($albumPayload['existing'] as $img) {
                $album[] = [
                    'url' => $img['url'],
                    'thumb' => $img['thumb'] ?? $img['url'],
                ];
            }
        }

        // حذف فایل تصاویر حذف شده از storage
        if (isset($albumPayload['removed'])) {
            foreach ($albumPayload['removed'] as $removedImage) {
                $filename = $removedImage['filename'] ?? basename($removedImage['url'] ?? '');
                if ($filename) {
                    Storage::disk('public')->delete('products/' . $filename);
                }
            }
        }

        return $album;
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json([
            "status" => "success",
            "message" => "محصول با موفقیت حذف شد"
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
