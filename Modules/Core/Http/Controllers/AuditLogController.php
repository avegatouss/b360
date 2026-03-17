<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\AuditLog;
use Modules\Core\Support\CurrentInstance;

final class AuditLogController extends Controller
{
    /**
     * List audit logs with filters.
     */
    public function index(Request $request, string $slug)
    {
        $instance = CurrentInstance::get();

        // Determine which table to use
        $table = $this->resolveTable();

        $query = DB::table($table)
            ->where("{$table}.instance_id", $instance->id)
            ->leftJoin('users', "{$table}.user_id", '=', 'users.id')
            ->select("{$table}.*", 'users.name as user_name', 'users.email as user_email')
            ->orderByDesc("{$table}.created_at");

        // Filters
        if ($request->filled('user')) {
            $query->where("{$table}.user_id", $request->input('user'));
        }

        if ($request->filled('action')) {
            $query->where("{$table}.action", 'like', '%' . $request->input('action') . '%');
        }

        if ($request->filled('model')) {
            $query->where("{$table}.model", 'like', '%' . $request->input('model') . '%');
        }

        if ($request->filled('date_from')) {
            $query->where("{$table}.created_at", '>=', $request->input('date_from') . ' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->where("{$table}.created_at", '<=', $request->input('date_to') . ' 23:59:59');
        }

        $logs = $query->paginate(25)->appends($request->query());

        // Get unique users for filter dropdown
        $users = DB::table($table)
            ->where('instance_id', $instance->id)
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        $userList = DB::table('users')
            ->whereIn('id', $users)
            ->select('id', 'name', 'email')
            ->get();

        // Get unique actions
        $actions = DB::table($table)
            ->where('instance_id', $instance->id)
            ->distinct()
            ->pluck('action');

        // Get unique models
        $models = DB::table($table)
            ->where('instance_id', $instance->id)
            ->distinct()
            ->pluck('model');

        return view('core::admin.audit-logs.index', compact(
            'instance', 'logs', 'userList', 'actions', 'models'
        ));
    }

    /**
     * Show detail of a single audit log entry.
     */
    public function show(string $slug, int $id)
    {
        $instance = CurrentInstance::get();
        $table = $this->resolveTable();

        $log = DB::table($table)
            ->leftJoin('users', "{$table}.user_id", '=', 'users.id')
            ->select("{$table}.*", 'users.name as user_name', 'users.email as user_email')
            ->where("{$table}.id", $id)
            ->where("{$table}.instance_id", $instance->id)
            ->first();

        if (!$log) {
            abort(404, 'Entree d\'audit introuvable.');
        }

        // Decode JSON values
        $oldValues = is_string($log->old_values) ? json_decode($log->old_values, true) : ($log->old_values ?? []);
        $newValues = is_string($log->new_values) ? json_decode($log->new_values, true) : ($log->new_values ?? []);

        return view('core::admin.audit-logs.show', compact('instance', 'log', 'oldValues', 'newValues'));
    }

    /**
     * Resolve which audit log table exists.
     */
    private function resolveTable(): string
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('audit_logs')) {
            return 'audit_logs';
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('eshop_audit_logs')) {
            return 'eshop_audit_logs';
        }

        return 'audit_logs';
    }
}
