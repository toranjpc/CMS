<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\User\Models\User;
use Modules\User\Models\Option;

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
            if (in_array(request('status', ''), [0, 1])) {
                $Options = $Options->where('status', request('status'));
            } else {
                $Options = $Options->where("status", ">", 0);
            }
            $Options = $Options->paginate(request("limit", 5));
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
                $pers = collect(app('router')->getRoutes())
                    ->filter(fn($route) => in_array('checkPermission', $route->gatherMiddleware()))
                    ->map(fn($route) => $route->getName())
                    ->values();
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
        return $request;
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'permissions' => 'required|array',
            'f_id' => 'nullable|integer',
        ]);

        $job = Option::create([
            "f_id" => $data['f_id'] ?? 0,
            "title" => $data['title'],
            "option" => [
                "permissions" => $data['permissions'],
            ],
            "kind" => "job",
        ]);

        return response()->json($job, 201);
    }


    public function job_update(Request $request, $id)
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

    public function job_destroy($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
    /******* Jobs *******/
}
