<?php

namespace Modules\Eshop360\Http\Controllers\Charges;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Eshop360\Models\CompanyCharge;
use Modules\Eshop360\Services\ChargesService;
use Modules\Core\Support\CurrentInstance;

class ChargesController extends Controller
{
    public function __construct(private ChargesService $chargesService) {}

    public function index()
    {
        $instance = CurrentInstance::get();
        $charges = CompanyCharge::where('instance_id', $instance->id)->get();
        $dashboardData = $this->chargesService->getDashboardData($instance->id);
        return view('eshop360::charges.index', compact('charges', 'dashboardData'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:rent,electricity,salary,transport,maintenance,insurance,other',
            'amount_monthly' => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
        ]);
        $validated['instance_id'] = CurrentInstance::get()->id;
        CompanyCharge::create($validated);
        return redirect()->back()->with('success', 'Charge added.');
    }

    public function update(Request $request, string $slug, CompanyCharge $charge)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:rent,electricity,salary,transport,maintenance,insurance,other',
            'amount_monthly' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);
        $charge->update($validated);
        return redirect()->back()->with('success', 'Charge updated.');
    }

    public function destroy(string $slug, CompanyCharge $charge)
    {
        $charge->delete();
        return redirect()->back()->with('success', 'Charge deleted.');
    }

    /**
     * API endpoint for real-time dashboard counter
     */
    public function realtime()
    {
        $instance = CurrentInstance::get();
        return response()->json($this->chargesService->getDashboardData($instance->id));
    }
}
