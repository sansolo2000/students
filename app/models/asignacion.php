<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class asignacion extends Model
{
	protected $table = 'asignaciones';
	protected $primaryKey = 'asi_codigo';
	protected $fillable = ['asi_codigo', 'rol_codigo', 'per_rut', 'updated_at', 'created_at'];
	protected $guarded	= ['asi_codigo'];

}
