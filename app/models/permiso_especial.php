<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class permiso_especial extends Model
{
	protected $table = 'permisos_especiales';
	protected $primaryKey = 'pee_codigo';
	protected $fillable = ['pee_codigo', 'mas_codigo', 'pee_nombre', 'updated_at', 'created_at'];
	protected $guarded	= ['pee_codigo'];

}


