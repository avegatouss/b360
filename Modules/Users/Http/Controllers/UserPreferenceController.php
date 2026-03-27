<?php

namespace Modules\Users\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Users\Services\UserPreferenceService;

class UserPreferenceController extends Controller
{
    public function __construct(private readonly UserPreferenceService $prefService)
    {
    }

    public function edit()
    {
        $prefs = $this->prefService->all(auth()->id());

        return view('users::preferences', compact('prefs'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'language'            => 'required|string|in:fr,en,es,pt',
            'theme'               => 'required|string|in:light,dark,auto',
            'timezone'            => 'required|string|max:100',
            'date_format'         => 'required|string|in:d/m/Y,m/d/Y,Y-m-d',
            'notifications_email' => 'nullable|boolean',
            'notifications_sms'   => 'nullable|boolean',
            'items_per_page'      => 'required|integer|in:10,20,30,50,100',
        ]);

        $validated['notifications_email'] = $request->boolean('notifications_email') ? '1' : '0';
        $validated['notifications_sms'] = $request->boolean('notifications_sms') ? '1' : '0';
        $validated['items_per_page'] = (string) $validated['items_per_page'];

        $this->prefService->setMany(auth()->id(), $validated);

        return redirect()->back()->with('status', 'Preferences mises a jour.');
    }
}
