<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Couple;
use Illuminate\Http\Request;

class CouplesController extends Controller
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
     * Display the specified Couple.
     *
     * @param  \App\Couple  $couple
     * @return \Illuminate\View\View
     */
    public function show(Couple $couple)
    {
        dd( $couple) ;
        return view('couples.show', compact('couple'));
    }

    /**
     * Show the form for editing the specified Couple.
     *
     * @param  \App\Couple  $couple
     * @return \Illuminate\View\View
     */
    public function edit(Couple $couple)
    {
        $this->authorize('edit', $couple);

        return view('couples.edit', compact('couple'));
    }

    /**
     * Update the specified Couple in storage.
     *
     * @param  \App\Couple  $couple
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Couple $couple)
    {
        dd( $couple) ;

        $this->authorize('edit', $couple);

        $coupleData = request()->validate([
            'marriage_date' => 'nullable|date|date_format:Y-m-d',
            'divorce_date'  => 'nullable|date|date_format:Y-m-d',
        ]);

        $couple->marriage_date = $coupleData['marriage_date'];
        $couple->divorce_date = $coupleData['divorce_date'];
        $couple->save();

        return redirect()->route('couples.show', $couple);
    }
}
