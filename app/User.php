<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'personas';
    protected $primaryKey = 'per_rut';
    protected $remember_token = 'per_remember_token';
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    			'per_codigo', 'per_rut', 'per_dv', 'per_nombre', 'per_apellido_paterno', 'per_apellido_materno', 'per_password', 'per_email', 'per_remember_token'
    		];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    //
    protected $hidden = [
    		'per_password', 'per_remember_token',
    ];
    

}
