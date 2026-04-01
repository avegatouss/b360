<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Eshop360\Models\Attendance;
use Modules\Eshop360\Models\Employee;
use Modules\Eshop360\Models\EmployeeCommission;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Warehouse;
use Modules\Eshop360\Services\HRService;
use Modules\Eshop360\Tests\TestCase;

final class HRControllerTest extends TestCase
{
    public function test_can_create_employee(): void
    {
        $instance = $this->makeRootInstance();
        $user     = $this->makeRootSuperAdmin($instance);

        $response = $this->actingAs($user)->post(
            route('eshop360.hr.employees.store', ['slug' => $instance->slug]),
            [
                'name'            => 'Kouame Yao',
                'email'           => 'kouame@saphir.ci',
                'phone'           => '+225 07 50 01 01',
                'position'        => 'Directeur entrepot',
                'department'      => 'Logistique',
                'salary'          => 800000,
                'commission_rate' => 0,
                'status'          => 'active',
            ]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('eshop_employees', [
            'instance_id' => $instance->id,
            'email'       => 'kouame@saphir.ci',
            'department'  => 'Logistique',
        ]);
    }

    public function test_can_process_salary(): void
    {
        $instance = $this->makeRootInstance();
        $user     = $this->makeRootSuperAdmin($instance);

        $employee = Employee::create([
            'instance_id'     => $instance->id,
            'name'            => 'Toure Mariam',
            'email'           => 'mariam@saphir.ci',
            'salary'          => 600000,
            'commission_rate' => 2.5,
            'status'          => 'active',
        ]);

        $response = $this->actingAs($user)->post(
            route('eshop360.hr.salaries.store', ['slug' => $instance->slug]),
            [
                'employee_id' => $employee->id,
                'period'      => now()->format('Y-m'),
                'bonus'       => 50000,
                'deductions'  => 0,
            ]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('eshop_employee_salaries', [
            'employee_id' => $employee->id,
            'amount'      => 600000,
            'bonus'       => 50000,
        ]);
    }

    public function test_clock_in_creates_attendance_record(): void
    {
        $instance = $this->makeRootInstance();
        $user     = $this->makeRootSuperAdmin($instance);

        $employee = Employee::create([
            'instance_id'     => $instance->id,
            'name'            => 'Bamba Seydou',
            'email'           => 'bamba@saphir.ci',
            'salary'          => 350000,
            'commission_rate' => 3.0,
            'status'          => 'active',
        ]);

        $response = $this->actingAs($user)->post(
            route('eshop360.hr.attendance.clock-in', ['slug' => $instance->slug]),
            ['employee_id' => $employee->id]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('eshop_attendance', [
            'employee_id' => $employee->id,
            'date'        => now()->toDateString(),
        ]);
        $attendance = Attendance::where('employee_id', $employee->id)->first();
        $this->assertNotNull($attendance);
        $this->assertNull($attendance->clock_out);
    }

    public function test_clock_out_updates_attendance(): void
    {
        $instance = $this->makeRootInstance();
        $user     = $this->makeRootSuperAdmin($instance);

        $employee = Employee::create([
            'instance_id'     => $instance->id,
            'name'            => 'Diallo Mamadou',
            'email'           => 'diallo@saphir.ci',
            'salary'          => 500000,
            'commission_rate' => 1.0,
            'status'          => 'active',
        ]);

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'date'        => now()->toDateString(),
            'clock_in'    => now()->subHours(8),
        ]);

        $response = $this->actingAs($user)->post(
            route('eshop360.hr.attendance.clock-out', ['slug' => $instance->slug]),
            ['attendance_id' => $attendance->id]
        );

        $response->assertRedirect();
        $this->assertNotNull($attendance->fresh()->clock_out);
        $this->assertNotNull($attendance->fresh()->hours_worked);
    }

    public function test_commission_auto_calculated_when_order_completed(): void
    {
        $instance  = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        $this->actingAs($user);

        $warehouse = Warehouse::create([
            'instance_id' => $instance->id,
            'name'        => 'WH Test',
            'code'        => 'WH-TST',
            'is_active'   => true,
        ]);

        $employee = Employee::create([
            'instance_id'     => $instance->id,
            'name'            => 'Agent Commercial',
            'email'           => 'agent@saphir.ci',
            'salary'          => 300000,
            'commission_rate' => 3.0,
            'status'          => 'active',
        ]);

        $order = Order::create([
            'instance_id'    => $instance->id,
            'employee_id'    => $employee->id,
            'order_number'   => 'ORD-COMM-001',
            'status'         => 'pending',
            'payment_status' => 'paid',
            'subtotal'       => 100000,
            'tax_amount'     => 0,
            'discount_amount'=> 0,
            'total'          => 100000,
            'paid_amount'    => 100000,
            'due_amount'     => 0,
            'source'         => 'pos',
        ]);

        app(HRService::class)->calculateCommissionForSale($order);

        $this->assertDatabaseHas('eshop_employee_commissions', [
            'employee_id' => $employee->id,
            'order_id'    => $order->id,
            'amount'      => 3000.0, // 3% de 100 000
            'rate'        => 3.0,
        ]);
    }

    public function test_no_commission_created_when_employee_rate_is_zero(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        $this->actingAs($user);

        $employee = Employee::create([
            'instance_id'     => $instance->id,
            'name'            => 'Comptable',
            'email'           => 'compta@saphir.ci',
            'salary'          => 550000,
            'commission_rate' => 0,
            'status'          => 'active',
        ]);

        $order = Order::create([
            'instance_id'    => $instance->id,
            'employee_id'    => $employee->id,
            'order_number'   => 'ORD-NOCOMM-001',
            'status'         => 'completed',
            'payment_status' => 'paid',
            'subtotal'       => 50000,
            'tax_amount'     => 0,
            'discount_amount'=> 0,
            'total'          => 50000,
            'paid_amount'    => 50000,
            'due_amount'     => 0,
            'source'         => 'pos',
        ]);

        app(HRService::class)->calculateCommissionForSale($order);

        $this->assertDatabaseMissing('eshop_employee_commissions', [
            'employee_id' => $employee->id,
            'order_id'    => $order->id,
        ]);
    }
}
