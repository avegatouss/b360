<?php

namespace Modules\Eshop360\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\HR\Models\Attendance;
use Modules\Eshop360\Domain\HR\Models\Employee;
use Modules\Eshop360\Services\HRService;

class AttendanceController extends Controller
{
    public function __construct(private HRService $hrService) {}

    public function index(Request $request)
    {
        $instance = CurrentInstance::get();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $date = $request->input('date');

        $query = Attendance::whereHas('employee', fn ($q) => $q->where('instance_id', $instance->id))
            ->with('employee');

        if ($dateFrom && $dateTo) {
            $query->whereBetween('date', [$dateFrom, $dateTo]);
        } elseif ($date) {
            $query->where('date', $date);
            $dateFrom = $date;
            $dateTo = $date;
        } else {
            // Default: show today, or latest date with data
            $today = now()->toDateString();
            $query->where('date', $today);
            $dateFrom = $today;
            $dateTo = $today;

            $testCount = (clone $query)->count();
            if ($testCount === 0) {
                $latestDate = Attendance::whereHas('employee', fn ($q) => $q->where('instance_id', $instance->id))
                    ->orderByDesc('date')->value('date');
                if ($latestDate) {
                    $dateFrom = $latestDate instanceof \Carbon\Carbon ? $latestDate->toDateString() : (string) $latestDate;
                    $dateTo = $dateFrom;
                    $query = Attendance::whereHas('employee', fn ($q) => $q->where('instance_id', $instance->id))
                        ->with('employee')
                        ->where('date', $dateFrom);
                }
            }
        }

        $attendances = $query->orderByDesc('date')->orderBy('employee_id')->get();

        $employees = Employee::where('instance_id', $instance->id)->where('status', 'active')->orderBy('name')->get();

        $recentDates = Attendance::whereHas('employee', fn ($q) => $q->where('instance_id', $instance->id))
            ->selectRaw('date, count(*) as cnt')
            ->groupBy('date')
            ->orderByDesc('date')
            ->limit(15)
            ->get();

        return view('eshop360::hr.attendance.index', compact('attendances', 'employees', 'dateFrom', 'dateTo', 'recentDates'));
    }

    public function clockIn(Request $request)
    {
        $validated = $request->validate(['employee_id' => 'required|exists:eshop_employees,id']);
        $employee = Employee::where('instance_id', CurrentInstance::get()->id)->findOrFail($validated['employee_id']);
        $this->hrService->clockIn($employee);

        return redirect()->back()->with('success', __(':name pointe a l\'entree.', ['name' => $employee->name]));
    }

    public function clockOut(Request $request, string $slug, ?Attendance $attendance = null): RedirectResponse
    {
        $instance = CurrentInstance::get();

        if (! $attendance) {
            $attendanceId = $request->input('attendance_id');
            $attendance = Attendance::whereHas('employee', fn ($q) => $q->where('instance_id', $instance->id))
                ->findOrFail($attendanceId);
        } else {
            abort_unless($attendance->employee && (int) $attendance->employee->instance_id === (int) $instance->id, 403);
        }

        $this->hrService->clockOut($attendance);

        return redirect()->back()->with('success', __('Sortie enregistree.'));
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
