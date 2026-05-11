<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Http\Controllers\Client;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Contracts\Customer\CustomerDto;
use Modules\Eshop360\Contracts\Customer\CustomerReader;
use Modules\Menuiserie360\Domain\Client\Contracts\ClientRepositoryContract;
use Modules\Menuiserie360\Domain\Client\Models\ClientMenuiserie;

/**
 * P2 / M-UI-4 — Controller Client menuiserie.
 *
 * Consomme `ClientRepositoryContract` (qui passe par `CustomerReader`
 * Eshop360 + extension menuiserie, cf. ADR-021). Le endpoint `search`
 * permet l'autocomplete client global dans devis/create et autres
 * formulaires (lot M-UI-4).
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

    /**
     * Endpoint JSON pour l'autocomplete client (M-UI-4).
     * Retourne les customers Eshop360 actifs qui matchent la query
     * sur code / name / email / phone.
     *
     * Réponse 503 si Eshop360 est désactivé (R-403 — binding CustomerReader
     * absent, le module ne peut pas servir cette donnée).
     */
    public function search(Request $request): JsonResponse
    {
        $instance = CurrentInstance::get();
        abort_if($instance === null, 503, 'No instance context.');

        if (! app()->bound(CustomerReader::class)) {
            return response()->json([
                'error' => 'Eshop360 désactivé — recherche client indisponible (R-403).',
                'results' => [],
            ], 503);
        }

        $query = (string) $request->query('q', '');
        $limit = (int) $request->query('limit', 20);

        $customers = app(CustomerReader::class)->searchCustomers($instance->id, $query, $limit);

        $results = array_map(
            static fn (CustomerDto $c) => [
                'id' => $c->id,
                'code' => $c->code,
                'name' => $c->name,
                'email' => $c->email,
                'phone' => $c->phone,
                'city' => $c->city,
                'label' => "{$c->code} — {$c->name}".($c->city !== null ? " ({$c->city})" : ''),
            ],
            is_array($customers) ? $customers : iterator_to_array($customers),
        );

        return response()->json(['results' => $results]);
    }
}
