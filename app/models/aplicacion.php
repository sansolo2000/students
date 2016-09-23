<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class aplicacion extends Model
{
	protected $table = 'aplicaciones';
	protected $primaryKey = 'apl_codigo';
	protected $fillable = ['apl_codigo', 'apl_nombre', 'apl_descripcion', 'apl_orden', 'apl_activo'];
	protected $guarded	= ['apl_codigo'];
}
