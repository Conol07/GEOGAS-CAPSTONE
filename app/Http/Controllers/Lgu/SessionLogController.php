<?php

namespace App\Http\Controllers\Lgu;

use App\Http\Controllers\Controller;
use App\Models\SessionLog;
use App\Models\User;
use Illuminate\Http\Request;

class SessionLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = SessionLog::with('user')
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->input('role')))
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->input('action')))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->input('user_id')))
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        $users = User::orderBy('name')->get(['id', 'name', 'role']);

        return view('lgu.session-logs.index', compact('logs', 'users'));
    }
}
