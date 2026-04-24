<?php

namespace Modules\Eshop360\Domain\Communication\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Traits\BelongsToInstance;
use Modules\Core\Support\CurrentInstance;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class EmailTemplate extends Model
{
    use BelongsToChannel, BelongsToInstance, HasFactory;

    protected $morphClass = \Modules\Eshop360\Models\EmailTemplate::class;

    protected $table = 'eshop_email_templates';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'name',
        'subject',
        'body',
        'type',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'variables' => 'array',
    ];

    /**
     * Get a template by name for the current instance.
     */
    public static function getTemplate(string $name): ?self
    {
        $instance = CurrentInstance::get();
        if (! $instance) {
            return null;
        }

        return static::where('instance_id', $instance->id)
            ->where('name', $name)
            ->first();
    }

    /**
     * Render the template body by replacing {{variable}} placeholders with actual values.
     */
    public function render(array $data): string
    {
        $html = $this->body;
        foreach ($data as $key => $value) {
            $html = str_replace('{{'.$key.'}}', (string) $value, $html);
        }

        return $html;
    }

    /**
     * Render the subject by replacing {{variable}} placeholders with actual values.
     */
    public function renderSubject(array $data): string
    {
        $subject = $this->subject;
        foreach ($data as $key => $value) {
            $subject = str_replace('{{'.$key.'}}', (string) $value, $subject);
        }

        return $subject;
    }
}
