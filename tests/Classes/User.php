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
    protected $fillable = ['name', 'email', 'password'];

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