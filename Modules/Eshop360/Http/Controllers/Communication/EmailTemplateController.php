<?php

namespace Modules\Eshop360\Http\Controllers\Communication;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Database\Seeders\EmailTemplateSeeder;
use Modules\Eshop360\Domain\Communication\Models\EmailTemplate;

class EmailTemplateController extends Controller
{
    /**
     * List all email templates for the current instance.
     */
    public function index()
    {
        $instance = CurrentInstance::get();
        $templates = EmailTemplate::where('instance_id', $instance->id)
            ->orderBy('name')
            ->get();

        return view('eshop360::communication.email-templates.index', compact('instance', 'templates'));
    }

    /**
     * Show the edit form with WYSIWYG editor.
     */
    public function edit(EmailTemplate $template)
    {
        $instance = CurrentInstance::get();

        return view('eshop360::communication.email-templates.edit', compact('instance', 'template'));
    }

    /**
     * Save template changes.
     */
    public function update(Request $request, EmailTemplate $template)
    {
        $instance = CurrentInstance::get();

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $template->update([
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('eshop360.email-templates.edit', [$instance->slug, $template])
            ->with('success', 'Template mis a jour avec succes.');
    }

    /**
     * Preview a template rendered with sample data.
     */
    public function preview(EmailTemplate $template)
    {
        $sampleData = $this->getSampleData($template->name);
        $renderedSubject = $template->renderSubject($sampleData);
        $renderedBody = $template->render($sampleData);

        return response()->json([
            'subject' => $renderedSubject,
            'body' => $renderedBody,
        ]);
    }

    /**
     * Reset a template to its default content from the seeder.
     */
    public function resetToDefault(EmailTemplate $template)
    {
        $instance = CurrentInstance::get();
        $defaults = collect(EmailTemplateSeeder::defaults());
        $default = $defaults->firstWhere('name', $template->name);

        if (! $default) {
            return back()->with('error', 'Aucun template par defaut trouve pour "'.$template->name.'".');
        }

        $template->update([
            'subject' => $default['subject'],
            'body' => $default['body'],
            'variables' => $default['variables'],
            'type' => $default['type'],
            'is_active' => $default['is_active'],
        ]);

        return redirect()
            ->route('eshop360.email-templates.edit', [$instance->slug, $template])
            ->with('success', 'Template reinitialise aux valeurs par defaut.');
    }

    /**
     * Generate sample data for template preview.
     */
    private function getSampleData(string $templateName): array
    {
        return match ($templateName) {
            'invoice' => [
                'client_name' => 'Jean Dupont',
                'invoice_number' => 'FAC-2026-00042',
                'total' => '125 000 XAF',
                'due_date' => '30/04/2026',
                'company_name' => 'Ma Boutique',
            ],
            'password_reset' => [
                'user_name' => 'Marie Kamga',
                'reset_link' => 'https://example.com/reset-password/abc123token',
                'expiry_minutes' => '60',
            ],
            'product_list' => [
                'client_name' => 'Paul Mbarga',
                'products_table' => '<table style="width:100%;border-collapse:collapse;"><tr style="background:#f8f9fa;"><th style="padding:8px;border:1px solid #dee2e6;text-align:left;">Produit</th><th style="padding:8px;border:1px solid #dee2e6;text-align:right;">Prix</th></tr><tr><td style="padding:8px;border:1px solid #dee2e6;">Paracetamol 500mg</td><td style="padding:8px;border:1px solid #dee2e6;text-align:right;">1 500 XAF</td></tr><tr><td style="padding:8px;border:1px solid #dee2e6;">Amoxicilline 250mg</td><td style="padding:8px;border:1px solid #dee2e6;text-align:right;">3 200 XAF</td></tr></table>',
                'company_name' => 'Ma Boutique',
            ],
            'report' => [
                'recipient_name' => 'Administrateur',
                'report_name' => 'Ventes mensuelles',
                'period' => 'Mars 2026',
                'company_name' => 'Ma Boutique',
            ],
            'birthday' => [
                'client_name' => 'Sophie Nana',
                'company_name' => 'Ma Boutique',
                'discount_code' => 'ANNIV-2026',
            ],
            default => [
                'company_name' => 'Ma Boutique',
            ],
        };
    }
}
