<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\Core\Models\DocumentationPage;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;

final class DocumentationController extends Controller
{
    /**
     * Documentation home — category sidebar + page list.
     */
    public function index(Request $request)
    {
        $instance = CurrentInstance::get();
        $user = $request->user();
        $role = $user?->roles->first()?->name;
        $instanceId = $instance?->id;

        $pages = DocumentationPage::published()
            ->forInstance($instanceId)
            ->visibleToRole($role)
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $categories = DocumentationPage::categories();
        $grouped = $pages->groupBy('category');

        $isAdmin = $user && (
            TeamContext::isSuperAdmin($user) ||
            ($role === 'instance-admin')
        );

        return view('core::documentation.index', compact(
            'instance', 'pages', 'categories', 'grouped', 'isAdmin'
        ));
    }

    /**
     * Show a single documentation page.
     */
    public function show(Request $request, string $slug, string $page)
    {
        $instance = CurrentInstance::get();
        $user = $request->user();
        $role = $user?->roles->first()?->name;
        $instanceId = $instance?->id;

        $docPage = DocumentationPage::published()
            ->forInstance($instanceId)
            ->visibleToRole($role)
            ->where('slug', $page)
            ->firstOrFail();
        $page = $docPage;

        // Get all pages for prev/next navigation
        $allPages = DocumentationPage::published()
            ->forInstance($instanceId)
            ->visibleToRole($role)
            ->where('category', $page->category)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $currentIndex = $allPages->search(fn ($p) => $p->id === $page->id);
        $prevPage = $currentIndex > 0 ? $allPages[$currentIndex - 1] : null;
        $nextPage = $currentIndex < $allPages->count() - 1 ? $allPages[$currentIndex + 1] : null;

        // Render Markdown to HTML
        $htmlContent = Str::markdown($page->content);

        $categories = DocumentationPage::categories();

        $isAdmin = $user && (
            TeamContext::isSuperAdmin($user) ||
            ($role === 'instance-admin')
        );

        return view('core::documentation.show', compact(
            'instance', 'page', 'htmlContent', 'prevPage', 'nextPage', 'categories', 'isAdmin'
        ));
    }

    /**
     * Edit form (admin only).
     */
    public function edit(Request $request, string $slug, string $page)
    {
        $this->authorizeAdmin($request);

        $instance = CurrentInstance::get();
        $docPage = DocumentationPage::where('slug', $page)->firstOrFail();
        $categories = DocumentationPage::categories();
        $roles = ['super-admin', 'instance-admin', 'manager', 'agent', 'user'];
        $page = $docPage;

        return view('core::documentation.edit', compact(
            'instance', 'page', 'categories', 'roles'
        ));
    }

    /**
     * Update a documentation page.
     */
    public function update(Request $request, string $slug, string $page)
    {
        $this->authorizeAdmin($request);

        $docPage = DocumentationPage::where('slug', $page)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string|max:50',
            'role_visibility' => 'nullable|array',
            'sort_order' => 'integer|min:0',
            'is_published' => 'boolean',
        ]);

        $validated['updated_by'] = $request->user()->id;
        $validated['is_published'] = $request->boolean('is_published', true);
        $validated['role_visibility'] = $request->input('role_visibility') ?: null;

        $docPage->update($validated);

        $instance = CurrentInstance::get();

        return redirect()
            ->route('documentation.show', [$instance?->slug ?? '', $docPage->slug])
            ->with('status', 'Page mise a jour avec succes.');
    }

    /**
     * Create form (admin only).
     */
    public function create(Request $request)
    {
        $this->authorizeAdmin($request);

        $instance = CurrentInstance::get();
        $categories = DocumentationPage::categories();
        $roles = ['super-admin', 'instance-admin', 'manager', 'agent', 'user'];

        return view('core::documentation.edit', [
            'instance' => $instance,
            'page' => null,
            'categories' => $categories,
            'roles' => $roles,
        ]);
    }

    /**
     * Store a new documentation page.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|alpha_dash',
            'content' => 'required|string',
            'category' => 'required|string|max:50',
            'role_visibility' => 'nullable|array',
            'sort_order' => 'integer|min:0',
            'is_published' => 'boolean',
        ]);

        $instance = CurrentInstance::get();

        $validated['instance_id'] = $instance?->id;
        $validated['created_by'] = $request->user()->id;
        $validated['is_published'] = $request->boolean('is_published', true);
        $validated['role_visibility'] = $request->input('role_visibility') ?: null;

        $page = DocumentationPage::create($validated);

        return redirect()
            ->route('documentation.show', [$instance?->slug ?? '', $page->slug])
            ->with('status', 'Page creee avec succes.');
    }

    /**
     * Delete a documentation page.
     */
    public function destroy(Request $request, string $slug, string $page)
    {
        $this->authorizeAdmin($request);

        $docPage = DocumentationPage::where('slug', $page)->firstOrFail();
        $docPage->delete();

        $instance = CurrentInstance::get();

        return redirect()
            ->route('documentation.index', $instance?->slug ?? '')
            ->with('status', 'Page supprimee.');
    }

    /**
     * Search documentation pages (AJAX).
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $instance = CurrentInstance::get();
        $user = $request->user();
        $role = $user?->roles->first()?->name;

        $results = DocumentationPage::published()
            ->forInstance($instance?->id)
            ->visibleToRole($role)
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('content', 'LIKE', "%{$query}%");
            })
            ->orderBy('sort_order')
            ->limit(20)
            ->get(['id', 'slug', 'title', 'category'])
            ->map(function ($page) {
                return [
                    'slug' => $page->slug,
                    'title' => $page->title,
                    'category' => $page->category,
                    'category_label' => DocumentationPage::categories()[$page->category] ?? $page->category,
                ];
            });

        return response()->json(['results' => $results]);
    }

    /**
     * Ensure the current user is admin.
     */
    private function authorizeAdmin(Request $request): void
    {
        $user = $request->user();
        $role = $user?->roles->first()?->name;

        if (!$user || !in_array($role, ['super-admin', 'instance-admin'], true)) {
            abort(403, 'Acces reserve aux administrateurs.');
        }
    }
}
