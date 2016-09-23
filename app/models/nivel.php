<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class nivel extends Model
{
	protected $table = 'niveles';
	protected $primaryKey = 'niv_codigo';
	protected $fillable = ['niv_codigo', 'niv_nombre', 'niv_orden', 'niv_activo'];
	protected $guarded	= ['niv_codigo'];
	//
}
