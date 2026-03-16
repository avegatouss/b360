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
        } else {
            $results = $this->demoManager->seedAll($instance->id);
            $failed = collect($results)->where('success', false)->count();
            $result = [
                'success' => $failed === 0,
                'message' => $failed === 0
                    ? 'Toutes les donnees de demo ont ete installees.'
                    : "{$failed} provider(s) en echec.",
            ];
        }

        return redirect()->route('demo.index', $instance->slug)
            ->with($result['success'] ? 'status' : 'error', $result['message']);
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
