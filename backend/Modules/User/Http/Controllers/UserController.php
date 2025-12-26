<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\User\Models\User;
use Modules\User\Models\Option;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where("")->where();

        $users = $users->paginate();

        return response()->json(
            [
                "status" => "",
                "data" => $users
            ]
        );
    }

    public function show($id)
    {
        return response()->json(User::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);
        return response()->json($user, 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name'     => 'nullable|string|max:255',
            'email'    => 'nullable|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $user->update($data);
        return response()->json($user);
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(null, 204);
    }

    /******* categories *******/
    public function category_index()
    {
        try {
            $Options = Option::select('id', 'f_id', 'title', 'option', 'created_at', 'deleted_at')
                ->where("kind", "category");

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
                ],
                [
                    'title.required' => 'عنوان دسته بندی الزامی است',
                    'title.string' => 'عنوان دسته بندی باید به‌صورت متن باشد',
                    'title.max' => 'عنوان دسته بندی نباید بیشتر از ۲۵۵ کاراکتر باشد',
                    'title.unique'   => 'این عنوان قبلاً ثبت شده است',
                ]
            );

            $category = Option::create([
                "title" => $data['title'],
                "kind" => "category",
                "option" => [],
            ]);

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
                ],
                [
                    'title.required' => 'عنوان دسته بندی الزامی است',
                    'title.string' => 'عنوان دسته بندی باید به‌صورت متن باشد',
                    'title.max' => 'عنوان دسته بندی نباید بیشتر از ۲۵۵ کاراکتر باشد',
                    'title.unique'   => 'این عنوان قبلاً ثبت شده است',
                ]
            );

            $category->title = $data['title'];
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
        $category->delete();
        return response()->json([
            "status" => "success",
            "message" => "دسته بندی با موفقیت حذف شد"
        ], 200);
    }

    public function category_force_destroy($id)
    {
        $category = Option::withTrashed()->findOrFail($id);

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


    /******* Jobs *******/
    public function job_index()
    {
        try {
            $Options = Option::select('id', 'title', 'option', 'created_at', 'deleted_at')
                ->where("kind", "job");
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

            if (!empty(request('withPers'))) {
                $pers1 = collect(app('router')->getRoutes())
                    ->filter(fn($route) => in_array('checkPermission', $route->gatherMiddleware()))
                    ->map(fn($route) => $route->getName())
                    ->filter();
                $pers2 = ['*'];
                $pers = $pers1->merge($pers2)->values();
            } else {
                $pers = collect([]);
            }

            return response()->json(
                [
                    "status" => "success",
                    "pers" => $pers,
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

    public function job_show($id)
    {
        return response()->json(Option::findOrFail($id));
    }

    public function job_store(Request $request)
    {
        try {
            $data = $request->validate(
                [
                    // 'title' => 'required|string|max:255|unique:options,title,NULL,id,f_id,0,kind,job,deleted_at,NULL',
                    'title' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('options', 'title')
                            ->where('f_id', 0)
                            ->where('kind', 'job')
                            ->whereNull('deleted_at'),
                    ],
                    'permissions' => 'nullable|array',
                ],
                [
                    'title.required' => 'عنوان نقش الزامی است',
                    'title.string' => 'عنوان نقش باید به‌صورت متن باشد',
                    'title.max' => 'عنوان نقش نباید بیشتر از ۲۵۵ کاراکتر باشد',
                    'title.unique'   => 'این عنوان قبلاً ثبت شده است',

                    'permissions.required' => 'انتخاب حداقل یک دسترسی الزامی است',
                    'permissions.array' => 'فرمت دسترسی‌ها نامعتبر است',
                ]
            );

            $job = Option::create([
                "f_id" => 0,
                "title" => $data['title'],
                "option" => [
                    "permissions" => $data['permissions'] ?? [],
                ],
                "kind" => "job",
            ]);

            return response()->json([
                "status" => "success",
                "data" => $job
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                "status" => "validation_error",
                "errors" => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ثبت نقش رخ داد",
            ], 500);
        }
    }

    public function job_update(Request $request, Option $job)
    {
        try {
            $data = $request->validate(
                [
                    // 'title' => 'required|string|max:255|unique:options,title,' . $job->id . ',id,f_id,0,kind,job,deleted_at,NULL',
                    'title' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('options', 'title')
                            ->where('f_id', 0)
                            ->where('kind', 'job')
                            ->whereNull('deleted_at')
                            ->ignore($job->id),
                    ],
                    'permissions' => 'nullable|array',
                ],
                [
                    'title.required' => 'عنوان نقش الزامی است',
                    'title.string' => 'عنوان نقش باید به‌صورت متن باشد',
                    'title.max' => 'عنوان نقش نباید بیشتر از ۲۵۵ کاراکتر باشد',
                    'title.unique'   => 'این عنوان قبلاً ثبت شده است',

                    'permissions.required' => 'انتخاب حداقل یک دسترسی الزامی است',
                    'permissions.array' => 'فرمت دسترسی‌ها نامعتبر است',
                ]
            );

            $option = $job->option ?? [];
            $option["permissions"] = $data['permissions'] ?? [];
            $job->title = $data['title'];
            $job->option = $option;
            $job->update();
            return response()->json([
                "status" => "success",
                "data" => $job
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                "status" => "validation_error",
                "errors" => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ویرایش نقش رخ داد",
            ], 500);
        }
    }

    public function job_destroy(Option $job)
    {
        $job->delete();
        return response()->json([
            "status" => "success",
            "message" => "نقش با موفقیت حذف شد"
        ], 200);
    }

    public function job_force_destroy($id)
    {
        $job = Option::withTrashed()->findOrFail($id);
        $job->forceDelete();
        return response()->json([
            "status" => "success",
            "message" => "نقش به صورت دائمی حذف شد"
        ], 200);
    }

    public function job_restore($id)
    {
        $job = Option::withTrashed()->findOrFail($id);
        $job->restore();
        return response()->json([
            "status" => "success",
            "message" => "نقش بازیابی شد",
            "data" => $job
        ], 200);
    }
    /******* Jobs *******/
}
