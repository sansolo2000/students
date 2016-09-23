<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class comuna extends Model
{
	protected $table = 'comunas';
	protected $primaryKey = 'com_codigo';
	protected $fillable = ['com_codigo', 'reg_codigo', 'com_nombre', 'reg_orden', 'reg_activo'];
	protected $guarded	= ['com_codigo'];
	//
}
