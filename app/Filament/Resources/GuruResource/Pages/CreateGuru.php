<?php

namespace App\Filament\Resources\GuruResource\Pages;

use App\Filament\Resources\GuruResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateGuru extends CreateRecord
{
    protected static string $resource = GuruResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Handle user account creation
        if ($data['create_user_account'] ?? false) {
            $user = User::create([
                'name' => $data['user_name'],
                'email' => $data['user_email'],
                'password' => Hash::make($data['user_password']),
                'role' => 'guru',
                'is_active' => true,
            ]);

            $data['user_id'] = $user->id;
        }

        // Remove temporary form fields
        unset(
            $data['create_user_account'],
            $data['user_name'],
            $data['user_email'],
            $data['user_password'],
            $data['user_password_confirmation']
        );

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
