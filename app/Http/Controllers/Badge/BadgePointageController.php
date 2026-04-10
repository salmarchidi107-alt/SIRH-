<?php

namespace App\Http\Controllers\Badge;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Pointage;
use App\Models\BadgeRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BadgePointageController extends Controller
{
    public function __construct()
{
    Auth::shouldUse('badge');
}
    public function dashboard(Request $request)
    {
        $user = auth('badge')->user();
        $employee = $user?->employee ?? Employee::first();
        $today = Carbon::today();
        $shift = BadgeRecord::where('employee_id', $employee->id)
            ->whereDate('created_at', $today)
            ->get();

        $todayShift = [
            'first_entree' => $shift->where('type','entree')->first()?->created_at?->format('H:i'),
            'last_sortie'  => $shift->where('type','sortie')->last()?->created_at?->format('H:i'),
            'pause_display'=> $shift->where('type','pause')->count() ? $shift->where('type','pause')->count().' fois' : null,
            'total_human'  => $this->calcTotalTime($shift),
        ];

        return view('badge.dashboard', [
            'employee' => $employee,
            'todayShift' => $todayShift,
            'canEntree' => $shift->where('type','entree')->isEmpty() || $shift->last()?->type==='sortie',
            'canSortie'  => $shift->where('type','entree')->isNotEmpty() && $shift->last()?->type!=='sortie'
        ]);
    }

    public function handleAction(Request $request)
    {
        $request->validate(['action' => 'required|string']);
        
        $user = auth('badge')->user();
        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }
        
        $employee = $user->employee ?? Employee::first();
        $action = $request->action;

        $realType = match($action) {
            'debut', 'retour_pause' => 'entree',
            'pause', 'sortie_pause' => 'pause',
            'fin', 'fin_shift' => 'sortie',
            default => throw new \InvalidArgumentException("Invalid action: {$action}")
        };

        $this->recordAction($realType, $employee);

        $request->session()->flash('last_type', $action);

        return response()->json([
            'success' => true, 
            'redirect' => route('badge.result'),
            'message' => 'Pointage enregistré avec succès'
        ]);
    }

    public function action(Request $request)
    {
        $user = auth('badge')->user();
        $employee = $user?->employee ?? Employee::first();
        $type = $request->action ?? 'entree';

        $realType = match($type) {
            'debut' => 'entree',
            'pause' => 'pause',
            'fin' => 'sortie',
            default => throw new \InvalidArgumentException("Invalid action type: {$type}")
        };

        $this->recordAction($realType, $employee);

        return response()->json(['success'=>true, 'redirect'=>route('badge.pointage')]);
    }

    private function calcTotalTime($shift)
    {
        $entrees = $shift->where('type','entree')->pluck('created_at')->toArray();
        $sorties = $shift->where('type','sortie')->pluck('created_at')->toArray();
        if(!$entrees || !$sorties) return '0h 0m';

        $total = 0;
        $count = min(count($entrees), count($sorties));
        for($i=0;$i<$count;$i++){
            $total += strtotime($sorties[$i]) - strtotime($entrees[$i]);
        }
        $hours = floor($total/3600);
        $minutes = floor(($total%3600)/60);
        return "{$hours}h {$minutes}m";
    }

    private function calcTotalPause($shift)
    {
        $pauses = $shift->where('type', 'pause')->pluck('created_at')->toArray();
        $retours = $shift->where('type', 'entree')->slice(1)->pluck('created_at')->toArray();
        if(empty($pauses) || empty($retours)) return '0m';

        $total = 0;
        $count = min(count($pauses), count($retours));
        for($i=0; $i<$count; $i++){
            $total += strtotime($retours[$i]) - strtotime($pauses[$i]);
        }
        $minutes = floor($total / 60);
        return $minutes > 0 ? $minutes . 'm' : '0m';
    }

    public function recordAction($type, $employee = null)
    {
        if (!$employee) {
            $user = auth('badge')->user();
            $employee = $user?->employee ?? Employee::first();
        }
        if (!$employee) {
            throw new \Exception('No employee found');
        }
        
        // BadgeRecord
        $record = new BadgeRecord();
        $record->employee_id = $employee->id;
        $record->type = $type;
        $record->save();
        
        // Sync Pointage (RH table)
        $today = now('Africa/Casablanca')->format('Y-m-d');
        $pointage = Pointage::firstOrCreate(
            ['employee_id' => $employee->id, 'date' => $today],
            ['statut' => 'present', 'valide' => false, 'source' => 'badge']
        );
        
        $nowTime = now('Africa/Casablanca')->format('H:i:s');
        if ($type === 'entree') {
            $pointage->heure_entree = $nowTime;
        } elseif ($type === 'sortie') {
            $pointage->heure_sortie = $nowTime;
        }
        $pointage->save();
        
        // Recalcul total heures
        if (method_exists($pointage, 'calculerTotalHeures')) {
            $pointage->calculerTotalHeures();
        }
    }

    public function entree(Request $request)
    {
        $user = auth('badge')->user();
        $employee = $user->employee;
        $this->recordAction('entree', $employee);
        $request->session()->flash('last_type', 'entree');
        return redirect()->route('badge.result');
    }

    public function sortie(Request $request)
    {
        $user = auth('badge')->user();
        $employee = $user->employee;
        $this->recordAction('sortie', $employee);
        $request->session()->flash('last_type', 'sortie');
        return redirect()->route('badge.result');
    }

    public function result(Request $request)
    {
        $user = auth('badge')->user();
        $employee = $user->employee ?? Employee::first();
        if (!$employee) {
            Log::error('No employee for badge user in result', ['user_id' => $user?->id]);
            return redirect()->route('badge.dashboard');
        }
        $today = Carbon::today();
        $shift = BadgeRecord::where('employee_id', $employee->id)
            ->whereDate('created_at', $today)
            ->get();
        
        // Get type from session or default
        $type = $request->session()->get('last_type', 'entree');

        $todayShift = [
            'first_entree' => $shift->where('type','entree')->first()?->created_at?->format('H:i'),
            'last_sortie'  => $shift->where('type','sortie')->last()?->created_at?->format('H:i'),
            'pause_start' => $shift->where('type','pause')->first()?->created_at?->format('H:i'),
            'pause_end' => $shift->where('type','entree')->slice(1)->first()?->created_at?->format('H:i'),
            'pause_display' => $shift->where('type','pause')->count() ? $shift->where('type','pause')->count() . ' pause(s)' : null,
            'total_pause_human' => $this->calcTotalPause($shift),
            'total_human'  => $this->calcTotalTime($shift),
        ];

        $type = $request->session()->get('last_type', 'action');

        return view('badge.result', compact('employee', 'todayShift', 'type'));
    }
}

