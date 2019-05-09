<?php

namespace Classes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

/**
 * Class User
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @method static create(array $array)
 */
class User extends Model {

    /**
     * @var array
     */
    protected $fillable = ['first_name', 'last_name', 'username', 'email', 'password'];

    /**
     * @var array
     */
    protected $hidden = ['password'];

    /**
     * @param $value
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }
}
