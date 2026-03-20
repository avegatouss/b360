<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('eshop_codifarm_margin_config')) {
            return;
        }

        $configs = DB::table('eshop_codifarm_margin_config')->get();

        foreach ($configs as $config) {
            // Check if a Revendeur channel already exists for this instance
            $existing = DB::table('eshop_distribution_channels')
                ->where('instance_id', $config->instance_id)
                ->where('slug', 'codifarm')
                ->first();

            if ($existing) {
                $channelId = $existing->id;

                // Update existing channel with revendeur rates
                DB::table('eshop_distribution_channels')
                    ->where('id', $channelId)
                    ->update([
                        'margin_rate'   => $config->saphir_margin_rate,
                        'buy_rate'      => $config->codifarm_buy_rate,
                        'debt_share'    => $config->debt_share,
                        'channel_share' => $config->codifarm_share,
                        'owner_share'   => $config->saphir_share,
                    ]);
            } else {
                $channelId = DB::table('eshop_distribution_channels')->insertGetId([
                    'instance_id'   => $config->instance_id,
                    'name'          => 'CODIFARM',
                    'slug'          => 'codifarm',
                    'code'          => 'CODIFARM',
                    'description'   => 'Canal de distribution CODIFARM (migré automatiquement)',
                    'is_active'     => true,
                    'margin_rate'   => $config->saphir_margin_rate,
                    'buy_rate'      => $config->codifarm_buy_rate,
                    'debt_share'    => $config->debt_share,
                    'channel_share' => $config->codifarm_share,
                    'owner_share'   => $config->saphir_share,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            // Migrate revendeur margin logs into channel margin logs
            $logs = DB::table('eshop_codifarm_margin_logs')
                ->where('instance_id', $config->instance_id)
                ->get();

            foreach ($logs as $log) {
                DB::table('eshop_channel_margin_logs')->insert([
                    'instance_id'  => $log->instance_id,
                    'channel_id'   => $channelId,
                    'order_id'     => $log->order_id,
                    'total_margin' => $log->total_margin,
                    'debt_part'    => $log->debt_part,
                    'channel_part' => $log->codifarm_part,
                    'owner_part'   => $log->saphir_part,
                    'created_at'   => $log->created_at,
                    'updated_at'   => $log->updated_at,
                ]);
            }

            // Update orders: set channel_id where is_revendeur = true
            DB::table('eshop_orders')
                ->where('instance_id', $config->instance_id)
                ->where('is_codifarm', true)
                ->whereNull('channel_id')
                ->update(['channel_id' => $channelId]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('eshop_codifarm_margin_config')) {
            return;
        }

        // For each instance that has a Revendeur channel, reverse the migration
        $channels = DB::table('eshop_distribution_channels')
            ->where('slug', 'codifarm')
            ->get();

        foreach ($channels as $channel) {
            // Remove migrated margin logs (those matching revendeur channel)
            DB::table('eshop_channel_margin_logs')
                ->where('channel_id', $channel->id)
                ->delete();

            // Reset channel_id on orders that were linked to revendeur
            DB::table('eshop_orders')
                ->where('channel_id', $channel->id)
                ->update(['channel_id' => null]);

            // Delete the auto-created channel (only if it was created by this migration)
            if ($channel->code === 'CODIFARM') {
                DB::table('eshop_distribution_channels')
                    ->where('id', $channel->id)
                    ->delete();
            }
        }
    }
};
