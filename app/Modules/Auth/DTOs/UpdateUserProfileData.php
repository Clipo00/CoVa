<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

use Illuminate\Http\UploadedFile;

final readonly class UpdateUserProfileData
{
    public function __construct(
        public string $name,
        public string $email,
        public ?UploadedFile $avatar = null,
        public ?string $currentPassword = null,
        public ?string $newPassword = null,
    ) {}
}
