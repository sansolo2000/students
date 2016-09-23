<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class curso extends Model
{
    //
	protected $table = 'cursos';
	protected $primaryKey = 'cur_codigo';
	protected $fillable = ['cur_codigo', 'cur_letra', 'cur_numero', 'col_codigo', 'pro_codigo', 'niv_codigo', 'cur_activo'];
	protected $guarded	= ['cur_codigo'];
	
	
}
