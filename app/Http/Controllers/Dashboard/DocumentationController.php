<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;

class DocumentationController extends Controller
{
    public function index()
    {
        return view('dashboard.docs.index');
    }
}

