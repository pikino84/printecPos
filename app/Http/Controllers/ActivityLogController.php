<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    

    public function index()
    {
        $logs = Activity::with('causer')->latest()->paginate(20);
        return view('activity_logs.index', compact('logs'));
    }
}
