<?php

namespace Modules\Eshop360\Database\Seeders;

use Modules\Eshop360\Models\EshopModuleSetting;

final class DemoSettingsSeeder
{
    public function run(int $instanceId): void
    {
        $settings = [
            'pos' => [
                'default_layout' => 'layout1',
                'default_warehouse_id' => null,
                'default_customer_id' => null,
                'payment_methods' => ['cash', 'card', 'cheque', 'bank_transfer'],
                'tax_inclusive' => false,
                'sound_enabled' => true,
                'print_receipt' => true,
                'products_per_page' => 24,
                'default_discount' => 0,
                'allow_manual_price' => false,
                'barcode_scanner' => true,
            ],
            'invoice' => [
                'company_name' => '[DEMO] SAPHIR Pharma',
                'company_address' => 'Boulevard de la Republique, Abidjan, Cote d\'Ivoire',
                'company_phone' => '+225 07 00 00 00',
                'company_email' => 'contact@saphir-pharma.ci',
                'tax_number' => 'CI-2024-NIF-00123',
                'default_terms' => 'Paiement a 30 jours. Penalites de retard: 1.5% par mois.',
                'default_footer' => 'Merci pour votre confiance. SAPHIR Pharma — Votre partenaire sante.',
                'default_due_days' => 30,
                'default_template' => 'default',
                'currency_symbol' => 'FCFA',
                'currency_position' => 'after',
                'show_tax_breakdown' => true,
                'show_payment_info' => true,
                'bank_name' => 'SGBCI',
                'bank_account' => '00123456789',
                'bank_iban' => 'CI93 CI00 0000 1234 5678 9012 34',
            ],
            'printer' => [
                'printer_type' => 'network',
                'printer_host' => '192.168.1.100',
                'printer_port' => 9100,
                'receipt_width' => 80,
                'receipt_header' => 'SAPHIR Pharma - Point de vente',
                'receipt_footer' => 'Merci de votre visite !',
                'print_logo' => false,
                'auto_print_receipt' => true,
                'print_kitchen_order' => false,
            ],
        ];

        foreach ($settings as $group => $data) {
            EshopModuleSetting::updateOrCreate(
                ['instance_id' => $instanceId, 'group' => $group],
                ['data' => $data]
            );
        }
    }

    public function reset(int $instanceId): void
    {
        EshopModuleSetting::where('instance_id', $instanceId)->delete();
    }
}
