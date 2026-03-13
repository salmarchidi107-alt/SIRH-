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
        
        
        $recentNews = News::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        
        $pendingCount = $pendingAbsences->count();
        $newsCount = $recentNews->count();
        
        
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();
        
        return view('notifications.index', compact('pendingAbsences', 'recentNews', 'pendingCount', 'newsCount', 'employees'));
    }
    
    public function data()
    {
        
        $pendingAbsences = Absence::with('employee')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        
        $recentNews = News::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        
        $totalCount = $pendingAbsences->count() + $recentNews->count();
        
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
                    'message' => \Str::limit($news->content, 50),
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

