<?php

namespace Modules\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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
            $apps = App::with(['admin:id,name,lastname'])
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
            $app = App::with(['admin:id,name,lastname'])->findOrFail($id);
            
            // Get plan
            $plan = ExtData::where('f_id', $app->id)
                ->where('kind', 'AppPlan')
                ->with('om:id,title')
                ->first();
            
            $app->plan = $plan?->om;

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
            $data = $request->validate([
                'uid' => 'required|exists:users,id',
                'url' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('apps', 'url')->whereNull('deleted_at')
                ],
                'title' => 'required|string|max:255',
                'plan_id' => 'nullable|exists:options,id',
                'expiry_date' => 'nullable|date',
                'status' => 'nullable|integer|in:0,1',
            ], [
                'uid.required' => 'انتخاب کاربر الزامی است',
                'uid.exists' => 'کاربر انتخاب شده معتبر نیست',
                'url.required' => 'دامنه الزامی است',
                'url.unique' => 'این دامنه قبلاً ثبت شده است',
                'title.required' => 'نام شرکت/مغازه الزامی است',
                'plan_id.exists' => 'پلن انتخاب شده معتبر نیست',
                'expiry_date.date' => 'تاریخ اعتبار باید معتبر باشد',
            ]);

            $app = App::create([
                'uid' => $data['uid'],
                'url' => $data['url'],
                'title' => $data['title'],
                'status' => $data['status'] ?? 1,
                'expiry_date' => $data['expiry_date'] ?? null,
            ]);

            // Attach plan if provided
            if (!empty($data['plan_id'])) {
                ExtData::create([
                    'f_id' => $app->id,
                    'm_id' => $data['plan_id'],
                    'kind' => 'AppPlan',
                    'status' => 1,
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data' => $app->load('admin')
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

            $data = $request->validate([
                'uid' => 'nullable|exists:users,id',
                'url' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('apps', 'url')->ignore($id)->whereNull('deleted_at')
                ],
                'title' => 'nullable|string|max:255',
                'plan_id' => 'nullable|exists:options,id',
                'expiry_date' => 'nullable|date',
                'status' => 'nullable|integer|in:0,1',
            ], [
                'uid.exists' => 'کاربر انتخاب شده معتبر نیست',
                'url.unique' => 'این دامنه قبلاً ثبت شده است',
                'plan_id.exists' => 'پلن انتخاب شده معتبر نیست',
                'expiry_date.date' => 'تاریخ اعتبار باید معتبر باشد',
            ]);

            $app->update(array_filter([
                'uid' => $data['uid'] ?? null,
                'url' => $data['url'] ?? null,
                'title' => $data['title'] ?? null,
                'status' => $data['status'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
            ], fn($value) => $value !== null));

            // Update plan
            if (isset($data['plan_id'])) {
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

            return response()->json([
                'status' => 'success',
                'data' => $app->load('admin')
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
}
