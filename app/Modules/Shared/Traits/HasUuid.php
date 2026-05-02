<?php

declare(strict_types=1);

namespace App\Modules\Shared\Traits;

use App\Modules\Shared\ValueObjects\Uuid;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Uuid::generate();
            }
        });
    }

    public function initializeHasUuid(): void
    {
        if (!in_array('uuid', $this->fillable, true)) {
            $this->fillable[] = 'uuid';
        }
    }
}
