<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\helpers\navegador;
use Session;
use App\Http\Requests;
use App\models\curso;
use App\helpers\util;
use DB;
use App\models\periodo;
use App\models\calificacion;
use Maatwebsite\Excel\Facades\Excel;
use App\models\alumno;
use Illuminate\Support\Facades\Input;
use App\models\asignatura;
use Hamcrest\Type\IsNumeric;
use App\models\profesor;
use App\models\anyo;
use App\models\asignacion;


class VerNotasController extends Controller
{
	public $cur_codigo;
	public $asg_nombre;
	public $per_rut;
	public $per_nombre;
	public $pri_codigo;
	public $errores;
	
	
	public $Privilegio_modulo = 'Ver Notas';
	public $paginate = 20;
	
	public function index($id = NULL)
	{
		// Menu
	
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$cantidad = 0;

		//Privilegios
		
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		if ($privilegio->mas_read == 0){
			return redirect()->route('logout');
		}
		else{
			// Descripcion de tabla.
			$persona = VerNotasController::rol_usuario($idusuario);
			if ($persona->rol_nombre == 'Profesor'){
				$cursos = curso::select(DB::raw('CONCAT(cursos.cur_numero, "&deg;", cursos.cur_letra, " ", niveles.niv_nombre) as name'))
								->join('niveles', 'cursos.niv_codigo', '=', 'niveles.niv_codigo')
								->join('profesores as pj', 'cursos.pro_codigo', '=', DB::raw('pj.pro_codigo'))
								->join('personas AS pr', DB::raw('pr.per_rut'), '=', 'pj.per_rut')
								->where(DB::raw('pj.per_rut'), '=', $idusuario)
								->where('cursos.cur_activo', '=', 1)
								->get();
				
			}
			else {
				$cursos = curso::select('cursos.cur_codigo', DB::raw('CONCAT(cursos.cur_numero, "&deg;", cursos.cur_letra, " ", niveles.niv_nombre) as name'))
								->join('niveles', 'cursos.niv_codigo', '=', 'niveles.niv_codigo')
								->join('profesores as pj', 'cursos.pro_codigo', '=', DB::raw('pj.pro_codigo'))
								->join('personas AS pr', DB::raw('pr.per_rut'), '=', 'pj.per_rut')
								->where('cursos.cur_activo', '=', 1)
								->get();
			}
			$tabla = '';
				
			foreach ($cursos as $curso){
				$tabla .= '<tr>';
				$tabla .= '<td>'.$curso->name.'</td>';
				$control = VerNotasController::asignaturas_curso($curso->cur_codigo, $persona);
				$tabla .= '<td>'.$control.'</td>';
				$tabla .= '<td>Column content</td>';
				$tabla .= '</tr>';
			}
			
			$ubicacion = 1;
			$periodos = periodo::orderBy('periodos.pri_orden', 'ASC')->get();
 			$asignatura_seleccionada = '';
 			$ubicacion = 1;
 			
 			$periodo = new periodo();
 			$periodo = periodo::orderBy('pri_orden', 'ASC')
 			->lists('pri_nombre', 'pri_codigo')
 			->toArray();
 			
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => '/'.util::obtener_url().'vernotas', 'pk' => 'cur_codigo', 'clase' => 'container col-md-12', 'col' => 5);
			return view('main.vernotas')
					->with('menu', $menu)
//					->with('cuerpo', $cuerpo)
					->with('cur_codigo', $this->cur_codigo)
					->with('pri_codigo', $this->pri_codigo)
//					->with('periodo2', $periodo2)
					->with('periodo', $periodo)
					->with('user', $idusuario)
//					->with('profesor', $profesor)
//					->with('record', $asignaturas)
//					->with('cabecera', $cabecera)
					->with('entidad', $entidad)
//					->with('script', $script)
//					->with('modal', $modal)
					->with('errores', $this->errores)
					->with('CantidadNotas', $cantidad)
					->with('privilegio', $privilegio);
					
		}
	}
	
	public function show(){
		return redirect()->route('vernotas.index');
	}
	
	private function rol_usuario($idusuario){
		$persona = asignacion::join('roles', 'asignaciones.rol_codigo', '=', 'roles.rol_codigo')
		->where('asignaciones.per_rut', '=', $idusuario)
		->where('roles.rol_admin', '=', 1)
		->first();
		return $persona;
	}
	
	private function asignaturas_curso($cur_codigo, $persona){
		
		$curso = curso::join('profesores', 'cursos.cur_codigo', '=', 'profesores.cur_codigo')
						->where('profesores', 'profesores.per_rut', '=', $persona->per_rut)
						->where('cursos.cur_codigo', '=', $cur_codigo)
						->get();
		
		if ($curso->count() > 0){				
			$asignatura = asignatura::join('cursos', 'asignaturas.cur_codigo', '=', 'cursos.cur_codigo')
							->join('profesores', 'cursos.pro_codigo', '=', 'profesores.pro_codigo')
							->where('cursos.cur_codigo', '=', $cur_codigo)
							->where('profesores.per_rut', '=', $persona->per_rut)
							->get();
		}
		else {
			$asignatura = asignatura::join('cursos', 'asignaturas.cur_codigo', '=', 'cursos.cur_codigo')
									->join('profesores', 'cursos.pro_codigo', '=', 'profesores.pro_codigo')
									->where('cursos.cur_codigo', '=', $cur_codigo)
									->where('profesores.per_rut', '=', $persona->per_rut)
									->get();
		}
		$control = 
	}
	
	//
}
