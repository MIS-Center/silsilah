<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;

class UserMarriagesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Show user marriage list.
     *
     * @param  \App\User  $user
     * @return \Illuminate\View\View
     */
    public function index(User $user)
    {
        $marriages = $user->marriages()->with('husband', 'wife')
            ->withCount('childs')->get();
        dd( $marriages ) ;
        return view('users.marriages', compact('user', 'marriages'));
    }
}
