<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Modules\Auth\Models\IpRule;
use Modules\Core\Support\CurrentInstance;

class IpRuleController extends Controller
{
    public function index()
    {
        $instance = CurrentInstance::get();
        $rules = IpRule::with(['user', 'creator'])
            ->orderByDesc('created_at')
            ->get();

        return view('authmod::ip-rules.index', [
            'instance' => $instance,
            'rules'    => $rules,
        ]);
    }

    public function store(Request $request)
    {
        $instance = CurrentInstance::get();

        $validated = $request->validate([
            'ip_address' => ['required', 'ip'],
            'type'       => ['required', 'in:allow,deny'],
            'user_id'    => ['nullable', 'integer', 'exists:users,id'],
            'note'       => ['nullable', 'string', 'max:255'],
        ]);

        $validated['created_by'] = $request->user()->id;

        IpRule::create($validated);

        // Invalidate cache
        Cache::forget("ip_rules:{$instance->id}");

        return redirect()->route('ip-rules.index', $instance->slug)
            ->with('status', 'Regle IP ajoutee avec succes.');
    }

    public function destroy(IpRule $ipRule)
    {
        $instance = CurrentInstance::get();

        $ipRule->delete();

        // Invalidate cache
        Cache::forget("ip_rules:{$instance->id}");

        return redirect()->route('ip-rules.index', $instance->slug)
            ->with('status', 'Regle IP supprimee.');
    }
}
