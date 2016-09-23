<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class rol extends Model
{
	protected $table = 'roles'; 
	protected $primaryKey = 'rol_codigo';
	protected $fillable = array('rol_codigo', 'rol_nombre', 'rol_admin', 'rol_orden', 'rol_activo');
	
	
	
	//
}
