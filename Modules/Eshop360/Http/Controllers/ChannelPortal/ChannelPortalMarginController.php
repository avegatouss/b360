<?php

namespace Modules\Eshop360\Http\Controllers\ChannelPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Eshop360\Domain\Channel\Models\ChannelMarginLog;

class ChannelPortalMarginController extends Controller
{
    /**
     * List margin logs for this channel with optional date range filter.
     */
    public function index(Request $request)
    {
        $channel = $request->resolved_channel;

        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());

        $query = ChannelMarginLog::where('channel_id', $channel->id)
            ->with('order.customer');

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        // Summary stats for the filtered period
        $summaryQuery = ChannelMarginLog::where('channel_id', $channel->id);
        if ($from) {
            $summaryQuery->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $summaryQuery->whereDate('created_at', '<=', $to);
        }

        $summary = $summaryQuery->selectRaw(
            'SUM(total_margin) as total_margin, SUM(channel_part) as channel_part, SUM(owner_part) as owner_part, SUM(debt_part) as debt_part, COUNT(*) as count'
        )->first();

        return view('eshop360::channel-portal.margins.index', compact(
            'channel', 'logs', 'summary', 'from', 'to'
        ));
    }
}
