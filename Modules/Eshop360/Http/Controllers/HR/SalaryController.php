<?php

namespace Modules\Eshop360\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Eshop360\Models\Employee;
use Modules\Eshop360\Models\EmployeeSalary;
use Modules\Eshop360\Services\HRService;
use Modules\Core\Support\CurrentInstance;

class SalaryController extends Controller
{
    public function __construct(private HRService $hrService) {}

    public function index()
    {
        $instance = CurrentInstance::get();
        $salaries = EmployeeSalary::whereHas('employee', fn($q) => $q->where('instance_id', $instance->id))
            ->with('employee')
            ->latest()
            ->paginate(20);
        $employees = Employee::where('instance_id', $instance->id)->where('status', 'active')->get();
        return view('eshop360::hr.salaries.index', compact('salaries', 'employees'));
    }

    public function process(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:eshop_employees,id',
            'period' => 'required|string|max:7', // e.g. 2026-03
            'bonus' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        $employee = Employee::findOrFail($validated['employee_id']);
        $this->hrService->processSalary(
            $employee,
            $validated['period'],
            $validated['bonus'] ?? 0,
            $validated['deductions'] ?? 0,
            $validated['notes'] ?? null
        );
        return redirect()->back()->with('success', 'Salary processed.');
    }

    public function markPaid(string $slug, EmployeeSalary $salary)
    {
        $salary->update(['paid_at' => now()]);
        return redirect()->back()->with('success', 'Salary marked as paid.');
    }
}
