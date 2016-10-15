<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class calificacion extends Model
{
	protected $table = 'calificaciones';
	protected $primaryKey = 'cal_codigo';
	protected $fillable = ['cal_codigo', 'cal_numero', 'cal_posicion', 'alu_codigo', 'asi_codigo', 'updated_at', 'created_at'];
	protected $guarded	= ['cal_codigo'];
		
}
