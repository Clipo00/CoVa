<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;

class BlueprintFavorite extends Model
{
    protected $fillable = [
        'user_id',
        'blueprint_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function blueprint()
    {
        return $this->belongsTo(Blueprint::class);
    }
}
