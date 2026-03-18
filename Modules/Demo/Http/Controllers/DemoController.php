<?php

namespace Modules\Demo\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Demo\Services\DemoManager;

final class DemoController extends Controller
{
    public function __construct(
        private readonly DemoManager $demoManager,
    ) {}

    public function index(string $slug)
    {
        $instance = CurrentInstance::get();
        $providers = $this->demoManager->providers();
        $byModule = $this->demoManager->byModule();

        return view('demo::index', compact('instance', 'providers', 'byModule'));
    }

    public function seed(Request $request, string $slug)
    {
        $instance = CurrentInstance::get();
        $providerId = $request->input('provider');

        if ($providerId) {
            $result = $this->demoManager->seed($providerId, $instance->id);

            return redirect()->route('demo.index', $instance->slug)
                ->with($result['success'] ? 'status' : 'error', $result['message']);
        }

        $results = $this->demoManager->seedAll($instance->id);
        $all = collect($results);
        $succeeded = $all->where('success', true);
        $failures = $all->where('success', false);

        // Build detailed report
        $report = [];
        foreach ($results as $id => $r) {
            $report[] = ($r['success'] ? '  OK' : '  ECHEC') . "  {$id}: {$r['message']}";
        }

        if ($failures->isEmpty()) {
            return redirect()->route('demo.index', $instance->slug)
                ->with('status', "Toutes les donnees de demo ont ete installees ({$succeeded->count()} providers).");
        }

        // Log failures for debugging
        \Illuminate\Support\Facades\Log::warning('Demo seed failures', [
            'instance_id' => $instance->id,
            'failures' => $failures->toArray(),
        ]);

        $message = "{$failures->count()} provider(s) en echec sur {$all->count()}.\n\n"
            . "Succes ({$succeeded->count()}):\n"
            . $succeeded->keys()->map(fn ($id) => "  - {$id}")->implode("\n")
            . "\n\nEchecs ({$failures->count()}):\n"
            . $failures->map(fn ($r, $id) => "  - {$id}: {$r['message']}")->implode("\n");

        return redirect()->route('demo.index', $instance->slug)
            ->with('error', $message);
    }

    public function reset(Request $request, string $slug)
    {
        $instance = CurrentInstance::get();
        $providerId = $request->input('provider');

        if ($providerId) {
            $result = $this->demoManager->reset($providerId, $instance->id);
        } else {
            $results = $this->demoManager->resetAll($instance->id);
            $failed = collect($results)->where('success', false)->count();
            $result = [
                'success' => $failed === 0,
                'message' => 'Donnees de demo reinitialisees.',
            ];
        }

        return redirect()->route('demo.index', $instance->slug)
            ->with($result['success'] ? 'status' : 'error', $result['message']);
    }
}
