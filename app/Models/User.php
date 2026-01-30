<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Role;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    public const ROLE_REPORTER = 'reporter';
    public const ROLE_EDITOR = 'editor';
    public const ROLE_ADMIN = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function roleRelation()
    {
        return $this->belongsTo(Role::class, 'role', 'slug');
    }

    public function permissions(): array
    {
        $role = $this->roleRelation()->first();
        $permissions = $role?->permissions ?? $this->defaultPermissionsForRole($this->role);

        if (in_array('*', $permissions, true)) {
            return ['*'];
        }

        return $permissions;
    }

    private function defaultPermissionsForRole(?string $role): array
    {
        return match ($role) {
            self::ROLE_ADMIN => ['*'],
            self::ROLE_EDITOR => [
                'articles.view_all',
                'articles.edit_all',
                'articles.approve',
            ],
            self::ROLE_REPORTER => [
                'articles.create',
                'articles.edit',
                'articles.review',
            ],
            default => [],
        };
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions();

        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isEditor(): bool
    {
        return $this->role === self::ROLE_EDITOR;
    }

    public function isReporter(): bool
    {
        return $this->role === self::ROLE_REPORTER;
    }
}
