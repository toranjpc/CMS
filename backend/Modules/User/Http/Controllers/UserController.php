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
        $users = User::with([
            // "reagent",
            // "category",
            // "userPlan",
            // "extraData",
            "jobOption:id,title"
        ]);

        if (!empty(request('sex'))) $users = $users->where('sex', request('sex') == "men" ? 1 : 0);
        if (!empty(request('name'))) $users = $users->where('name', 'LIKE', '%' . request('name') . '%');
        if (!empty(request('lastname'))) $users = $users->where('lastname', 'LIKE', '%' . request('lastname') . '%');
        if (!empty(request('username'))) $users = $users->where('username', 'LIKE', '%' . request('username') . '%');
        if (!empty(request('mobile'))) $users = $users->where('mobile', 'LIKE', '%' . request('mobile') . '%');

        if (!empty(request('status')) && request('status') == "deleted") $users = $users->onlyTrashed();
        $users = $users->orderByDesc('id')->paginate(request("limit", 10));

        $DATA = [
            'items' => $users->items(),
            'total' => $users->total(),
            'per_page' => $users->perPage(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'from' => $users->firstItem(),
            'to' => $users->lastItem(),
        ];
        return response()->json(
            [
                "status" => "success",
                "data" => $DATA
            ],
            200
        );
    }

    public function show($id)
    {
        return response()->json(User::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            // اطلاعات پایه
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',

            // اطلاعات تماس
            'mobile' => ['required', 'integer', 'unique:users,mobile'],

            // اطلاعات شخصی
            'sex' => 'required|integer|in:0,1',
            'national_code' => 'nullable|string|size:10|regex:/^[0-9]+$/',
            'birth_date' => 'nullable|date|date_format:Y-m-d',

            // اطلاعات سیستم
            'job' => 'nullable|integer|exists:options,id',
            'type' => 'required|string|in:user,seller,staff',
            'referral_id' => 'nullable|string|max:255',

            // اطلاعات اضافی
            // 'alias' => 'nullable|string|max:255',
            'ircode' => 'nullable|integer',

            // امنیت
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|same:password',
        ], [
            // اطلاعات پایه
            'name.required' => 'نام الزامی است',
            'name.string' => 'نام باید به‌صورت صحیح نوشته شود',
            'name.max' => 'نام نباید بیشتر از ۲۵۵ کاراکتر باشد',
            'lastname.required' => 'نام خانوادگی الزامی است',
            'lastname.string' => 'نام خانوادگی باید به‌صورت صحیح نوشته شود',
            'lastname.max' => 'نام خانوادگی نباید بیشتر از ۲۵۵ کاراکتر باشد',
            'username.required' => 'نام کاربری الزامی است',
            'username.string' => 'نام کاربری باید به‌صورت صحیح نوشته شود',
            'username.max' => 'نام کاربری نباید بیشتر از ۲۵۵ کاراکتر باشد',
            'username.unique' => 'این نام کاربری قبلاً ثبت شده است',

            // اطلاعات تماس
            'mobile.required' => 'شماره موبایل الزامی است',
            'mobile.integer' => 'شماره موبایل باید عدد باشد',
            'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است',

            // اطلاعات شخصی
            'sex.required' => 'جنسیت الزامی است',
            'sex.integer' => 'جنسیت باید عدد باشد',
            'sex.in' => 'جنسیت باید ۰ (زن) یا ۱ (مرد) باشد',
            'national_code.string' => 'کد ملی باید به‌صورت صحیح نوشته شود',
            'national_code.size' => 'کد ملی باید ۱۰ رقم باشد',
            'national_code.regex' => 'کد ملی باید فقط شامل اعداد باشد',
            'birth_date.date' => 'تاریخ تولد باید تاریخ معتبر باشد',
            'birth_date.date_format' => 'فرمت تاریخ تولد باید YYYY-MM-DD باشد',

            // اطلاعات سیستم
            'job.integer' => 'نقش باید عدد باشد',
            'job.exists' => 'نقش انتخاب شده معتبر نیست',
            'type.required' => 'نوع کاربری الزامی است',
            'type.string' => 'نوع کاربری باید به‌صورت صحیح نوشته شود',
            'type.in' => 'نوع کاربری باید یکی از مقادیر user, seller, staff باشد',
            'referral_id.string' => 'شناسه معرف باید به‌صورت صحیح نوشته شود',
            'referral_id.max' => 'شناسه معرف نباید بیشتر از ۲۵۵ کاراکتر باشد',

            // اطلاعات اضافی
            // 'alias.string' => 'نام مستعار باید به‌صورت صحیح نوشته شود',
            // 'alias.max' => 'نام مستعار نباید بیشتر از ۲۵۵ کاراکتر باشد',
            'ircode.integer' => 'کد پستی باید عدد باشد',

            // امنیت
            'password.required' => 'رمز عبور الزامی است',
            'password.string' => 'رمز عبور باید به‌صورت صحیح نوشته شود',
            'password.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد',
            'password_confirmation.required' => 'تکرار رمز عبور الزامی است',
            'password_confirmation.same' => 'رمز عبور و تکرار آن باید یکسان باشند',
        ]);

        // آماده‌سازی داده‌ها برای ذخیره
        $userData = [
            'name' => $data['name'],
            'lastname' => $data['lastname'],
            'username' => $data['username'],
            'mobile' => $data['mobile'],
            'sex' => $data['sex'],
            'ircode' => $data['ircode'] ?? 0,
            // 'alias' => $data['alias'],
            'birth' => $data['birth_date'],
            'password' => bcrypt($data['password']),
            'job' => $data['job'],
            'datas' => [
                'national_code' => $data['national_code'],
                'type' => $data['type'],
                'referral_id' => $data['referral_id'],
            ],
        ];

        $user = User::create($userData);


        if ($request->hasFile('avatar')) {
            $avatarFile = $request->file('avatar');
            $extension = strtolower($avatarFile->getClientOriginalExtension());
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
            if (!in_array($extension, $allowedExtensions)) {
                throw new \Exception('فرمت فایل مجاز نیست');
            }
            $avatarName = $user->id; //. '.' . $extension;
            $stored = $avatarFile->storeAs('users', $avatarName);
        }


        return response()->json($user, 201);
    }

    public function update(Request $request, user $user)
    {
        // return $request;

        $data = $request->validate([
            // اطلاعات پایه
            'name' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username,' . $user->id],

            // اطلاعات تماس
            'mobile' => ['nullable', 'integer', 'unique:users,mobile,' . $user->id],

            // اطلاعات شخصی
            'sex' => 'nullable|integer|in:0,1',
            'national_code' => 'nullable|string|size:10|regex:/^[0-9]+$/',
            'birth_date' => 'nullable|date|date_format:Y-m-d',

            // اطلاعات سیستم
            'job' => 'nullable|integer|exists:options,id',
            'type' => 'nullable|string|in:user,seller,staff',
            'referral_id' => 'nullable|string|max:255',

            // اطلاعات اضافی
            // 'alias' => 'nullable|string|max:255',
            'ircode' => 'nullable|integer',

            // امنیت
            'password' => 'nullable|string|min:8',
            'password_confirmation' => 'nullable|string|same:password',
        ], [
            // اطلاعات پایه
            'name.string' => 'نام باید به‌صورت صحیح نوشته شود',
            'name.max' => 'نام نباید بیشتر از ۲۵۵ کاراکتر باشد',
            'lastname.string' => 'نام خانوادگی باید به‌صورت صحیح نوشته شود',
            'lastname.max' => 'نام خانوادگی نباید بیشتر از ۲۵۵ کاراکتر باشد',
            'username.string' => 'نام کاربری باید به‌صورت صحیح نوشته شود',
            'username.max' => 'نام کاربری نباید بیشتر از ۲۵۵ کاراکتر باشد',
            'username.unique' => 'این نام کاربری قبلاً ثبت شده است',

            // اطلاعات تماس
            'mobile.integer' => 'شماره موبایل باید عدد باشد',
            'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است',

            // اطلاعات شخصی
            'sex.integer' => 'جنسیت باید عدد باشد',
            'sex.in' => 'جنسیت باید ۰ (زن) یا ۱ (مرد) باشد',
            'national_code.string' => 'کد ملی باید به‌صورت صحیح نوشته شود',
            'national_code.size' => 'کد ملی باید ۱۰ رقم باشد',
            'national_code.regex' => 'کد ملی باید فقط شامل اعداد باشد',
            'birth_date.date' => 'تاریخ تولد باید تاریخ معتبر باشد',
            'birth_date.date_format' => 'فرمت تاریخ تولد باید YYYY-MM-DD باشد',

            // اطلاعات سیستم
            'job.integer' => 'نقش باید عدد باشد',
            'job.exists' => 'نقش انتخاب شده معتبر نیست',
            'type.string' => 'نوع کاربری باید به‌صورت صحیح نوشته شود',
            'type.in' => 'نوع کاربری باید یکی از مقادیر user, seller, staff باشد',
            'referral_id.string' => 'شناسه معرف باید به‌صورت صحیح نوشته شود',
            'referral_id.max' => 'شناسه معرف نباید بیشتر از ۲۵۵ کاراکتر باشد',

            // اطلاعات اضافی
            // 'alias.string' => 'نام مستعار باید به‌صورت صحیح نوشته شود',
            // 'alias.max' => 'نام مستعار نباید بیشتر از ۲۵۵ کاراکتر باشد',
            'ircode.integer' => 'کد پستی باید عدد باشد',

            // امنیت
            'password.string' => 'رمز عبور باید به‌صورت صحیح نوشته شود',
            'password.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد',
            'password_confirmation.same' => 'رمز عبور و تکرار آن باید یکسان باشند',
        ]);

        // آماده‌سازی داده‌ها برای بروزرسانی
        $userData = [];

        // فیلدهای مستقیم
        if (isset($data['name'])) $userData['name'] = $data['name'];
        if (isset($data['lastname'])) $userData['lastname'] = $data['lastname'];
        if (isset($data['username'])) $userData['username'] = $data['username'];
        if (isset($data['mobile'])) $userData['mobile'] = $data['mobile'];
        if (isset($data['sex'])) $userData['sex'] = $data['sex'];
        if (isset($data['ircode'])) $userData['ircode'] = $data['ircode'];
        // if (isset($data['alias'])) $userData['alias'] = $data['alias'];
        if (isset($data['birth_date'])) $userData['birth'] = $data['birth_date'];
        if (isset($data['job'])) $userData['job'] = $data['job'];

        // هش کردن رمز عبور در صورت وجود
        if (!empty($data['password'])) {
            $userData['password'] = bcrypt($data['password']);
        }

        // بروزرسانی داده‌های JSON
        $currentDatas = $user->datas ?? [];
        $updatedDatas = array_merge($currentDatas, array_filter([
            'national_code' => $data['national_code'] ?? null,
            'type' => $data['type'] ?? null,
            'referral_id' => $data['referral_id'] ?? null,
        ], function ($value) {
            return $value !== null;
        }));

        if (!empty($updatedDatas)) {
            $userData['datas'] = $updatedDatas;
        }


        if ($request->hasFile('avatar')) {
            $avatarFile = $request->file('avatar');
            $extension = strtolower($avatarFile->getClientOriginalExtension());
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
            if (!in_array($extension, $allowedExtensions)) {
                throw new \Exception('فرمت فایل مجاز نیست');
            }
            $avatarName = $user->id; //. '.' . $extension;
            $stored = $avatarFile->storeAs('users', $avatarName);
        }


        $user->update($userData);
        return response()->json($user);
    }

    public function destroy(user $user)
    {
        $me = auth()->user;
        if ($me->id == $user->id) {
            return response()->json([
                "status" => "error",
                "message" => "داشتی خودتو حذف میکردی !!!!!",
            ], 500);
        }

        $user->delete();
        return response()->json([
            "status" => "success",
            "message" => "کاربر با موفقیت حذف شد"
        ], 200);
    }

    public function force_destroy($id)
    {
        $user = user::withTrashed()->findOrFail($id);

        // پاک کردن فایل آواتار
        $oldAvatarPath = storage_path('app/public/users/' . $user->id . '.*');
        $oldFiles = glob($oldAvatarPath);
        foreach ($oldFiles as $oldFile) {
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        $user->forceDelete();
        return response()->json([
            "status" => "success",
            "message" => "کاربر به صورت دائمی حذف شد"
        ], 200);
    }


    public function restore($id)
    {
        $user = user::withTrashed()->findOrFail($id);
        $user->restore();
        return response()->json([
            "status" => "success",
            "message" => "کاربر بازیابی شد",
            "data" => $user
        ], 200);
    }



    /******* categories *******/
    public function category_index()
    {
        try {
            $Options = Option::select('id', 'f_id', 'title', 'option', 'created_at', 'updated_at', 'deleted_at')
                ->where("kind", "category");

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
                    'icon' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',

                ],
                [
                    'title.required' => 'عنوان دسته بندی الزامی است',
                    'title.string' => 'عنوان دسته بندی باید به‌صورت متن باشد',
                    'title.max' => 'عنوان دسته بندی نباید بیشتر از ۲۵۵ کاراکتر باشد',
                    'title.unique'   => 'این عنوان قبلاً ثبت شده است',
                    'icon.file' => 'فایل باید معتبر باشد',
                    'icon.mimes' => 'فرمت فایل باید jpeg، png، jpg، gif یا svg باشد',
                    'icon.max' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد',
                ]
            );

            $category = Option::create([
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
                $stored = $iconFile->storeAs('users/categories', $iconName);
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
                    'icon' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ],
                [
                    'title.required' => 'عنوان دسته بندی الزامی است',
                    'title.string' => 'عنوان دسته بندی باید به‌صورت متن باشد',
                    'title.max' => 'عنوان دسته بندی نباید بیشتر از ۲۵۵ کاراکتر باشد',
                    'title.unique'   => 'این عنوان قبلاً ثبت شده است',
                    'icon.file' => 'فایل باید معتبر باشد',
                    'icon.mimes' => 'فرمت فایل باید jpeg، png، jpg، gif یا svg باشد',
                    'icon.max' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد',
                ]
            );

            $category->title = $data['title'];
            $category->updated_at = now();
            $category->update();

            if ($request->hasFile('icon')) {
                $oldIconPath = storage_path('app/public/categories/' . $category->id . '.*');
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
                $stored = $iconFile->storeAs('users/categories', $iconName);
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

        // پاک کردن فایل آیکون
        $oldIconPath = storage_path('app/public/categories/' . $category->id . '.*');
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
