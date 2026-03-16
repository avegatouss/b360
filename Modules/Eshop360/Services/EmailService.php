<?php

namespace Modules\Eshop360\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Modules\Eshop360\Models\EmailTemplate;
use Modules\Core\Support\CurrentInstance;

/**
 * Email sending service for Eshop360.
 * Uses Laravel's built-in Mail facade with template support.
 */
final class EmailService
{
    /**
     * Send an email using a template.
     */
    public function sendFromTemplate(string $templateSlug, string $to, array $variables = [], ?string $subject = null): bool
    {
        $instance = CurrentInstance::get();
        if (!$instance) return false;

        $template = EmailTemplate::where('instance_id', $instance->id)
            ->where('slug', $templateSlug)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            Log::warning("Email template not found: {$templateSlug}");
            return false;
        }

        $body = $this->replaceVariables($template->body, $variables);
        $emailSubject = $subject ?? $this->replaceVariables($template->subject, $variables);

        return $this->send($to, $emailSubject, $body);
    }

    /**
     * Send a raw email.
     */
    public function send(string $to, string $subject, string $body, ?string $from = null): bool
    {
        try {
            Mail::html($body, function ($message) use ($to, $subject, $from) {
                $message->to($to)->subject($subject);
                if ($from) {
                    $message->from($from);
                }
            });

            return true;
        } catch (\Throwable $e) {
            Log::error('Email sending failed', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send invoice email to customer.
     */
    public function sendInvoice($invoice, ?string $pdfHtml = null): bool
    {
        $customer = $invoice->customer;
        if (!$customer || !$customer->email) return false;

        $instance = CurrentInstance::get();
        $subject = "Facture {$invoice->invoice_number} - {$instance->name}";

        $body = "<h2>Facture {$invoice->invoice_number}</h2>";
        $body .= "<p>Bonjour {$customer->name},</p>";
        $body .= "<p>Veuillez trouver ci-dessous les détails de votre facture :</p>";
        $body .= "<ul>";
        $body .= "<li><strong>Référence :</strong> {$invoice->invoice_number}</li>";
        $body .= "<li><strong>Date :</strong> " . ($invoice->created_at?->format('d/m/Y') ?? '') . "</li>";
        $body .= "<li><strong>Échéance :</strong> " . ($invoice->due_date?->format('d/m/Y') ?? 'N/A') . "</li>";
        $body .= "<li><strong>Total :</strong> " . number_format($invoice->total, 2) . "</li>";
        $body .= "</ul>";
        $body .= "<p>Cordialement,<br>{$instance->name}</p>";

        return $this->send($customer->email, $subject, $body);
    }

    /**
     * Send order confirmation email.
     */
    public function sendOrderConfirmation($order): bool
    {
        $customer = $order->customer;
        if (!$customer || !$customer->email) return false;

        $instance = CurrentInstance::get();
        $subject = "Confirmation de commande {$order->reference} - {$instance->name}";

        $body = "<h2>Commande confirmée</h2>";
        $body .= "<p>Bonjour {$customer->name},</p>";
        $body .= "<p>Votre commande <strong>{$order->reference}</strong> a bien été enregistrée.</p>";
        $body .= "<ul>";
        $body .= "<li><strong>Total :</strong> " . number_format($order->total, 2) . "</li>";
        $body .= "<li><strong>Date :</strong> " . ($order->created_at?->format('d/m/Y H:i') ?? '') . "</li>";
        $body .= "</ul>";
        $body .= "<p>Merci pour votre confiance !<br>{$instance->name}</p>";

        return $this->send($customer->email, $subject, $body);
    }

    /**
     * Replace template variables like {{name}}, {{amount}}, etc.
     */
    private function replaceVariables(string $text, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $text = str_replace("{{" . $key . "}}", (string) $value, $text);
        }
        return $text;
    }
}
