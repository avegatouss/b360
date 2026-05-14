<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mnu_clients_menuiserie')) {
            return;
        }

        if (Schema::hasColumn('mnu_clients_menuiserie', 'customer_id')
            && ! Schema::hasColumn('mnu_clients_menuiserie', 'legacy_eshop_customer_id')) {
            Schema::table('mnu_clients_menuiserie', function (Blueprint $table): void {
                $table->unsignedBigInteger('legacy_eshop_customer_id')->nullable()->after('customer_id');
            });

            DB::table('mnu_clients_menuiserie')->update([
                'legacy_eshop_customer_id' => DB::raw('customer_id'),
            ]);

            Schema::table('mnu_clients_menuiserie', function (Blueprint $table): void {
                $table->dropUnique('mnu_clients_instance_customer_unique');
                $table->dropColumn('customer_id');
            });
        }

        Schema::table('mnu_clients_menuiserie', function (Blueprint $table): void {
            if (! Schema::hasColumn('mnu_clients_menuiserie', 'code')) {
                $table->string('code', 50)->nullable()->after('instance_id');
            }
            if (! Schema::hasColumn('mnu_clients_menuiserie', 'type')) {
                $table->string('type', 30)->default('particulier')->after('code');
            }
            if (! Schema::hasColumn('mnu_clients_menuiserie', 'nom')) {
                $table->string('nom', 150)->nullable()->after('type');
            }
            if (! Schema::hasColumn('mnu_clients_menuiserie', 'prenom')) {
                $table->string('prenom', 100)->nullable()->after('nom');
            }
            if (! Schema::hasColumn('mnu_clients_menuiserie', 'raison_sociale')) {
                $table->string('raison_sociale', 200)->nullable()->after('prenom');
            }
            if (! Schema::hasColumn('mnu_clients_menuiserie', 'email')) {
                $table->string('email', 150)->nullable()->after('raison_sociale');
            }
            if (! Schema::hasColumn('mnu_clients_menuiserie', 'telephone_principal')) {
                $table->string('telephone_principal', 30)->nullable()->after('email');
            }
            if (! Schema::hasColumn('mnu_clients_menuiserie', 'telephone_secondaire')) {
                $table->string('telephone_secondaire', 30)->nullable()->after('telephone_principal');
            }
            if (! Schema::hasColumn('mnu_clients_menuiserie', 'adresse')) {
                $table->text('adresse')->nullable()->after('telephone_secondaire');
            }
            if (! Schema::hasColumn('mnu_clients_menuiserie', 'ville')) {
                $table->string('ville', 100)->nullable()->after('adresse');
            }
            if (! Schema::hasColumn('mnu_clients_menuiserie', 'pays')) {
                $table->char('pays', 2)->default('CI')->after('ville');
            }
            if (! Schema::hasColumn('mnu_clients_menuiserie', 'rccm')) {
                $table->string('rccm', 50)->nullable()->after('pays');
            }
            if (! Schema::hasColumn('mnu_clients_menuiserie', 'nif')) {
                $table->string('nif', 50)->nullable()->after('rccm');
            }
            if (! Schema::hasColumn('mnu_clients_menuiserie', 'statut')) {
                $table->string('statut', 30)->default('lead')->after('nif');
            }
            if (! Schema::hasColumn('mnu_clients_menuiserie', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('statut');
            }
            if (! Schema::hasColumn('mnu_clients_menuiserie', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        $this->backfillRequiredNativeFields();

        Schema::table('mnu_clients_menuiserie', function (Blueprint $table): void {
            $table->unique(['instance_id', 'code'], 'mnu_clients_instance_code_unique');
            $table->index(['instance_id', 'statut'], 'mnu_clients_instance_statut_idx');
            $table->index(['instance_id', 'is_active'], 'mnu_clients_instance_active_idx');
            $table->index('legacy_eshop_customer_id', 'mnu_clients_legacy_eshop_customer_idx');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('mnu_clients_menuiserie')) {
            return;
        }

        Schema::table('mnu_clients_menuiserie', function (Blueprint $table): void {
            $table->dropIndex('mnu_clients_legacy_eshop_customer_idx');
            $table->dropIndex('mnu_clients_instance_active_idx');
            $table->dropIndex('mnu_clients_instance_statut_idx');
            $table->dropUnique('mnu_clients_instance_code_unique');
        });

        Schema::table('mnu_clients_menuiserie', function (Blueprint $table): void {
            $table->dropColumn([
                'code',
                'type',
                'nom',
                'prenom',
                'raison_sociale',
                'email',
                'telephone_principal',
                'telephone_secondaire',
                'adresse',
                'ville',
                'pays',
                'rccm',
                'nif',
                'statut',
                'is_active',
                'deleted_at',
            ]);
        });

        if (Schema::hasColumn('mnu_clients_menuiserie', 'legacy_eshop_customer_id')
            && ! Schema::hasColumn('mnu_clients_menuiserie', 'customer_id')) {
            Schema::table('mnu_clients_menuiserie', function (Blueprint $table): void {
                $table->unsignedBigInteger('customer_id')->nullable()->after('instance_id');
            });

            DB::table('mnu_clients_menuiserie')->update([
                'customer_id' => DB::raw('legacy_eshop_customer_id'),
            ]);

            Schema::table('mnu_clients_menuiserie', function (Blueprint $table): void {
                $table->dropColumn('legacy_eshop_customer_id');
            });
        }
    }

    private function backfillRequiredNativeFields(): void
    {
        DB::table('mnu_clients_menuiserie')
            ->whereNull('code')
            ->orderBy('id')
            ->each(function (object $row): void {
                DB::table('mnu_clients_menuiserie')
                    ->where('id', $row->id)
                    ->update([
                        'code' => sprintf('CLI-%s-%04d', now()->format('Y'), (int) $row->id),
                        'nom' => 'Client '.$row->id,
                        'statut' => 'lead',
                        'is_active' => true,
                    ]);
            });
    }
};
