<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;



class persona extends Authenticatable
{
	protected $table = 'personas'; 
	protected $primaryKey = 'per_rut';
	protected $remember_token = 'per_remember_token';
	protected $fillable = array('per_rut', 'per_dv', 'per_nombre', 'per_nombre_segundo', 'per_apellido_paterno', 'per_apellido_materno', 'per_password', 'per_email', 'remember_token');
	
	protected $hidden = [
			'per_password', 'per_remember_token',
	];
	
}