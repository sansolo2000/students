<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class modulo extends Model
{
	protected $table = 'modulos';
	protected $primaryKey = 'mod_codigo';
	protected $fillable = ['mod_codigo', 'apl_codigo', 'mod_url', 'mod_nombre', 'mod_descripcion', 'mod_add', 'mod_read', 'mod_modify', 'mod_print', 'mod_orden', 'mod_activo', 'update_at', 'create_at'];
	protected $guarded	= ['mod_codigo'];
	//
	
	protected static function getModulos($apl_codigo){
		return modelo::where('apl_codigo', '=', $apl_codigo)->get();
	}
	
}
