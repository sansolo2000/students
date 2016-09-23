<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class colegio extends Model
{
	protected $table = 'colegios';
	protected $primaryKey = 'col_codigo';
	protected $fillable = ['col_codigo', 'col_nombre', 'col_direccion', 'com_codigo', 'col_activo'];
	protected $guarded	= ['col_codigo'];
	//
}
