<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Role
 *
 * @package App
 */
class GroupUser extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * Group users
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function group()
    {
        return $this->hasMany('App\Group');
    }


}
