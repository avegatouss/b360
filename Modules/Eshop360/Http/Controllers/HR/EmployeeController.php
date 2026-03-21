<?php

namespace Modules\Eshop360\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Eshop360\Models\Employee;
use Modules\Eshop360\Services\HRService;
use Modules\Core\Support\CurrentInstance;

class EmployeeController extends Controller
{
    public function __construct(private HRService $hrService) {}

    public function index()
    {
        $instance = CurrentInstance::get();
        $employees = Employee::with('user')->where('instance_id', $instance->id)->latest()->paginate(20);
        return view('eshop360::hr.employees.index', compact('employees'));
    }

    public function create()
    {
        return view('eshop360::hr.employees.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'position' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'salary' => 'required|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'joined_at' => 'nullable|date',
            'user_id' => 'nullable|exists:users,id',
        ]);
        $validated['instance_id'] = CurrentInstance::get()->id;
        Employee::create($validated);
        return redirect()->back()->with('success', 'Employee created.');
    }

    public function show(string $slug, Employee $employee)
    {
        $employee->load('user', 'salaries', 'commissions.order', 'attendances');
        return view('eshop360::hr.employees.show', compact('employee'));
    }

    public function edit(string $slug, Employee $employee)
    {
        return view('eshop360::hr.employees.edit', compact('employee'));
    }

    public function update(Request $request, string $slug, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'position' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'salary' => 'required|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|in:active,inactive,terminated',
        ]);
        $employee->update($validated);
        return redirect()->back()->with('success', 'Employee updated.');
    }

    public function destroy(string $slug, Employee $employee)
    {
        $employee->delete();
        return redirect()->back()->with('success', 'Employee deleted.');
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

        // Attach user to current instance
        DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $instance->id,
            'user_id'     => $user->id,
            'status'      => 'active',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Link employee to user
        $employee->update(['user_id' => $user->id]);

        return redirect()->back()->with('success', __('Compte utilisateur cree avec succes pour :name.', ['name' => $employee->name]));
    }
}
