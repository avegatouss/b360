<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Http\Controllers\Client;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Menuiserie360\Domain\Client\Contracts\ClientRepositoryContract;
use Modules\Menuiserie360\Domain\Client\Models\ClientMenuiserie;

/**
 * P2 / M-UI-4 — Controller Client menuiserie.
 *
 * Consomme le referentiel client natif Menuiserie360 (ADR-023).
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
     * Endpoint JSON pour l'autocomplete client natif.
     */
    public function search(Request $request): JsonResponse
    {
        $instance = CurrentInstance::get();
        abort_if($instance === null, 503, 'No instance context.');

        $query = (string) $request->query('q', '');
        $limit = (int) $request->query('limit', 20);

        if (mb_strlen(trim($query)) < 2) {
            return response()->json(['results' => []]);
        }

        $clients = ClientMenuiserie::query()
            ->where('instance_id', $instance->id)
            ->where('is_active', true)
            ->where(function ($builder) use ($query): void {
                $like = '%'.$query.'%';
                $builder
                    ->where('code', 'like', $like)
                    ->orWhere('nom', 'like', $like)
                    ->orWhere('prenom', 'like', $like)
                    ->orWhere('raison_sociale', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('telephone_principal', 'like', $like);
            })
            ->orderBy('code')
            ->limit(max(1, min($limit, 50)))
            ->get();

        $results = $clients->map(static fn (ClientMenuiserie $client): array => [
            'id' => (int) $client->getKey(),
            'code' => (string) $client->getAttribute('code'),
            'name' => $client->name,
            'email' => $client->getAttribute('email'),
            'phone' => $client->getAttribute('telephone_principal'),
            'city' => $client->getAttribute('ville'),
            'label' => $client->getAttribute('code').' - '.$client->name.($client->getAttribute('ville') !== null ? ' ('.$client->getAttribute('ville').')' : ''),
        ])->all();

        return response()->json(['results' => $results]);
    }
}
