<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessengerController extends Controller
{
    /**
     * Display the main messenger interface.
     *
     * @param  int|null  $id
     * @return \Illuminate\View\View
     */
    public function index(int $id = null): \Illuminate\View\View
    {
        $user = Auth::user();
        $friends = User::where('id', '!=', $user->id)
            ->orderBy('name')
            ->paginate();

        return view('messenger', [
            'friends' => $friends,
        ]);
    }
}
