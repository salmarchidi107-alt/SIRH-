<?php

namespace App\Http\Controllers;

use App\Models\{Equipment, EquipmentCategory, EquipmentAssignment, EquipmentDischarge, Employee};
use App\Enums\EmployeeStatus;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class EquipmentController extends Controller
{
    // Plus de __construct() / middleware() ici — Laravel 12 a retiré
    // cette API des contrôleurs. Le middleware 'auth' est appliqué
    // directement sur le groupe de routes (voir routes/web.php).

    // ── Catalogue (RH/Admin) ─────────────────────
    public function catalogue(Request $request)
    {
        abort_unless(auth()->user()->canView('equipment'), 403);

        $equipments = Equipment::with('category')
            ->when($request->category, fn($q) => $q->where('equipment_category_id', $request->category))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => Equipment::count(),
            'affectes' => Equipment::where('status', 'affecte')->count(),
            'disponibles' => Equipment::where('status', 'disponible')->count(),
            'maintenance' => Equipment::where('status', 'maintenance')->count(),
            'valeur_parc' => Equipment::sum('value'),
        ];

        return view('equipment.catalogue', compact('equipments', 'stats') + ['categories' => EquipmentCategory::all()]);
    }

    public function storeEquipment(Request $request)
    {
        abort_unless(auth()->user()->canCreate('equipment'), 403);

        $data = $request->validate([
            'reference' => 'required',
            'designation' => 'required',
            'equipment_category_id' => 'required|exists:equipment_categories,id',
            'brand' => 'nullable',
            'serial_number' => 'nullable',
            'condition' => 'required',
            'value' => 'nullable|numeric',
        ]);

        Equipment::create($data + ['status' => 'disponible']);

        return back()->with('success', 'Équipement ajouté au catalogue.');
    }

    // ── Affectation (RH/Admin) ───────────────────
    public function assignForm()
    {
        abort_unless(auth()->user()->canCreate('equipment'), 403);

        return view('equipment.assign', [
            'equipments' => Equipment::where('status', 'disponible')->get(),
            'employees' => Employee::active()->get(),
        ]);
    }

    public function storeAssignment(Request $request)
    {
        abort_unless(auth()->user()->canCreate('equipment'), 403);

        $data = $request->validate([
            'equipment_id' => 'required|exists:equipments,id',
            'employee_id' => 'required|exists:employees,id',
            'assigned_at' => 'required|date',
            'condition_at_assignment' => 'required',
        ]);

        $assignment = EquipmentAssignment::create($data + [
            'status' => 'active',
            'assigned_by' => auth()->id(),
        ]);

        Equipment::find($data['equipment_id'])->update(['status' => 'affecte']);

        $discharge = $this->generateDischarge($assignment, 'remise');

        return redirect()->route('equipment.discharge', $discharge)
            ->with('success', 'Équipement affecté et décharge générée.');
    }

    // ── Fiche salarié (RH/Admin: tout ; employé: la sienne en lecture) ──
    public function employeeShow(Employee $employee)
    {
        $user = auth()->user();
        $canManage = $user->canView('equipment') && in_array($user->role, ['admin', 'rh']);
        $isOwnRecord = $user->employee_id === $employee->id;

        abort_unless($canManage || ($isOwnRecord && $user->canView('equipment')), 403);

        return view('equipment.employee', [
            'employee' => $employee->load('equipmentAssignments.equipment.category'),
            'readonly' => !$canManage,
        ]);
    }

    // ── Décharge ──────────────────────────────────
    public function discharge(EquipmentDischarge $discharge)
    {
        $user = auth()->user();
        $discharge->load('assignment.equipment', 'assignment.employee');
        $canManage = $user->canView('equipment') && in_array($user->role, ['admin', 'rh']);
        $isOwnRecord = $user->employee_id === $discharge->assignment->employee_id;

        abort_unless($canManage || ($isOwnRecord && $user->canView('equipment')), 403);

        return view('equipment.discharge', [
            'discharge' => $discharge,
            'readonly' => !$canManage,
            'pending' => $canManage
                ? EquipmentDischarge::where('status', 'en_attente')->with('assignment.employee')->get()
                : collect(),
        ]);
    }

    public function dischargePdf(EquipmentDischarge $discharge)
    {
        $user = auth()->user();
        $discharge->load('assignment.equipment', 'assignment.employee');
        $canManage = $user->canView('equipment') && in_array($user->role, ['admin', 'rh']);
        $isOwnRecord = $user->employee_id === $discharge->assignment->employee_id;

        abort_unless($canManage || ($isOwnRecord && $user->canView('equipment')), 403);

        $pdf = Pdf::loadView('equipment.discharge-pdf', compact('discharge'));
        return $pdf->download("decharge-{$discharge->reference}.pdf");
    }

    public function dischargeSign(EquipmentDischarge $discharge)
    {
        abort_unless(auth()->user()->canEdit('equipment'), 403);

        $discharge->update(['status' => 'signee', 'signed_at' => now()]);
        return back()->with('success', 'Décharge marquée comme signée.');
    }

    private function generateDischarge(EquipmentAssignment $assignment, string $type): EquipmentDischarge
    {
        $year = now()->year;
        $count = EquipmentDischarge::whereYear('created_at', $year)->count() + 1;

        return EquipmentDischarge::create([
            'reference' => "DCH-{$year}-" . str_pad($count, 5, '0', STR_PAD_LEFT),
            'equipment_assignment_id' => $assignment->id,
            'type' => $type,
            'status' => 'en_attente',
        ]);
    }

    // ── Retours / Départ (RH/Admin) ──────────────
    public function returns()
    {
        abort_unless(auth()->user()->canEdit('equipment'), 403);

        return view('equipment.returns', [
            'departing' => Employee::where('status', EmployeeStatus::Inactive->value)
                ->whereHas('equipmentAssignments', fn($q) => $q->where('status', 'active'))
                ->with('activeEquipmentAssignments.equipment')
                ->get(),
        ]);
    }

    public function storeReturn(Request $request, EquipmentAssignment $assignment)
    {
        abort_unless(auth()->user()->canEdit('equipment'), 403);

        $data = $request->validate([
            'returned_at' => 'required|date',
            'condition_at_return' => 'required',
            'return_notes' => 'nullable|string',
        ]);

        $assignment->update($data + ['status' => 'returned']);
        $assignment->equipment->update(['status' => 'disponible']);

        $discharge = $this->generateDischarge($assignment, 'restitution');

        return back()->with('success', 'Restitution enregistrée — décharge ' . $discharge->reference . ' générée.');
    }
}
