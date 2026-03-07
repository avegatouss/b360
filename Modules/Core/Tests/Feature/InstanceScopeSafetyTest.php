<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Traits\BelongsToInstance;
use Modules\Core\Tests\Helpers\CreatesInstanceContext;

final class InstanceScopeSafetyTest extends TestCase
{
    use CreatesInstanceContext;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::connection('system')->create('business_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->string('name');
            $table->timestamps();
            $table->index(['instance_id']);
        });
    }

    public function test_no_instance_blocks_all_rows(): void
    {
        config(['app.instance_mode' => 'multi']);
        config(['app.instance_db_strategy' => 'shared']);

        $model = new class extends Model {
            use BelongsToInstance;
            protected $connection = 'system';
            protected $table = 'business_records';
            protected $guarded = [];
        };

        $this->assertSame(0, $model::query()->count());
    }

    public function test_instance_scope_filters_and_autofills(): void
    {
        config(['app.instance_mode' => 'multi']);
        config(['app.instance_db_strategy' => 'shared']);

        $i1 = $this->makeInstance('a');
        $i2 = $this->makeInstance('b');

        $model = new class extends Model {
            use BelongsToInstance;
            protected $connection = 'system';
            protected $table = 'business_records';
            protected $guarded = [];
        };

        $this->bindInstance($i1);
        $model::query()->create(['name' => 'R1']); // instance_id auto

        $this->bindInstance($i2);
        $model::query()->create(['name' => 'R2']);

        $this->bindInstance($i1);
        $this->assertSame(['R1'], $model::query()->pluck('name')->all());

        $this->bindInstance($i2);
        $this->assertSame(['R2'], $model::query()->pluck('name')->all());
    }
}
