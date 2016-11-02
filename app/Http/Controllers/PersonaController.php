<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
Use App\models\persona;
use App\models\rol;
use App\helpers\util;


use App\Http\Requests;
use App\models\profesor;

class PersonaController extends Controller
{
	public function getPersonaRol(Request $request, $per_rut, $cur_codigo){
		$usuarios = rol::select('personas.per_rut', 'roles.rol_codigo', 'roles.rol_nombre')
						->join('asignaciones', 'roles.rol_codigo', '=', 'asignaciones.rol_codigo')
						->join('personas', 'asignaciones.per_rut', '=', 'personas.per_rut')
						->where('personas.per_rut', '=', $per_rut)
						->get();

		foreach ($usuarios as $usuario){
			$jefe = '';
			if ($usuario->rol_nombre == 'Profesor'){
				$profesor = profesor::select('cursos.cur_codigo')	
						->join('cursos', 'profesores.pro_codigo', '=', 'cursos.pro_codigo')
						->where('profesores.per_rut', '=', $per_rut)
						->where('cursos.cur_codigo','=', $cur_codigo)
						->get();
				if ($profesor->count() > 0){
					$jefe = 'Jefe';
				}
				$record[] = array(	'per_rut' 		=> $usuario->per_rut,
									'rol_nombre' 	=> $usuario->rol_nombre,
									'jefe'			=> $jefe);
			}
			if ($usuario->rol_nombre == 'Administrador' || $usuario->rol_nombre == 'Direccion'){
				$record[] = array(	'per_rut' 		=> $usuario->per_rut,
									'rol_nombre' 	=> $usuario->rol_nombre,
									'jefe'			=> 'Admin');
				
			}		
		}
	
		if ($request->ajax()){
			return response()->json($record);
		}
		else{
			util::print_a($record,0);				
			return $usuario;
		}
	}
}
