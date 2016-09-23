<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class alumno extends Model
{
    //
	protected $table = 'alumnos';
	protected $primaryKey = 'alu_codigo';
	protected $fillable = ['alu_codigo', 'cur_codigo', 'per_rut'];
	protected $guarded	= ['alu_codigo'];
}
