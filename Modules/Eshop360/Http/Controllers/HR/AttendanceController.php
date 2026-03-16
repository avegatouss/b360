<?php

namespace Modules\Eshop360\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Modules\Eshop360\Models\Employee;
use Modules\Eshop360\Models\Attendance;
use Modules\Eshop360\Services\HRService;
use Modules\Core\Support\CurrentInstance;

class AttendanceController extends Controller
{
    public function __construct(private HRService $hrService) {}

    public function index()
    {
        $instance = CurrentInstance::get();
        $today = now()->toDateString();
        $attendance = Attendance::whereHas('employee', fn($q) => $q->where('instance_id', $instance->id))
            ->where('date', $today)
            ->with('employee')
            ->get();
        $employees = Employee::where('instance_id', $instance->id)->where('status', 'active')->get();
        return view('eshop360::hr.attendance.index', compact('attendance', 'employees'));
    }

    public function clockIn(Request $request)
    {
        $validated = $request->validate(['employee_id' => 'required|exists:eshop_employees,id']);
        $employee = Employee::findOrFail($validated['employee_id']);
        $this->hrService->clockIn($employee);
        return redirect()->back()->with('success', "{$employee->name} clocked in.");
    }

    public function clockOut(Request $request, string $slug, ?Attendance $attendance = null): RedirectResponse
    {
        if (!$attendance) {
            $attendanceId = $request->input('attendance_id');
            $attendance = Attendance::findOrFail($attendanceId);
        }

        $this->hrService->clockOut($attendance);
        return redirect()->back()->with('success', 'Clocked out.');
    }

    public function report(Request $request)
    {
        $instance = CurrentInstance::get();
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $report = $this->hrService->getAttendanceReport($instance->id, $from, $to);
        return view('eshop360::hr.attendance.report', compact('report', 'from', 'to'));
    }
}
