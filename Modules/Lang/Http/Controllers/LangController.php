<?php

namespace Modules\Lang\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Modules\Lang\Services\LocaleManager;

final class LangController extends Controller
{
    public function switch(string $locale, LocaleManager $manager): RedirectResponse
    {
        $manager->switch($locale);

        return redirect()->back()->with('status', $manager->label($locale));
    }
}
