<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

final class InstanceSelectionController extends Controller
{
    public function select(Request $request)
    {
        $user = $request->user();
        if (!$user) abort(401);

        $instances = DB::connection('system')
            ->table('instances')
            ->join('instance_user', 'instances.id', '=', 'instance_user.instance_id')
            ->where('instance_user.user_id', $user->id)
            ->where('instance_user.status', 'active')
            ->where('instances.is_active', true)
            ->select(['instances.slug', 'instances.name'])
            ->orderBy('instances.name')
            ->get();

        if ($instances->isEmpty()) {
            return redirect()->route('instances.no_active');
        }

        if ($instances->count() === 1) {
            return redirect()->to('/i/' . $instances->first()->slug);
        }

        return view('authmod::instances.select', compact('instances'));
    }

    public function choose(Request $request)
    {
        $data = $request->validate([
            'slug' => ['required','string','max:120','regex:/^[a-z0-9-]+$/'],
        ]);

        // Prevent enumeration: only allow slugs that belong to the user (active membership)
        $ok = DB::connection('system')
            ->table('instances')
            ->join('instance_user', 'instances.id', '=', 'instance_user.instance_id')
            ->where('instances.slug', $data['slug'])
            ->where('instance_user.user_id', $request->user()->id)
            ->where('instance_user.status', 'active')
            ->where('instances.is_active', true)
            ->exists();

        if (!$ok) abort(403);

        return redirect()->to('/i/' . $data['slug']);
    }

    public function noActive()
    {
        return view('authmod::instances.no-active');
    }
}
