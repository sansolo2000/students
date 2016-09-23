<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class region extends Model
{
	protected $table = 'regiones';
	protected $primaryKey = 'reg_codigo';
	protected $fillable = ['reg_codigo', 'reg_nombre', 'reg_orden', 'reg_activo'];
	protected $guarded	= ['reg_codigo'];
	//
}
