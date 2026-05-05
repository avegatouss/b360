<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Eshop360\Domain\HR\Models\Attendance;
use Modules\Eshop360\Domain\HR\Models\Employee;
use Modules\Eshop360\Domain\HR\Models\EmployeeSalary;
use Modules\Eshop360\Tests\TestCase;
use Spatie\Permission\Models\Permission;

/**
 * Additional HR tests complementing HRControllerTest.
 * Covers salary net computation, permission-based access, and clock-out hours_worked.
 */
final class HRTest extends TestCase
{
    private function makePermissions(): void
    {
        foreach (['eshop.hr.view', 'eshop.hr.manage'] as $perm) {
            Permission::findOrCreate($perm);
        }
    }

    public function test_store_employee_persists_salary_field(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);
        $this->makePermissions();

        $response = $this->actingAs($user)
            ->post(route('eshop360.hr.employees.store', $instance->slug), [
                'name' => 'Adjoua Kone',
                'email' => 'adjoua.kone@pharma.test',
                'phone' => '+22507000002',
                'position' => 'Magasiniere',
                'department' => 'Stock',
                'salary' => 220000,
                'joined_at' => now()->toDateString(),
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('eshop_employees', [
            'instance_id' => $instance->id,
            'name' => 'Adjoua Kone',
            'salary' => 220000,
            'department' => 'Stock',
        ]);
    }

    public function test_process_salary_net_equals_base_plus_bonus_minus_deductions(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);
        $this->makePermissions();

        $employee = Employee::create([
            'instance_id' => $instance->id,
            'name' => 'Paul Bamba',
            'email' => 'paul.bamba@pharma.test',
            'position' => 'Comptable',
            'salary' => 300000,
            'commission_rate' => 0,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->post(route('eshop360.hr.salaries.process', $instance->slug), [
                'employee_id' => $employee->id,
                'period' => '2026-03',
                'bonus' => 30000,
                'deductions' => 10000,
                'notes' => 'Salaire mars avec prime',
            ]);

        $response->assertRedirect();

        $salary = EmployeeSalary::where('employee_id', $employee->id)
            ->where('period', '2026-03')
            ->firstOrFail();

        // net = 300000 + 30000 - 10000 = 320000
        $this->assertSame(320000.0, (float) $salary->net_amount);
        $this->assertSame(30000.0, (float) $salary->bonus);
        $this->assertSame(10000.0, (float) $salary->deductions);
        $this->assertSame('2026-03', $salary->period);
    }

    public function test_process_salary_with_no_bonus_no_deductions(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);
        $this->makePermissions();

        $employee = Employee::create([
            'instance_id' => $instance->id,
            'name' => 'Christelle Yao',
            'email' => 'christelle.yao@pharma.test',
            'position' => 'Caissiere',
            'salary' => 180000,
            'commission_rate' => 0,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post(route('eshop360.hr.salaries.process', $instance->slug), [
                'employee_id' => $employee->id,
                'period' => '2026-02',
                'bonus' => 0,
                'deductions' => 0,
            ]);

        $salary = EmployeeSalary::where('employee_id', $employee->id)
            ->where('period', '2026-02')
            ->firstOrFail();

        // net = 180000 + 0 - 0 = 180000
        $this->assertSame(180000.0, (float) $salary->net_amount);
    }

    public function test_clock_in_then_clock_out_records_hours_worked(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);
        $this->makePermissions();

        $employee = Employee::create([
            'instance_id' => $instance->id,
            'name' => 'Bob Traore',
            'email' => 'bob.traore@pharma.test',
            'position' => 'Livreur',
            'salary' => 150000,
            'commission_rate' => 0,
            'status' => 'active',
        ]);

        // Clock in via controller
        $this->actingAs($user)
            ->post(route('eshop360.hr.attendance.clock-in', $instance->slug), [
                'employee_id' => $employee->id,
            ]);

        $attendance = Attendance::where('employee_id', $employee->id)->firstOrFail();
        $this->assertNull($attendance->clock_out);

        // Clock out via controller
        $response = $this->post(
            route('eshop360.hr.attendance.clock-out-by-id', [$instance->slug, $attendance->id])
        );

        $response->assertRedirect();

        $attendance->refresh();
        $this->assertNotNull($attendance->clock_out);
        $this->assertNotNull($attendance->hours_worked);
        $this->assertGreaterThanOrEqual(0.0, (float) $attendance->hours_worked);
    }

    public function test_employee_index_returns_ok(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);
        $this->makePermissions();

        Employee::create([
            'instance_id' => $instance->id,
            'name' => 'Test Employe Index',
            'email' => 'test.index@pharma.test',
            'position' => 'Agent',
            'salary' => 100000,
            'commission_rate' => 0,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->get(route('eshop360.hr.employees.index', $instance->slug));

        $response->assertOk();
        $response->assertSee('Test Employe Index');
    }
}
