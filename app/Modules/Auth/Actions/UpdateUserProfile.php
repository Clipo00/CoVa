<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\DTOs\UpdateUserProfileData;
use App\Modules\Auth\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

final class UpdateUserProfile
{
    /** @var string Disco de storage para avatares (configurable: 'avatars', 's3', etc.) */
    private string $disk;

    public function __construct()
    {
        $this->disk = config('filesystems.disks.avatars.driver') === 's3' ? 's3' : 'avatars';
    }

    public function execute(User $user, UpdateUserProfileData $data): User
    {
        $updateData = [
            'name' => $data->name,
            'email' => $data->email,
        ];

        if ($data->avatar) {
            $this->deleteOldAvatar($user);
            $path = $data->avatar->store('', $this->disk);
            $updateData['avatar'] = $path;
        }

        if ($data->newPassword) {
            $updateData['password'] = Hash::make($data->newPassword);
        }

        $user->update($updateData);

        return $user->fresh();
    }

    private function deleteOldAvatar(User $user): void
    {
        if ($user->avatar) {
            Storage::disk($this->disk)->delete($user->avatar);
        }
    }
}
