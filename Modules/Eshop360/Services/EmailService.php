<?php

namespace Modules\Eshop360\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Communication\Models\EmailTemplate;

/**
 * Email sending service for Eshop360.
 * Uses Laravel's built-in Mail facade with configurable template support.
 */
final class EmailService
{
    /**
     * Send an email using a named template.
     *
     * @param  string  $templateName  Template name (e.g. 'invoice', 'birthday')
     * @param  string  $to  Recipient email address
     * @param  array  $data  Variables to replace in the template
     * @param  array  $attachments  Optional file paths to attach
     */
    public function sendTemplate(string $templateName, string $to, array $data, array $attachments = []): bool
    {
        $template = EmailTemplate::getTemplate($templateName);

        if (! $template) {
            Log::warning("Email template not found or inactive: {$templateName}");

            return false;
        }

        if (! $template->is_active) {
            Log::info("Email template '{$templateName}' is disabled, skipping send.");

            return false;
        }

        $subject = $template->renderSubject($data);
        $body = $template->render($data);

        return $this->send($to, $subject, $body, null, $attachments);
    }

    /**
     * Send a raw email.
     *
     * @param  string  $to  Recipient email address
     * @param  string  $subject  Email subject
     * @param  string  $body  HTML body
     * @param  string|null  $from  Optional sender address
     * @param  array  $attachments  Optional file paths to attach
     */
    public function send(string $to, string $subject, string $body, ?string $from = null, array $attachments = []): bool
    {
        try {
            Mail::html($body, function ($message) use ($to, $subject, $from, $attachments) {
                $message->to($to)->subject($subject);
                if ($from) {
                    $message->from($from);
                }
                foreach ($attachments as $path) {
                    $message->attach($path);
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
     * Send invoice email to customer using the 'invoice' template.
     */
    public function sendInvoice($invoice, ?string $to = null): bool
    {
        $customer = $invoice->customer;
        $email = $to ?? ($customer?->email ?? null);
        if (! $email) {
            return false;
        }

        $instance = CurrentInstance::get();

        return $this->sendTemplate('invoice', $email, [
            'client_name' => $customer->name ?? 'Client',
            'invoice_number' => $invoice->invoice_number ?? $invoice->reference ?? '',
            'total' => number_format($invoice->total ?? 0, 0, ',', ' ').' XAF',
            'due_date' => $invoice->due_date?->format('d/m/Y') ?? 'N/A',
            'company_name' => $instance?->name ?? 'B360',
        ]);
    }

    /**
     * Send birthday greeting to a customer using the 'birthday' template.
     */
    public function sendBirthdayGreeting($customer): bool
    {
        if (! $customer->email) {
            return false;
        }

        $instance = CurrentInstance::get();

        return $this->sendTemplate('birthday', $customer->email, [
            'client_name' => $customer->name ?? 'Client',
            'company_name' => $instance?->name ?? 'B360',
            'discount_code' => 'ANNIV-'.now()->year,
        ]);
    }

    /**
     * Send order confirmation email.
     */
    public function sendOrderConfirmation($order): bool
    {
        $customer = $order->customer;
        if (! $customer || ! $customer->email) {
            return false;
        }

        $instance = CurrentInstance::get();
        $subject = "Confirmation de commande {$order->reference} - ".($instance?->name ?? 'B360');

        $body = '<h2>Commande confirmee</h2>';
        $body .= "<p>Bonjour {$customer->name},</p>";
        $body .= "<p>Votre commande <strong>{$order->reference}</strong> a bien ete enregistree.</p>";
        $body .= '<ul>';
        $body .= '<li><strong>Total :</strong> '.number_format($order->total, 2).'</li>';
        $body .= '<li><strong>Date :</strong> '.($order->created_at?->format('d/m/Y H:i') ?? '').'</li>';
        $body .= '</ul>';
        $body .= '<p>Merci pour votre confiance !<br>'.($instance?->name ?? 'B360').'</p>';

        return $this->send($customer->email, $subject, $body);
    }

    /**
     * Send password reset email using the 'password_reset' template.
     */
    public function sendPasswordReset(string $to, string $userName, string $resetLink, int $expiryMinutes = 60): bool
    {
        return $this->sendTemplate('password_reset', $to, [
            'user_name' => $userName,
            'reset_link' => $resetLink,
            'expiry_minutes' => (string) $expiryMinutes,
        ]);
    }

    /**
     * Send product list / catalogue email using the 'product_list' template.
     */
    public function sendProductList(string $to, string $clientName, string $productsTableHtml): bool
    {
        $instance = CurrentInstance::get();

        return $this->sendTemplate('product_list', $to, [
            'client_name' => $clientName,
            'products_table' => $productsTableHtml,
            'company_name' => $instance?->name ?? 'B360',
        ]);
    }

    /**
     * Send report email using the 'report' template.
     */
    public function sendReport(string $to, string $recipientName, string $reportName, string $period, array $attachments = []): bool
    {
        $instance = CurrentInstance::get();

        return $this->sendTemplate('report', $to, [
            'recipient_name' => $recipientName,
            'report_name' => $reportName,
            'period' => $period,
            'company_name' => $instance?->name ?? 'B360',
        ], $attachments);
    }
}
