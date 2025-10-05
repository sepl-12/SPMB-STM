<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email_address',
        'password_hash',
        'role_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password_hash' => 'hashed',
        ];
    }

    /**
     * Retrieve the authentication password.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    /**
     * Disable remember token updates because the column does not exist.
     */
    public function setRememberToken($value): void
    {
        // Intentionally left empty.
    }

    /**
     * There is no remember token column for this user model.
     */
    public function getRememberToken(): ?string
    {
        return null;
    }
}
