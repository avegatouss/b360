<?php

namespace Modules\Eshop360\Http\Controllers\Settings;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Webhook;
use Modules\Eshop360\Services\WebhookService;

class WebhookController extends Controller
{
    public function __construct(private readonly WebhookService $webhookService)
    {
    }

    public function index()
    {
        $instance = CurrentInstance::get();
        $webhooks = Webhook::where('instance_id', $instance->id)
            ->withCount('logs')
            ->latest()
            ->get();

        $availableEvents = WebhookService::EVENTS;

        return view('eshop360::settings.webhooks.index', compact('webhooks', 'availableEvents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'secret' => 'nullable|string|max:100',
            'events' => 'required|array|min:1',
            'events.*' => 'string|in:' . implode(',', WebhookService::EVENTS) . ',*',
            'is_active' => 'boolean',
        ]);

        $validated['instance_id'] = CurrentInstance::get()->id;

        Webhook::create($validated);

        return redirect()->route('eshop360.webhooks.index')
            ->with('success', __('Webhook created.'));
    }

    public function update(Request $request, Webhook $webhook)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'secret' => 'nullable|string|max:100',
            'events' => 'required|array|min:1',
            'events.*' => 'string|in:' . implode(',', WebhookService::EVENTS) . ',*',
            'is_active' => 'boolean',
        ]);

        $webhook->update($validated);

        return redirect()->route('eshop360.webhooks.index')
            ->with('success', __('Webhook updated.'));
    }

    public function destroy(Webhook $webhook)
    {
        $webhook->delete();

        return redirect()->route('eshop360.webhooks.index')
            ->with('success', __('Webhook deleted.'));
    }

    public function ping(Webhook $webhook)
    {
        $result = $this->webhookService->ping($webhook);

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    public function logs(Webhook $webhook)
    {
        $logs = $webhook->logs()->latest('created_at')->paginate(30);

        return view('eshop360::settings.webhooks.logs', compact('webhook', 'logs'));
    }

    public function resetFailures(Webhook $webhook)
    {
        $webhook->update(['failure_count' => 0, 'is_active' => true]);

        return redirect()->back()->with('success', __('Failure counter reset.'));
    }
}
