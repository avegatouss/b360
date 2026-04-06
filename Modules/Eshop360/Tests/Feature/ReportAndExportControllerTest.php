<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Eshop360\Models\Account;
use Modules\Eshop360\Models\Brand;
use Modules\Eshop360\Models\Category;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\Employee;
use Modules\Eshop360\Models\EmployeeCommission;
use Modules\Eshop360\Models\Expense;
use Modules\Eshop360\Models\ExpenseCategory;
use Modules\Eshop360\Models\Income;
use Modules\Eshop360\Models\IncomeSource;
use Modules\Eshop360\Models\Invoice;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\OrderItem;
use Modules\Eshop360\Models\Payment;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\PurchaseOrder;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Models\StockMovement;
use Modules\Eshop360\Models\Supplier;
use Modules\Eshop360\Models\Warehouse;
use Modules\Eshop360\Tests\TestCase;
use ZipArchive;

final class ReportAndExportControllerTest extends TestCase
{
    public function test_advanced_reports_render_with_real_data_contracts(): void
    {
        [$instance, $user] = $this->seedReportingContext();
        $query = [
            'slug' => $instance->slug,
            'from' => now()->startOfDay()->toDateString(),
            'to' => now()->toDateString(),
        ];

        // Note: XOF currency has 0 decimal places, so format_currency() renders integers.
        // Assertions use integer strings to match the actual view output.

        $this->actingAs($user)
            ->get(route('eshop360.reports.overview', $query))
            ->assertOk()
            ->assertSee("Vue d'ensemble")
            ->assertSee('Produit Test')
            ->assertSee('Client Test');

        $this->actingAs($user)
            ->get(route('eshop360.reports.cashbook', $query))
            ->assertOk()
            ->assertSee('Livre de caisse')
            ->assertSee('PAY-001')
            ->assertSee('Frais logistiques');

        $this->actingAs($user)
            ->get(route('eshop360.reports.sales-by-product', $query))
            ->assertOk()
            ->assertSee('Produit Test');

        $this->actingAs($user)
            ->get(route('eshop360.reports.sales-by-category', $query))
            ->assertOk()
            ->assertSee('Pharmacie');

        $this->actingAs($user)
            ->get(route('eshop360.reports.customer-dues', ['slug' => $instance->slug]))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('eshop360.reports.supplier-dues', ['slug' => $instance->slug]))
            ->assertOk()
            ->assertSee('Fournisseur Test');

