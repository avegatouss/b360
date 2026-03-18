<?php

namespace Modules\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentationPage extends Model
{
    protected $table = 'documentation_pages';

    protected $fillable = [
        'instance_id',
        'slug',
        'title',
        'content',
        'category',
        'role_visibility',
        'sort_order',
        'is_published',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'role_visibility' => 'array',
        'sort_order' => 'integer',
        'is_published' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope: published pages only.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope: global docs (instance_id IS NULL) or docs for a given instance.
     */
    public function scopeForInstance($query, ?int $instanceId = null)
    {
        return $query->where(function ($q) use ($instanceId) {
            $q->whereNull('instance_id');
            if ($instanceId) {
                $q->orWhere('instance_id', $instanceId);
            }
        });
    }

    /**
     * Scope: visible to a given role.
     */
    public function scopeVisibleToRole($query, ?string $role)
    {
        return $query->where(function ($q) use ($role) {
            $q->whereNull('role_visibility');
            if ($role) {
                $q->orWhereJsonContains('role_visibility', $role);
            }
        });
    }

    /**
     * Categories list with labels.
     */
    public static function categories(): array
    {
        return [
            'getting-started' => 'Demarrage',
            'pos' => 'Point de vente',
            'products' => 'Produits',
            'sales' => 'Ventes & Facturation',
            'stock' => 'Stocks',
            'finance' => 'Finance',
            'hr' => 'Ressources humaines',
            'channels' => 'Canaux de distribution',
            'admin' => 'Administration',
            'api' => 'API',
            'faq' => 'FAQ',
        ];
    }
}
