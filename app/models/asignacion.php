<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class asignacion extends Model
{
	protected $table = 'asignaciones';
	protected $primaryKey = 'asi_codigo';
	protected $fillable = ['asi_codigo', 'rol_codigo', 'per_rut'];
	protected $guarded	= ['asi_codigo'];

}
