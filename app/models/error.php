<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class error extends Model
{
	protected $table = 'errores';
	protected $primaryKey = 'err_codigo';
	protected $fillable = ['err_codigo', 'err_fecha', 'err_datos', 'per_rut', 'mod_codigo', 'updated_at', 'created_at'];
	protected $guarded	= ['err_codigo'];    
}


