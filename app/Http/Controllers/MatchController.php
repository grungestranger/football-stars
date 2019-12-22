<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class MatchController extends Controller
{
    /**
     * Show match page.
     *
     * @return View
     */
    public function index(): View
    {
        $user = auth()->user();

        $data = [];

        return view('match', $data);
    }
}
