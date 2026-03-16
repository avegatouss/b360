<?php

namespace Modules\Eshop360\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Eshop360\Models\Employee;
use Modules\Eshop360\Services\HRService;
use Modules\Core\Support\CurrentInstance;

class EmployeeController extends Controller
{
    public function __construct(private HRService $hrService) {}

    public function index()
    {
        $instance = CurrentInstance::get();
        $employees = Employee::where('instance_id', $instance->id)->latest()->paginate(20);
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
        $employee->load('salaries', 'commissions.order', 'attendance');
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
}
