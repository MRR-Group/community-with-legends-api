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
use Illuminate\Database\Eloquent\Relations\HasOne;
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
 * @property bool $hasPassword
 * @property bool $has_twitch_login
 * @property Carbon $email_verified_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?string $last_login_ip
 * @property UserTfaCode $userTfaCode
 * @property Collection<Comment> $comments
 * @property Collection<Reaction> $reactions
 * @property Collection<HardwareItem> $hardware
 * @property Collection<GameProposal> $gameProposals
 * @property Collection<UserGame> $userGames
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

    public function userGames(): HasMany
    {
        return $this->hasMany(UserGame::class);
    }

    public function gameProposals(): HasMany
    {
        return $this->hasMany(GameProposal::class);
    }

    public function userTfaCode(): HasOne
    {
        return $this->hasOne(UserTfaCode::class);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new PasswordResetCodeNotification($this->email));
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, "reportable");
    }

    public function hardware(): HasMany
    {
        return $this->hasMany(HardwareItem::class);
    }

    public function hasPassword(): Attribute
    {
        return Attribute::get(fn(): bool => !empty($this->password));
    }

    protected function avatar(): Attribute
    {
        return Attribute::get(fn(): string => IdenticonHelper::url($this->id));
    }

    protected function casts(): array
    {
        return [
            "email_verified_at" => "datetime",
            "password" => "hashed",
        ];
    }
}
