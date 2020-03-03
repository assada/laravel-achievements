<?php
declare(strict_types=1);

namespace Gstt\Tests\Model;

use Gstt\Achievements\Achiever;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Class User
 *
 * @package Gstt\Tests\Model
 */
class User extends Authenticatable
{
    use Achiever;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
}
