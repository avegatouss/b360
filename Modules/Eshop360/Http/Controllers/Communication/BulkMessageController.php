<?php

namespace Modules\Eshop360\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\BulkMessageLog;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\CustomerGroup;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\EmailTemplate;
use Modules\Eshop360\Models\Store;
use Modules\Eshop360\Jobs\SendBulkEmail;
use Modules\Eshop360\Jobs\SendBulkSms;

class BulkMessageController extends Controller
{
    public function compose()
    {
        $instance = CurrentInstance::get();

        $customerGroups = CustomerGroup::where('instance_id', $instance->id)->get();
        $stores = Store::where('instance_id', $instance->id)->where('is_active', true)->get();
        $channels = DistributionChannel::where('instance_id', $instance->id)
            ->where('is_active', true)->orderBy('name')->get();
        $emailTemplates = EmailTemplate::where('instance_id', $instance->id)->where('is_active', true)->get();

        return view('eshop360::communication.bulk.compose', compact(
            'customerGroups',
            'stores',
            'channels',
            'emailTemplates',
        ));
    }

    public function preview(Request $request)
    {
        $instance = CurrentInstance::get();

        $query = $this->buildRecipientQuery($instance->id, $request);
        $count = $query->count();

        $sampleRecipients = $query->limit(10)->get(['name', 'email', 'phone']);

        return response()->json([
            'count' => $count,
            'sample' => $sampleRecipients,
        ]);
    }

    public function send(Request $request)
    {
        $instance = CurrentInstance::get();

        $validated = $request->validate([
            'type' => 'required|in:email,sms',
            'subject' => 'required_if:type,email|nullable|string|max:255',
            'message' => 'required|string',
            'template_id' => 'nullable|exists:eshop_email_templates,id',
            'customer_group_id' => 'nullable|exists:eshop_customer_groups,id',
            'store_id' => 'nullable|exists:eshop_stores,id',
            'channel_id' => 'nullable|exists:eshop_distribution_channels,id',
            'status' => 'nullable|in:active,inactive',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $query = $this->buildRecipientQuery($instance->id, $request);
        $recipients = $query->get();

        if ($recipients->isEmpty()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Aucun destinataire ne correspond aux filtres selectionnes.');
        }

        $filters = $request->only(['customer_group_id', 'store_id', 'channel_id', 'status', 'date_from', 'date_to']);

        $log = BulkMessageLog::create([
            'instance_id' => $instance->id,
            'type' => $validated['type'],
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'],
            'template_id' => $validated['template_id'] ?? null,
            'filters' => $filters,
            'total_recipients' => $recipients->count(),
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        if ($validated['type'] === 'email') {
            $templateSlug = null;
            if (!empty($validated['template_id'])) {
                $template = EmailTemplate::find($validated['template_id']);
                $templateSlug = $template?->slug ?? null;
            }

            // Dispatch in chunks to avoid memory issues
            $recipients->chunk(200)->each(function ($chunk) use ($log, $templateSlug) {
                SendBulkEmail::dispatch($log, $chunk, $templateSlug);
            });
        } else {
            $recipients->chunk(200)->each(function ($chunk) use ($log) {
                SendBulkSms::dispatch($log, $chunk);
            });
        }

        return redirect()
            ->route('eshop360.bulk-messages.show', [$instance->slug, $log])
            ->with('success', "Campagne lancee : {$recipients->count()} destinataire(s).");
    }

    public function history()
    {
        $logs = BulkMessageLog::with('creator')
            ->latest()
            ->paginate(20);

        return view('eshop360::communication.bulk.history', compact('logs'));
    }

    public function show(BulkMessageLog $log)
    {
        $log->load('creator', 'template');

        return view('eshop360::communication.bulk.show', compact('log'));
    }

    /**
     * Build the customer query based on recipient filters.
     */
    private function buildRecipientQuery(int $instanceId, Request $request)
    {
        $query = Customer::where('instance_id', $instanceId);

        if ($request->filled('customer_group_id')) {
            $query->where('group_id', $request->input('customer_group_id'));
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->input('store_id'));
        }

        if ($request->filled('channel_id')) {
            $query->where('channel_id', $request->input('channel_id'));
        }

        if ($request->filled('status')) {
            $isActive = $request->input('status') === 'active';
            $query->where('is_active', $isActive);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to') . ' 23:59:59');
        }

        return $query;
    }
}
