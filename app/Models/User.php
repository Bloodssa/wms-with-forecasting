<?php

namespace App\Models;

use App\Enum\UserRole;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'name',
        'email',
        'password',
        'google_id',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
        'email_verified_at',
        'created_at',
        'updated_at'
    ];

    // cast the enum
    protected $casts = [
        'role' => UserRole::class
    ];

    /**
     * for validation
     * @param array $roles of the users
     */
    public function hasAnyRole(array $allowedRoles): bool
    {
        return in_array($this->role->value, $allowedRoles, true);
    }

    /**
     * Relationship of the warranties
     */
    public function warranties(): HasMany
    {
        return $this->hasMany(Warranty::class);
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(WarrantyInquiries::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
