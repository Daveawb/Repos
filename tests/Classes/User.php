<?php

namespace Classes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

/**
 * Class User
 */
class User extends Model {

    /**
     * @var array
     */
    protected $fillable = ['first_name', 'last_name', 'email', 'password', 'username'];

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