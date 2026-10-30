<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function users()
    {
        return view('admin.pages.allUser');
    }

    public function allUser()
    {
        $users = User::withCount('orders')->latest()->get();
        $formattedUsers = [];

        foreach ($users as $user) {
            $formattedUsers[] = [
                'id' => $user->id,
                'name' => $user->name ?? null,
                'profile_image' => $user->profile_image ?? null,
                'email' => $user->email,
                'total_orders' => $user->orders_count ?? 0,
            ];
        }

        $data['users'] = $formattedUsers;

        return response()->json($data);
    }

    public function viewUser(Request $request)
    {
        $data = null;
        if ($request->id) {
            $user = User::with('orders')->findOrFail($request->id);
            $data['user'] = $user;
        }

        return view('admin.pages.viewUser')->with(['data' => $data]);
    }
}