        $this->actingAs($user)
            ->get(route('eshop360.reports.profit-loss', $query))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('eshop360.reports.stock-report', ['slug' => $instance->slug]))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('eshop360.reports.tax', $query))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('eshop360.reports.commissions', $query))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('eshop360.reports.monthly-revenue', ['slug' => $instance->slug, 'year' => now()->year]))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('eshop360.reports.monthly-expenses', ['slug' => $instance->slug, 'year' => now()->year]))
            ->assertOk();
    }

    public function test_export_endpoints_return_csv_and_xlsx_with_real_fields(): void
    {
        [$instance, $user] = $this->seedReportingContext();

        $productsCsv = $this->actingAs($user)->get(route('eshop360.export.products', [
            'slug' => $instance->slug,
        ]));

        $productsCsv->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $productsContent = $productsCsv->streamedContent();
        $this->assertStringContainsString('Produit Test', $productsContent);
        $this->assertStringContainsString('18', $productsContent);

        $customersCsv = $this->actingAs($user)->get(route('eshop360.export.customers', [
            'slug' => $instance->slug,
        ]));

        $customersCsv->assertOk();
        $customersContent = $customersCsv->streamedContent();
        $this->assertStringContainsString('Client Test', $customersContent);
        $this->assertStringContainsString('150', $customersContent);

        $purchasesCsv = $this->actingAs($user)->get(route('eshop360.export.purchases', [
            'slug' => $instance->slug,
        ]));

        $purchasesCsv->assertOk();
        $purchasesContent = $purchasesCsv->streamedContent();
        $this->assertStringContainsString('PO-TEST-001', $purchasesContent);
        $this->assertStringContainsString('Fournisseur Test', $purchasesContent);

        $salesExport = $this->actingAs($user)->get(route('eshop360.export.sales', [
            'slug' => $instance->slug,
            'format' => 'xlsx',
        ]));

        $salesExport->assertOk();

        if (!class_exists(ZipArchive::class)) {
            $salesExport->assertHeader('content-type', 'text/csv; charset=UTF-8');
            $this->assertStringContainsString('ORD-TEST-001', $salesExport->streamedContent());

            return;
        }

        $salesExport->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $binary = $salesExport->streamedContent();
        $this->assertStringStartsWith('PK', $binary);

        $tempFile = tempnam(sys_get_temp_dir(), 'eshop-xlsx-test-');
        file_put_contents($tempFile, $binary);

        $zip = new ZipArchive();
        $zip->open($tempFile);
        $worksheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        @unlink($tempFile);

        $this->assertIsString($worksheet);
        $this->assertStringContainsString('ORD-TEST-001', $worksheet);
        $this->assertStringContainsString('Client Test', $worksheet);
    }

    private function seedReportingContext(): array
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);

        $category = Category::create([
            'instance_id' => $instance->id,
            'name' => 'Pharmacie',
            'slug' => 'pharmacie',
            'is_active' => true,
        ]);

        $brand = Brand::create([
            'instance_id' => $instance->id,
            'name' => 'Saphir',
            'slug' => 'saphir',
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'instance_id' => $instance->id,
            'name' => 'Fournisseur Test',
            'phone' => '688000000',
            'country' => 'CM',
            'balance' => 30,
            'is_active' => true,
        ]);

        $warehouse = Warehouse::create([
            'instance_id' => $instance->id,
            'name' => 'Depot Central',
            'code' => 'WH-01',
            'is_active' => true,
        ]);

        $product = Product::create([
            'instance_id' => $instance->id,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'supplier_id' => $supplier->id,
            'name' => 'Produit Test',
            'slug' => 'produit-test',
            'sku' => 'PRD-001',
            'price' => 50,
            'cost_price' => 20,
            'purchase_price_factory' => 18,
            'pght' => 45,
            'tax_rate' => 10,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'unit',
            'min_quantity' => 0,
            'alert_quantity' => 2,
            'stock_alert_quantity' => 2,
            'is_active' => true,
        ]);

        Stock::create([
            'instance_id' => $instance->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 7,
            'reserved_quantity' => 2,
        ]);

        StockMovement::create([
            'instance_id' => $instance->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'in',
            'quantity' => 10,
            'reference_type' => Product::class,
            'reference_id' => $product->id,
            'performed_by' => $user->id,
        ]);

        StockMovement::create([
            'instance_id' => $instance->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'out',
            'quantity' => 3,
            'reference_type' => Product::class,
            'reference_id' => $product->id,
            'performed_by' => $user->id,
        ]);

        $customer = Customer::create([
            'instance_id' => $instance->id,
            'code' => 'CUST-001',
            'name' => 'Client Test',
            'email' => 'client@test.com',
            'phone' => '699000000',
            'address' => 'Douala',
            'wallet_balance' => 12,
            'credit_limit' => 100,
            'is_active' => true,
        ]);

        $orderOne = Order::create([
            'instance_id' => $instance->id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-TEST-001',
            'status' => 'completed',
            'payment_status' => 'partial',
            'payment_method' => 'cash',
            'subtotal' => 90,
            'tax_amount' => 10,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'total' => 100,
            'paid_amount' => 60,
            'due_amount' => 40,
            'source' => 'pos',
            'biller_id' => $user->id,
            'created_at' => now()->subHours(3),
            'updated_at' => now()->subHours(3),
        ]);

        $orderTwo = Order::create([
            'instance_id' => $instance->id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-TEST-002',
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'card',
            'subtotal' => 45,
            'tax_amount' => 5,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'total' => 50,
            'paid_amount' => 50,
            'due_amount' => 0,
            'source' => 'manual',
            'biller_id' => $user->id,
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ]);

        OrderItem::create([
            'order_id' => $orderOne->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'quantity' => 2,
            'unit_price' => 50,
            'discount' => 0,
            'tax' => 10,
            'total' => 100,
        ]);

        OrderItem::create([
            'order_id' => $orderTwo->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'quantity' => 1,
            'unit_price' => 50,
            'discount' => 0,
            'tax' => 5,
            'total' => 50,
        ]);

        Payment::create([
            'instance_id' => $instance->id,
            'payable_type' => Order::class,
            'payable_id' => $orderOne->id,
            'amount' => 60,
            'method' => 'cash',
            'reference' => 'PAY-001',
            'status' => 'completed',
            'received_by' => $user->id,
            'created_at' => now()->subHours(3),
            'updated_at' => now()->subHours(3),
        ]);

        Payment::create([
            'instance_id' => $instance->id,
            'payable_type' => Order::class,
            'payable_id' => $orderTwo->id,
            'amount' => 50,
            'method' => 'card',
            'reference' => 'PAY-002',
            'status' => 'completed',
            'received_by' => $user->id,
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ]);

        Invoice::create([
            'instance_id' => $instance->id,
            'order_id' => $orderOne->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-TEST-001',
            'status' => 'sent',
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => 90,
            'tax_amount' => 10,
            'discount_amount' => 0,
            'total' => 100,
            'paid_amount' => 60,
            'due_amount' => 40,
            'created_by' => $user->id,
        ]);

        PurchaseOrder::create([
            'instance_id' => $instance->id,
            'supplier_id' => $supplier->id,
            'supplier_name' => $supplier->name,
            'supplier_email' => 'supplier@test.com',
            'reference' => 'PO-TEST-001',
            'status' => 'received',
            'total' => 40,
            'paid_amount' => 10,
            'due_amount' => 30,
            'payment_status' => 'partial',
            'notes' => 'Achat test',
            'created_by' => $user->id,
            'created_at' => now()->subHours(4),
            'updated_at' => now()->subHours(4),
        ]);

        $account = Account::create([
            'instance_id' => $instance->id,
            'name' => 'Caisse principale',
            'type' => 'cash',
            'account_number' => 'CAISSE-01',
            'balance' => 0,
            'currency' => 'XAF',
            'is_active' => true,
        ]);

        $expenseCategory = ExpenseCategory::create([
            'instance_id' => $instance->id,
            'name' => 'Logistique',
        ]);

        Expense::create([
            'instance_id' => $instance->id,
            'category_id' => $expenseCategory->id,
            'account_id' => $account->id,
            'amount' => 20,
            'date' => now()->toDateString(),
            'description' => 'Frais logistiques',
            'user_id' => $user->id,
        ]);

        $incomeSource = IncomeSource::create([
            'instance_id' => $instance->id,
            'name' => 'Autres revenus',
        ]);

        Income::create([
            'instance_id' => $instance->id,
            'source_id' => $incomeSource->id,
            'account_id' => $account->id,
            'amount' => 20,
            'date' => now()->toDateString(),
            'description' => 'Prime exceptionnelle',
            'user_id' => $user->id,
        ]);

        $employee = Employee::create([
            'instance_id' => $instance->id,
            'name' => 'Employe Test',
            'email' => 'employee@test.com',
            'position' => 'Commercial',
            'salary' => 0,
            'commission_rate' => 10,
            'status' => 'active',
        ]);

        EmployeeCommission::create([
            'employee_id' => $employee->id,
            'order_id' => $orderOne->id,
            'rate' => 10,
            'amount' => 10,
            'created_at' => now()->subHours(3),
            'updated_at' => now()->subHours(3),
        ]);

        EmployeeCommission::create([
            'employee_id' => $employee->id,
            'order_id' => $orderTwo->id,
            'rate' => 10,
            'amount' => 5,
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ]);

        return [$instance, $user];
    }
}
