<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Http\Controllers\Client;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Menuiserie360\Domain\Client\Contracts\ClientRepositoryContract;
use Modules\Menuiserie360\Domain\Client\Models\ClientMenuiserie;

/**
 * P2 — Controller Client menuiserie (consomme ClientRepositoryContract
 * qui passe par CustomerReader Eshop360 + extension menuiserie).
 */
final class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $instance = CurrentInstance::get();
        abort_if($instance === null, 503, 'No instance context.');

        // Liste des clients ayant un historique menuiserie (chantier > 0).
        // Pour l'index complet, l'humain peut élargir en P2-B-2 (search étendue).
        $extensions = ClientMenuiserie::query()
            ->where('instance_id', $instance->id)
            ->orderByDesc('total_chantiers_count')
            ->paginate(30)
            ->withQueryString();

        return view('menuiserie360::clients.index', compact('extensions'));
    }

    public function show(Request $request, string $slug, int|string $customerId): View
    {
        $instance = CurrentInstance::get();
        abort_if($instance === null, 503, 'No instance context.');

        $client = app(ClientRepositoryContract::class)
            ->find($instance->id, (int) $customerId);

        abort_if($client === null, 404, 'Client menuiserie introuvable.');

        return view('menuiserie360::clients.show', compact('client'));
    }
}
