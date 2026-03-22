<?php

namespace Modules\Eshop360\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Attendance;
use Modules\Eshop360\Models\Employee;
use Modules\Eshop360\Models\EmployeeCommission;
use Modules\Eshop360\Models\EmployeeSalary;
use Modules\Eshop360\Services\HRService;

class EmployeeController extends Controller
{
    public function __construct(private HRService $hrService) {}

    public function index(Request $request)
    {
        $instance = CurrentInstance::get();

        $query = Employee::with('user')
            ->where('instance_id', $instance->id)
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->department, fn ($q, $d) => $q->where('department', $d))
            ->when($request->search, fn ($q, $s) => $q->where(function ($qq) use ($s) {
                $qq->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('position', 'like', "%{$s}%");
            }));

        $fq = clone $query;
        $allIds = (clone $fq)->pluck('id');

        $kpi = (object) [
            'total'           => (clone $fq)->count(),
            'active'          => (clone $fq)->where('status', 'active')->count(),
            'inactive'        => (clone $fq)->whereIn('status', ['inactive', 'terminated'])->count(),
            'total_salary'    => (int) (clone $fq)->where('status', 'active')->sum('salary'),
            'avg_salary'      => (int) (clone $fq)->where('status', 'active')->avg('salary'),
            'with_account'    => (clone $fq)->whereNotNull('user_id')->count(),
            'commissions_month' => (int) EmployeeCommission::whereIn('employee_id', $allIds)->whereMonth('created_at', now()->month)->sum('amount'),
            'present_today'   => Attendance::whereIn('employee_id', $allIds)->whereDate('date', today())->count(),
        ];

        // Departments for filter
        $departments = Employee::where('instance_id', $instance->id)
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->sort();

        $employees = $query->latest()->paginate(20)->withQueryString();

        return view('eshop360::hr.employees.index', compact('employees', 'kpi', 'departments'));
    }

    public function create()
    {
        return view('eshop360::hr.employees.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:50',
            'position'        => 'required|string|max:255',
            'department'      => 'nullable|string|max:255',
            'salary'          => 'required|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'joined_at'       => 'nullable|date',
            'user_id'         => 'nullable|exists:users,id',
        ]);
        $validated['instance_id'] = CurrentInstance::get()->id;
        Employee::create($validated);
        return redirect()->route('eshop360.hr.employees.index', $request->route('slug'))
            ->with('success', __('Employe cree avec succes.'));
    }

    public function show(string $slug, Employee $employee)
    {
        $employee->load('user', 'salaries', 'commissions.order', 'attendances');

        $stats = [
            'total_salary_paid'   => (int) $employee->salaries->whereNotNull('paid_at')->sum('net_amount'),
            'total_salary_pending'=> (int) $employee->salaries->whereNull('paid_at')->sum('net_amount'),
            'total_commissions'   => (int) $employee->commissions->sum('amount'),
            'unpaid_commissions'  => (int) $employee->commissions->whereNull('paid_at')->sum('amount'),
            'days_present_month'  => $employee->attendances->where('date', '>=', now()->startOfMonth())->count(),
            'hours_month'         => round($employee->attendances->where('date', '>=', now()->startOfMonth())->sum('hours_worked'), 1),
            'tenure_months'       => $employee->joined_at ? $employee->joined_at->diffInMonths(now()) : null,
        ];

        return view('eshop360::hr.employees.show', compact('employee', 'stats'));
    }

    public function edit(string $slug, Employee $employee)
    {
        return view('eshop360::hr.employees.edit', compact('employee'));
    }

    public function update(Request $request, string $slug, Employee $employee)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:50',
            'position'        => 'required|string|max:255',
            'department'      => 'nullable|string|max:255',
            'salary'          => 'required|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'status'          => 'nullable|in:active,inactive,terminated',
        ]);
        $employee->update($validated);
        return redirect()->back()->with('success', __('Employe mis a jour.'));
    }

    public function destroy(string $slug, Employee $employee)
    {
        $employee->delete();
        return redirect()->route('eshop360.hr.employees.index', $slug)->with('success', __('Employe supprime.'));
    }

    public function createUserAccount(Request $request, string $slug, Employee $employee)
    {
        if ($employee->user_id) {
            return redirect()->back()->with('error', __('Cet employe possede deja un compte utilisateur.'));
        }

        $validated = $request->validate([
            'email'    => 'required|email|unique:system.users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $instance = CurrentInstance::get();

        $user = User::create([
            'full_name' => $employee->name,
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'phone'     => $employee->phone,
            'is_active' => true,
        ]);

        DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $instance->id,
            'user_id'     => $user->id,
            'status'      => 'active',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $employee->update(['user_id' => $user->id]);

        return redirect()->back()->with('success', __('Compte utilisateur cree pour :name.', ['name' => $employee->name]));
    }
}
