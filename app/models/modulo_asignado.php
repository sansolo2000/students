<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class modulo_asignado extends Model
{
	protected $table = 'modulos_asignados';
	protected $primaryKey = 'mas_codigo';
	protected $fillable = ['mod_codigo', 'rol_codigo', 'mas_add', 'mas_edit', 'mas_delete', 'mas_read', 'mas_orden', 'mas_activo'];
	//protected $guarded	= array('mod_codigo', 'rol_codigo');
}



