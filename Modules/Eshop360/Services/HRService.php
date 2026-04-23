<?php

namespace Modules\Eshop360\Services;

use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Log;
use Modules\Eshop360\Models\Attendance;
use Modules\Eshop360\Models\Employee;
use Modules\Eshop360\Models\EmployeeCommission;
use Modules\Eshop360\Models\EmployeeSalary;
use Modules\Eshop360\Models\Order;

class HRService
{
    /**
     * Process monthly salary for an employee
     */
    public function processSalary(Employee $employee, string $period, float $bonus = 0, float $deductions = 0, ?string $notes = null): EmployeeSalary
    {
        $netAmount = $employee->salary + $bonus - $deductions;

        return EmployeeSalary::create([
            'employee_id' => $employee->id,
            'amount' => $employee->salary,
            'period' => $period,
            'bonus' => $bonus,
            'deductions' => $deductions,
            'net_amount' => $netAmount,
            'notes' => $notes,
        ]);
    }

    /**
     * Auto-calculate commission when an order is completed, based on
     * employee_id on the order.
     *
     * R-004 — Idempotence à 2 couches :
     *   1. Guard applicatif : check `exists()` avant `create()` (fast path).
     *   2. Filet SGBD : contrainte UNIQUE (order_id, employee_id) sur
     *      `eshop_employee_commissions` (migration P0 2026_04_04_200001).
     *
     * Entre les deux persiste une race window théorique (deux requêtes
     * simultanées voient `exists()=false` avant d'insérer). Le filet SGBD
     * ferme cette race : la seconde insertion lève
     * `UniqueConstraintViolationException`, que nous traduisons
     * silencieusement en `return` idempotent (pas un 500).
     *
     * Voir ADR-005-commission-idempotency-strategy.
     */
    public function calculateCommissionForSale(Order $order): void
    {
        $employeeId = $order->employee_id ?? null;
        if (! $employeeId) {
            return;
        }

        // Idempotence guard (fast path) — skip if commission already recorded
        if (EmployeeCommission::where('order_id', $order->id)->where('employee_id', $employeeId)->exists()) {
            return;
        }

        $employee = Employee::find($employeeId);
        if (! $employee || (float) $employee->commission_rate <= 0) {
            return;
        }

        $commissionAmount = round((float) $order->total * ((float) $employee->commission_rate / 100), 2);

        try {
            EmployeeCommission::create([
                'employee_id' => $employee->id,
                'order_id' => $order->id,
                'amount' => $commissionAmount,
                'rate' => $employee->commission_rate,
            ]);
        } catch (UniqueConstraintViolationException $e) {
            // Race gagnée par une requête concurrente : la commission a
            // été créée entre notre check et notre insert. Le résultat
            // final est identique (une seule commission pour cet ordre),
            // on journalise et on sort proprement.
            Log::info('Commission already recorded (race resolved by DB constraint)', [
                'order_id' => $order->id,
                'employee_id' => $employeeId,
            ]);
        }
    }

    /**
     * Clock in an employee
     */
    public function clockIn(Employee $employee): Attendance
    {
        return Attendance::create([
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
            'clock_in' => now(),
        ]);
    }

    /**
     * Clock out an employee
     */
    public function clockOut(Attendance $attendance): Attendance
    {
        $clockOut = now();
        $hoursWorked = Carbon::parse($attendance->clock_in)->diffInMinutes($clockOut) / 60;

        $attendance->update([
            'clock_out' => $clockOut,
            'hours_worked' => round($hoursWorked, 2),
        ]);

        return $attendance;
    }

    /**
     * Get attendance report for a period
     */
    public function getAttendanceReport(int $instanceId, string $from, string $to): array
    {
        $employees = Employee::where('instance_id', $instanceId)
            ->where('status', 'active')
            ->with(['attendance' => function ($q) use ($from, $to) {
                $q->whereBetween('date', [$from, $to]);
            }])
            ->get();

        return $employees->map(function ($emp) {
            $totalHours = $emp->attendance->sum('hours_worked');
            $daysPresent = $emp->attendance->count();

            return [
                'employee' => $emp,
                'days_present' => $daysPresent,
                'total_hours' => round($totalHours, 2),
                'average_hours' => $daysPresent > 0 ? round($totalHours / $daysPresent, 2) : 0,
            ];
        })->toArray();
    }

    /**
     * Get commission summary for an employee
     */
    public function getCommissionSummary(Employee $employee, ?string $from = null, ?string $to = null): array
    {
        $query = EmployeeCommission::where('employee_id', $employee->id);
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        return [
            'total_commissions' => $query->sum('amount'),
            'paid_commissions' => (clone $query)->whereNotNull('paid_at')->sum('amount'),
            'unpaid_commissions' => (clone $query)->whereNull('paid_at')->sum('amount'),
            'count' => $query->count(),
        ];
    }
}
