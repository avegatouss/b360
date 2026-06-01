<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('eshop_loans', function (Blueprint $table) {
            $table->string('reference')->nullable()->after('instance_id');
            $table->enum('type', ['given', 'received'])->default('given')->after('reference');
            $table->string('party_name')->nullable()->after('party_id');
            $table->date('start_date')->nullable()->after('duration_months');
            $table->date('due_date')->nullable()->after('start_date');
            $table->unsignedBigInteger('account_id')->nullable()->after('due_date');
            $table->unsignedBigInteger('created_by')->nullable()->after('notes');
        });

        // Loan schedule (echeancier)
        Schema::create('eshop_loan_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('eshop_loans')->cascadeOnDelete();
            $table->integer('installment_number');
            $table->date('due_date');
            $table->decimal('principal', 15, 2)->default(0);
            $table->decimal('interest', 15, 2)->default(0);
            $table->decimal('total_due', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->enum('status', ['pending', 'paid', 'partial', 'overdue'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_loan_schedules');
        Schema::table('eshop_loans', function (Blueprint $table) {
            $table->dropColumn(['reference', 'type', 'party_name', 'start_date', 'due_date', 'account_id', 'created_by']);
        });
    }
};
