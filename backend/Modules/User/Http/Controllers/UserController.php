<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\User\Models\User;
use Modules\User\Models\Option;
use Illuminate\Validation\ValidationException;

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

    /******* Jobs *******/
    public function job_index()
    {
        try {
            $Options = Option::select('id', 'title', 'option', 'created_at')
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
                ]
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
                    'title' => 'required|string|max:255',
                    'permissions' => 'nullable|array',
                ],
                [
                    'title.required' => 'عنوان نقش الزامی است',
                    'title.string' => 'عنوان نقش باید به‌صورت متن باشد',
                    'title.max' => 'عنوان نقش نباید بیشتر از ۲۵۵ کاراکتر باشد',

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
                    'title' => 'required|string|max:255',
                    'permissions' => 'nullable|array',
                ],
                [
                    'title.required' => 'عنوان نقش الزامی است',
                    'title.string' => 'عنوان نقش باید به‌صورت متن باشد',
                    'title.max' => 'عنوان نقش نباید بیشتر از ۲۵۵ کاراکتر باشد',

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

    public function job_destroy(Request $request, $job)
    {
        if (!empty($request->action)) {
            if ($request->action == "restore") {
                $BaseJob = Option::withTrashed()->findOrFail($job);
                $BaseJob->restore();
                return response()->json([
                    "status" => "success",
                    "message" => "نقش بازیابی شد",
                    "data" => $BaseJob
                ], 200);
            } elseif ($request->action == "delete") {
                $BaseJob = Option::withTrashed()->findOrFail($job);
                $BaseJob->forceDelete(); // Permanent delete
                return response()->json([
                    "status" => "success",
                    "message" => "نقش به صورت دائمی حذف شد"
                ], 200);
            }
        }
        $BaseJob = Option::findOrFail($job);
        $BaseJob->delete(); // Soft delete
        return response()->json([
            "status" => "success",
            "message" => "نقش با موفقیت حذف شد"
        ], 200);
    }
    /******* Jobs *******/
}
