<?php

namespace Modules\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\App\Models\App;
use Modules\User\Models\User;
use Modules\User\Models\Option;
use Modules\User\Models\ExtData;
use Illuminate\Validation\Rule;

class AppController extends Controller
{
    public function index(Request $request)
    {
        try {
            $apps = App::with(['admin:id,name,lastname', 'parent:id,title,url'])
                ->when($request->input('title'), function ($q) use ($request) {
                    $q->where('title', 'LIKE', '%' . $request->input('title') . '%');
                })
                ->when($request->input('url'), function ($q) use ($request) {
                    $q->where('url', 'LIKE', '%' . $request->input('url') . '%');
                })
                ->when($request->input('status') && $request->input('status') == 'deleted', function ($q) {
                    $q->onlyTrashed();
                })
                ->when($request->input('status') && $request->input('status') != 'deleted', function ($q) use ($request) {
                    $q->where('status', $request->input('status'));
                })
                ->when($request->input('app_id') !== null, function ($q) use ($request) {
                    if ($request->input('app_id') === '0' || $request->input('app_id') === '') {
                        // فقط اکانت‌های اصلی (بدون شعبه)
                        $q->whereNull('app_id');
                    } else {
                        // شعبه‌های یک اکانت خاص
                        $q->where('app_id', $request->input('app_id'));
                    }
                })
                ->when($request->input('is_branch') === '1', function ($q) {
                    // فقط شعبه‌ها
                    $q->whereNotNull('app_id');
                })
                ->when($request->input('is_branch') === '0', function ($q) {
                    // فقط اکانت‌های اصلی
                    $q->whereNull('app_id');
                })
                ->orderByDesc('id')
                ->paginate($request->input('limit', 10));

            // Get plans for each app
            $appIds = $apps->pluck('id')->toArray();
            $appPlans = collect();
            
            if (!empty($appIds)) {
                $appPlans = ExtData::whereIn('f_id', $appIds)
                    ->where('kind', 'AppPlan')
                    ->with('om:id,title')
                    ->get()
                    ->groupBy('f_id');
            }

            // Get expiry dates
            $apps->getCollection()->transform(function ($app) use ($appPlans) {
                $plan = $appPlans->get($app->id)?->first();
                $app->plan = $plan?->om ?? null;
                return $app;
            });

            return response()->json([
                'status' => 'success',
                'items' => $apps
            ], 200);
        } catch (\Throwable $th) {
            Log::error('AppController@index error', [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $app = App::with(['admin:id,name,lastname', 'parent:id,title,url', 'branches:id,title,url,app_id', 'employees', 'warehouses'])->findOrFail($id);
            
            if (is_null($app->app_id)) {
                $plan = ExtData::where('f_id', $app->id)
                    ->where('kind', 'AppPlan')
                    ->with('om:id,title')
                    ->first();
                $app->plan = $plan?->om;
            }

            return response()->json([
                'status' => 'success',
                'data' => $app
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $isBranch = !empty($request->input('app_id'));

            // قوانین اعتبارسنجی متفاوت برای پورتال و شعبه
            $rules = [
                'title' => 'required|string|max:255',
                'plan_id' => 'nullable|exists:options,id',
                'expiry_date' => 'nullable|date',
                'status' => 'nullable|integer|in:0,1',
                'app_id' => 'nullable|exists:apps,id',
            ];

            $messages = [
                'title.required' => 'نام شرکت/مغازه الزامی است',
                'plan_id.exists' => 'پلن انتخاب شده معتبر نیست',
                'expiry_date.date' => 'تاریخ اعتبار باید معتبر باشد',
                'app_id.exists' => 'اکانت اصلی انتخاب شده معتبر نیست',
            ];

            if ($isBranch) {
                // شعبه: uid و url ندارد، کارمندان از طریق extdatas
                unset($rules['plan_id']);
                $rules['employee_ids'] = 'nullable|array';
                $rules['employee_ids.*'] = 'exists:users,id';
                $rules['warehouse_ids'] = 'nullable|array';
                $rules['warehouse_ids.*'] = [
                    Rule::exists('product_options', 'id')->where(function ($query) {
                        $query->where('kind', 'warehouse');
                    })
                ];
                $messages['employee_ids.*.exists'] = 'کاربر انتخاب شده معتبر نیست';
                $messages['warehouse_ids.*.exists'] = 'انبار انتخاب شده معتبر نیست';
            } else {
                // پورتال: uid و url الزامی
                $rules['uid'] = 'required|exists:users,id';
                $rules['url'] = ['required', 'string', 'max:255', Rule::unique('apps', 'url')->whereNull('deleted_at')];
                $messages['uid.required'] = 'انتخاب کاربر الزامی است';
                $messages['uid.exists'] = 'کاربر انتخاب شده معتبر نیست';
                $messages['url.required'] = 'دامنه الزامی است';
                $messages['url.unique'] = 'این دامنه قبلاً ثبت شده است';
            }

            $data = $request->validate($rules, $messages);

            // اگر app_id مشخص شده، بررسی کنیم که خودش شعبه نباشد
            if (!empty($data['app_id'])) {
                $parentApp = App::find($data['app_id']);
                if ($parentApp && $parentApp->app_id) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'نمی‌توان برای یک شعبه، شعبه دیگر ایجاد کرد'
                    ], 422);
                }
            }

            $app = App::create([
                'uid' => $isBranch ? null : $data['uid'],
                'url' => $isBranch ? null : $data['url'],
                'title' => $data['title'],
                'status' => $data['status'] ?? 1,
                'expiry_date' => $data['expiry_date'] ?? null,
                'app_id' => $data['app_id'] ?? null,
            ]);

            // Attach plan if provided
            if (!$isBranch && !empty($data['plan_id'])) {
                ExtData::create([
                    'f_id' => $app->id,
                    'm_id' => $data['plan_id'],
                    'kind' => 'AppPlan',
                    'status' => 1,
                ]);
            }

            // شعبه: اتصال کارمندان از طریق extdatas
            if ($isBranch && !empty($data['employee_ids'])) {
                foreach ($data['employee_ids'] as $userId) {
                    ExtData::create([
                        'f_id' => $app->id,
                        'm_id' => $userId,
                        'kind' => 'AppEmployee',
                        'status' => 1,
                    ]);
                }
            }

            if ($isBranch && !empty($data['warehouse_ids'])) {
                foreach ($data['warehouse_ids'] as $warehouseId) {
                    ExtData::create([
                        'f_id' => $app->id,
                        'm_id' => $warehouseId,
                        'kind' => 'AppWarehouse',
                        'status' => 1,
                    ]);
                }
            }

            $app->load($isBranch ? ['employees', 'warehouses'] : 'admin');

            return response()->json([
                'status' => 'success',
                'data' => $app
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $app = App::findOrFail($id);
            $isBranch = $app->app_id !== null;

            $rules = [
                'title' => 'nullable|string|max:255',
                'plan_id' => 'nullable|exists:options,id',
                'expiry_date' => 'nullable|date',
                'status' => 'nullable|integer|in:0,1',
            ];

            $messages = [
                'plan_id.exists' => 'پلن انتخاب شده معتبر نیست',
                'expiry_date.date' => 'تاریخ اعتبار باید معتبر باشد',
            ];

            if ($isBranch) {
                // شعبه: فقط کارمندان
                unset($rules['plan_id']);
                $rules['employee_ids'] = 'nullable|array';
                $rules['employee_ids.*'] = 'exists:users,id';
                $rules['warehouse_ids'] = 'nullable|array';
                $rules['warehouse_ids.*'] = [
                    Rule::exists('product_options', 'id')->where(function ($query) {
                        $query->where('kind', 'warehouse');
                    })
                ];
                $messages['employee_ids.*.exists'] = 'کاربر انتخاب شده معتبر نیست';
                $messages['warehouse_ids.*.exists'] = 'انبار انتخاب شده معتبر نیست';
            } else {
                // پورتال: uid و url
                $rules['uid'] = 'nullable|exists:users,id';
                $rules['url'] = ['nullable', 'string', 'max:255', Rule::unique('apps', 'url')->ignore($id)->whereNull('deleted_at')];
                $rules['app_id'] = 'nullable|exists:apps,id';
                $messages['uid.exists'] = 'کاربر انتخاب شده معتبر نیست';
                $messages['url.unique'] = 'این دامنه قبلاً ثبت شده است';
                $messages['app_id.exists'] = 'اکانت اصلی انتخاب شده معتبر نیست';
            }

            $data = $request->validate($rules, $messages);

            // بررسی app_id برای پورتال‌ها
            if (!$isBranch && isset($data['app_id']) && $data['app_id'] !== null) {
                if ($data['app_id'] == $app->id) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'یک اکانت نمی‌تواند شعبه خودش باشد'
                    ], 422);
                }
                $parentApp = App::find($data['app_id']);
                if ($parentApp && $parentApp->app_id) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'نمی‌توان برای یک شعبه، شعبه دیگر ایجاد کرد'
                    ], 422);
                }
            }

            // فیلدهای قابل آپدیت
            $updateData = array_filter([
                'title' => $data['title'] ?? null,
                'status' => $data['status'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
            ], fn($value) => $value !== null);

            if (!$isBranch) {
                // پورتال: uid و url هم آپدیت شود
                if (isset($data['uid'])) $updateData['uid'] = $data['uid'];
                if (isset($data['url'])) $updateData['url'] = $data['url'];
                if (isset($data['app_id'])) $updateData['app_id'] = $data['app_id'];
            }

            $app->update($updateData);

            // Update plan
            if (!$isBranch && isset($data['plan_id'])) {
                ExtData::where('f_id', $app->id)
                    ->where('kind', 'AppPlan')
                    ->delete();

                if ($data['plan_id']) {
                    ExtData::create([
                        'f_id' => $app->id,
                        'm_id' => $data['plan_id'],
                        'kind' => 'AppPlan',
                        'status' => 1,
                    ]);
                }
            }

            // شعبه: بروزرسانی کارمندان
            if ($isBranch && isset($data['employee_ids'])) {
                // حذف کارمندان قبلی
                ExtData::where('f_id', $app->id)
                    ->where('kind', 'AppEmployee')
                    ->delete();

                // اضافه کردن کارمندان جدید
                foreach ($data['employee_ids'] as $userId) {
                    ExtData::create([
                        'f_id' => $app->id,
                        'm_id' => $userId,
                        'kind' => 'AppEmployee',
                        'status' => 1,
                    ]);
                }
            }

            if ($isBranch && isset($data['warehouse_ids'])) {
                ExtData::where('f_id', $app->id)
                    ->where('kind', 'AppWarehouse')
                    ->delete();

                foreach ($data['warehouse_ids'] as $warehouseId) {
                    ExtData::create([
                        'f_id' => $app->id,
                        'm_id' => $warehouseId,
                        'kind' => 'AppWarehouse',
                        'status' => 1,
                    ]);
                }
            }

            $app->load($isBranch ? ['employees', 'warehouses'] : 'admin');

            return response()->json([
                'status' => 'success',
                'data' => $app
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $app = App::findOrFail($id);
            $app->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'پورتال با موفقیت حذف شد'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            $app = App::withTrashed()->findOrFail($id);
            $app->restore();

            return response()->json([
                'status' => 'success',
                'data' => $app
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function forceDestroy($id)
    {
        try {
            $app = App::withTrashed()->findOrFail($id);
            $app->forceDelete();

            return response()->json([
                'status' => 'success',
                'message' => 'پورتال برای همیشه حذف شد'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function branches(Request $request)
    {
        try {
            $branches = App::with(['employees', 'warehouses', 'parent:id,title,url'])
                ->whereNotNull('app_id')
                ->when($request->input('title'), function ($q) use ($request) {
                    $q->where('title', 'LIKE', '%' . $request->input('title') . '%');
                })
                ->when($request->input('app_id'), function ($q) use ($request) {
                    $q->where('app_id', $request->input('app_id'));
                })
                ->when($request->input('mine') === '1', function ($q) {
                    $userId = Auth::id();
                    if (!$userId) {
                        $q->whereRaw('1=0');
                        return;
                    }
                    $q->whereHas('employees', function ($userQuery) use ($userId) {
                        $userQuery->where('users.id', $userId);
                    });
                })
                ->when($request->input('status') && $request->input('status') == 'deleted', function ($q) {
                    $q->onlyTrashed();
                })
                ->when($request->input('status') && $request->input('status') != 'deleted', function ($q) use ($request) {
                    $q->where('status', $request->input('status'));
                })
                ->orderByDesc('id')
                ->paginate($request->input('limit', 10));

            return response()->json([
                'status' => 'success',
                'items' => $branches
            ], 200);
        } catch (\Throwable $th) {
            Log::error('AppController@branches error', [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ], 500);
        }
    }
}
