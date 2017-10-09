<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function add(string $name, string $email, string $userId): User
    {
        $this->name = $name;
        $this->email = $email;
        $this->user_id = $userId;
        $this->save();

        return $this;
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }
}
