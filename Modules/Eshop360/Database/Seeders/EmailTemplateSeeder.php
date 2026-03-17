<?php

namespace Modules\Eshop360\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Eshop360\Models\EmailTemplate;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Seed the 5 default email templates for a given instance.
     */
    public function run(int $instanceId = 1): void
    {
        foreach (static::defaults() as $tpl) {
            EmailTemplate::updateOrCreate(
                ['instance_id' => $instanceId, 'name' => $tpl['name']],
                $tpl
            );
        }
    }

    /**
     * Return the array of default template definitions.
     * Used by the seeder and the "reset to default" feature.
     */
    public static function defaults(): array
    {
        return [
            // 1. Invoice
            [
                'name' => 'invoice',
                'subject' => 'Facture {{invoice_number}}',
                'type' => 'transactional',
                'variables' => ['client_name', 'invoice_number', 'total', 'due_date', 'company_name'],
                'is_active' => true,
                'body' => <<<'HTML'
<div style="font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:0 auto;padding:20px;color:#333;">
    <div style="background:#2c3e50;padding:24px 30px;border-radius:8px 8px 0 0;text-align:center;">
        <h1 style="color:#fff;margin:0;font-size:22px;">{{company_name}}</h1>
    </div>
    <div style="background:#fff;border:1px solid #e0e0e0;border-top:none;padding:30px;border-radius:0 0 8px 8px;">
        <p style="font-size:16px;">Bonjour <strong>{{client_name}}</strong>,</p>
        <p>Veuillez trouver ci-dessous les informations relatives a votre facture :</p>
        <table style="width:100%;border-collapse:collapse;margin:20px 0;">
            <tr style="background:#f8f9fa;">
                <td style="padding:10px 14px;border:1px solid #dee2e6;font-weight:600;">Reference</td>
                <td style="padding:10px 14px;border:1px solid #dee2e6;">{{invoice_number}}</td>
            </tr>
            <tr>
                <td style="padding:10px 14px;border:1px solid #dee2e6;font-weight:600;">Montant total</td>
                <td style="padding:10px 14px;border:1px solid #dee2e6;">{{total}}</td>
            </tr>
            <tr style="background:#f8f9fa;">
                <td style="padding:10px 14px;border:1px solid #dee2e6;font-weight:600;">Date d'echeance</td>
                <td style="padding:10px 14px;border:1px solid #dee2e6;">{{due_date}}</td>
            </tr>
        </table>
        <p>Si vous avez des questions concernant cette facture, n'hesitez pas a nous contacter.</p>
        <p style="margin-top:30px;">Cordialement,<br><strong>{{company_name}}</strong></p>
    </div>
    <div style="text-align:center;padding:16px;color:#999;font-size:12px;">
        &copy; {{company_name}} — Tous droits reserves.
    </div>
</div>
HTML,
            ],

            // 2. Password reset
            [
                'name' => 'password_reset',
                'subject' => 'Reinitialisation de mot de passe',
                'type' => 'system',
                'variables' => ['user_name', 'reset_link', 'expiry_minutes'],
                'is_active' => true,
                'body' => <<<'HTML'
<div style="font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:0 auto;padding:20px;color:#333;">
    <div style="background:#3498db;padding:24px 30px;border-radius:8px 8px 0 0;text-align:center;">
        <h1 style="color:#fff;margin:0;font-size:22px;">Reinitialisation du mot de passe</h1>
    </div>
    <div style="background:#fff;border:1px solid #e0e0e0;border-top:none;padding:30px;border-radius:0 0 8px 8px;">
        <p style="font-size:16px;">Bonjour <strong>{{user_name}}</strong>,</p>
        <p>Vous avez demande la reinitialisation de votre mot de passe. Cliquez sur le bouton ci-dessous pour definir un nouveau mot de passe :</p>
        <div style="text-align:center;margin:30px 0;">
            <a href="{{reset_link}}" style="display:inline-block;background:#3498db;color:#fff;padding:14px 32px;text-decoration:none;border-radius:6px;font-weight:600;font-size:15px;">Reinitialiser mon mot de passe</a>
        </div>
        <p style="color:#666;font-size:14px;">Ce lien expirera dans <strong>{{expiry_minutes}} minutes</strong>. Si vous n'avez pas fait cette demande, ignorez cet e-mail.</p>
        <hr style="border:none;border-top:1px solid #eee;margin:24px 0;">
        <p style="color:#999;font-size:12px;">Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :<br>{{reset_link}}</p>
    </div>
</div>
HTML,
            ],

            // 3. Product list / Catalogue
            [
                'name' => 'product_list',
                'subject' => 'Catalogue produits - {{company_name}}',
                'type' => 'marketing',
                'variables' => ['client_name', 'products_table', 'company_name'],
                'is_active' => true,
                'body' => <<<'HTML'
<div style="font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:0 auto;padding:20px;color:#333;">
    <div style="background:#27ae60;padding:24px 30px;border-radius:8px 8px 0 0;text-align:center;">
        <h1 style="color:#fff;margin:0;font-size:22px;">Catalogue Produits</h1>
        <p style="color:#d5f5e3;margin:6px 0 0;font-size:14px;">{{company_name}}</p>
    </div>
    <div style="background:#fff;border:1px solid #e0e0e0;border-top:none;padding:30px;border-radius:0 0 8px 8px;">
        <p style="font-size:16px;">Bonjour <strong>{{client_name}}</strong>,</p>
        <p>Voici notre selection de produits disponibles :</p>
        <div style="margin:20px 0;">
            {{products_table}}
        </div>
        <p>N'hesitez pas a nous contacter pour passer commande ou obtenir des informations supplementaires.</p>
        <div style="text-align:center;margin:24px 0;">
            <a href="#" style="display:inline-block;background:#27ae60;color:#fff;padding:12px 28px;text-decoration:none;border-radius:6px;font-weight:600;">Voir le catalogue complet</a>
        </div>
        <p style="margin-top:20px;">Cordialement,<br><strong>{{company_name}}</strong></p>
    </div>
    <div style="text-align:center;padding:16px;color:#999;font-size:12px;">
        &copy; {{company_name}} — Tous droits reserves.
    </div>
</div>
HTML,
            ],

            // 4. Report
            [
                'name' => 'report',
                'subject' => 'Rapport {{report_name}} - {{period}}',
                'type' => 'system',
                'variables' => ['recipient_name', 'report_name', 'period', 'company_name'],
                'is_active' => true,
                'body' => <<<'HTML'
<div style="font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:0 auto;padding:20px;color:#333;">
    <div style="background:#8e44ad;padding:24px 30px;border-radius:8px 8px 0 0;text-align:center;">
        <h1 style="color:#fff;margin:0;font-size:22px;">Rapport : {{report_name}}</h1>
        <p style="color:#d7bde2;margin:6px 0 0;font-size:14px;">Periode : {{period}}</p>
    </div>
    <div style="background:#fff;border:1px solid #e0e0e0;border-top:none;padding:30px;border-radius:0 0 8px 8px;">
        <p style="font-size:16px;">Bonjour <strong>{{recipient_name}}</strong>,</p>
        <p>Veuillez trouver ci-joint le rapport <strong>{{report_name}}</strong> couvrant la periode <strong>{{period}}</strong>.</p>
        <div style="background:#f8f9fa;border-left:4px solid #8e44ad;padding:16px 20px;margin:20px 0;border-radius:0 6px 6px 0;">
            <p style="margin:0;font-size:14px;color:#555;">Ce rapport a ete genere automatiquement. Les donnees sont a jour au moment de l'envoi. Pour toute question, contactez l'equipe de gestion.</p>
        </div>
        <p style="margin-top:20px;">Cordialement,<br><strong>{{company_name}}</strong></p>
    </div>
    <div style="text-align:center;padding:16px;color:#999;font-size:12px;">
        &copy; {{company_name}} — Tous droits reserves.
    </div>
</div>
HTML,
            ],

            // 5. Birthday
            [
                'name' => 'birthday',
                'subject' => "Joyeux anniversaire {{client_name}} ! \xF0\x9F\x8E\x82",
                'type' => 'marketing',
                'variables' => ['client_name', 'company_name', 'discount_code'],
                'is_active' => true,
                'body' => <<<'HTML'
<div style="font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:0 auto;padding:20px;color:#333;">
    <div style="background:linear-gradient(135deg,#e74c3c,#f39c12);padding:30px;border-radius:8px 8px 0 0;text-align:center;">
        <div style="font-size:48px;margin-bottom:10px;">&#127874;</div>
        <h1 style="color:#fff;margin:0;font-size:24px;">Joyeux Anniversaire !</h1>
    </div>
    <div style="background:#fff;border:1px solid #e0e0e0;border-top:none;padding:30px;border-radius:0 0 8px 8px;">
        <p style="font-size:18px;text-align:center;">Cher(e) <strong>{{client_name}}</strong>,</p>
        <p style="text-align:center;font-size:15px;">Toute l'equipe de <strong>{{company_name}}</strong> vous souhaite un tres heureux anniversaire !</p>
        <div style="background:#fff3cd;border:2px dashed #f39c12;border-radius:8px;padding:20px;text-align:center;margin:24px 0;">
            <p style="margin:0 0 8px;font-size:14px;color:#856404;">Votre code promo exclusif :</p>
            <p style="margin:0;font-size:28px;font-weight:700;color:#e74c3c;letter-spacing:3px;">{{discount_code}}</p>
            <p style="margin:8px 0 0;font-size:13px;color:#856404;">Valable pendant 7 jours</p>
        </div>
        <p style="text-align:center;">Profitez de cette offre speciale pour vous faire plaisir !</p>
        <p style="text-align:center;margin-top:24px;">Chaleureusement,<br><strong>{{company_name}}</strong></p>
    </div>
    <div style="text-align:center;padding:16px;color:#999;font-size:12px;">
        &copy; {{company_name}} — Tous droits reserves.
    </div>
</div>
HTML,
            ],
        ];
    }
}
