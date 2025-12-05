<?php

namespace Modules\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\App\Models\App;

class AppController extends Controller
{
    public function index()
    {
        return view('app::index');

        return response()->json(App::all());
    }

    public function show($id)
    {
        return response()->json(App::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:apps,email',
            'password' => 'required|string|min:8',
        ]);

        $data['password'] = bcrypt($data['password']);

        $app = App::create($data);
        return response()->json($app, 201);
    }

    public function update(Request $request, $id)
    {
        $app = App::findOrFail($id);

        $data = $request->validate([
            'name'     => 'nullable|string|max:255',
            'email'    => 'nullable|email|unique:apps,email,' . $app->id,
            'password' => 'nullable|string|min:8',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $app->update($data);
        return response()->json($app);
    }

    public function destroy($id)
    {
        App::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
