<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class apoderado_alumno extends Model
{
	protected $table = 'apoderados_alumnos';
	protected $primaryKey = 'apa_codigo';
	protected $fillable = ['apa_codigo', 'apo_codigo', 'alu_codigo', 'updated_at', 'created_at'];
	protected $guarded	= ['apa_codigo'];
}
