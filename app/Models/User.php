<?php

declare(strict_types=1);

namespace CommunityWithLegends\Models;

use Carbon\Carbon;
use CommunityWithLegends\Enums\Permission;
use CommunityWithLegends\Helpers\IdenticonHelper;
use CommunityWithLegends\Notifications\PasswordResetCodeNotification;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;
use Mchev\Banhammer\Traits\Bannable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $avatar
 * @property Carbon $email_verified_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?string $last_login_ip
 * @property Collection<Post> $posts
 * @property Collection<Comment> $comments
 * @property Collection<Reaction> $reactions
 * @property Collection<HardwareItem> $hardware
 * @property Collection<Report> $reports
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use Bannable;

    protected $fillable = [
        "name",
        "email",
        "password",
    ];
    protected $hidden = [
        "password",
        "remember_token",
    ];

    public function permissionsNames(): array
    {
        if (auth()->user() === null) {
            return [];
        }

        if (auth()->id() === $this->id || auth()->user()->hasPermissionTo(Permission::ViewUsers)) {
            return $this->getPermissionNames()->toArray();
        }

        return [];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new PasswordResetCodeNotification($this->email));
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, "reportable");
    }

    protected function avatar(): Attribute
    {
        return Attribute::get(fn(): string => IdenticonHelper::url($this->id));
    }

    public function hardware(): HasMany
    {
        return $this->hasMany(HardwareItem::class);
    }

    protected function casts(): array
    {
        return [
            "email_verified_at" => "datetime",
            "password" => "hashed",
        ];
    }
}
