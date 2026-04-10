<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\News;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {


        $pendingAbsences = Absence::with('employee')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $pendingCount = Absence::where('status', 'pending')->count();

        $recentNews = News::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
<<<<<<< HEAD


        $newsCount = News::count();

        $employees = Employee::where('status', 'active')->orderBy('first_name')->paginate(25);

=======
        
        
$totalCount = Absence::where('status', 'pending')->count() + News::count();
        $pendingCount = Absence::where('status', 'pending')->count();
        $newsCount = News::count();
        
        
        $employees = Employee::active()->orderBy('first_name')->get();
        
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
        return view('notifications.index', compact('pendingAbsences', 'recentNews', 'pendingCount', 'newsCount', 'employees'));
    }


    public function data()
    {


        $pendingAbsences = Absence::with('employee')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $pendingCount = Absence::where('status', 'pending')->count();

        $recentNews = News::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
<<<<<<< HEAD

        $totalCount = $pendingCount + News::count();


=======
        
        
$totalCount = Absence::where('status', 'pending')->count() + News::count();
        
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
        return response()->json([
            'absences' => $pendingAbsences->map(function($absence) {
                return [
                    'id' => $absence->id,
                    'type' => 'absence',
                    'title' => 'Demande de congé',
                    'employee' => $absence->employee ? $absence->employee->full_name : 'Inconnu',
                    'message' => $absence->employee ? $absence->employee->full_name . ' a demandé un congé' : 'Demande de congé',
                    'date' => $absence->start_date,
                    'status' => $absence->status,
                    'created_at' => $absence->created_at->format('d/m/Y H:i'),
                    'url' => route('absences.show', $absence->id),
                ];
            }),
            'news' => $recentNews->map(function($news) {
                return [
                    'id' => $news->id,
                    'type' => 'news',
                    'title' => $news->title,


            'message' => substr($news->content, 0, 50) . (strlen($news->content) > 50 ? '...' : ''),


                    'date' => $news->created_at,
                    'created_at' => $news->created_at->format('d/m/Y H:i'),
                    'url' => route('news.show', $news->id),
                ];
            }),
            'totalCount' => $totalCount,
            'pendingCount' => $pendingAbsences->count(),
        ]);
    }
}

