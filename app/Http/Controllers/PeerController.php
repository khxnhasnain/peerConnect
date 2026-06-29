<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PeerController extends Controller
{
    public function save(Request $request)
    {
        $user = User::find(Auth::id());

        $user->peer_id = $request->peer_id;
        $user->is_online = true;
        $user->save();

        return response()->json(['success' => true]);
    }
}
