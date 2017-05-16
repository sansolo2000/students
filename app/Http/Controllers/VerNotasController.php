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
	
	
	public $Privilegio_modulo = 'ver_notas';
	public $paginate = 20;
	
	public function index($id = NULL)
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$cantidad = 0;
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		if ($privilegio->mas_read == 0){
			return redirect()->route('logout');
		}
		else{
			$tabla = '';
			$ubicacion = 1;
			$periodos = periodo::join('anyos', 'periodos.any_codigo', '=', 'anyos.any_codigo')
								->where('anyos.any_activo', '=', 1)
								->orderBy('periodos.pri_orden', 'ASC')->get();
 			$asignatura_seleccionada = '';
 			$ubicacion = 1;
 			
 			$periodo = new periodo();
 			$periodo = periodo::join('anyos', 'periodos.any_codigo', '=', 'anyos.any_codigo')
								->where('anyos.any_activo', '=', 1)
								->orderBy('periodos.pri_orden', 'ASC')
					 			->lists('pri_nombre', 'pri_codigo')
					 			->toArray();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => '/'.util::obtener_url(), 'pk' => 'cur_codigo', 'clase' => 'container col-md-12', 'col' => 5);
			return view('main.vernotas')
					->with('menu', $menu)
					->with('id_usuario', $idusuario)
					->with('cur_codigo', $this->cur_codigo)
					->with('pri_codigo', $this->pri_codigo)
					->with('periodo', $periodo)
					->with('user', $idusuario)
					->with('entidad', $entidad)
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
		->first();
		return $persona;
	}
	
	public function cursos_mostrar(Request $request, $idusuario){
		
		$roles = VerNotasController::rol_usuario($idusuario);
		
		switch ($roles->rol_nombre) {
			case 'Administrador':
			case 'Direccion':
				$cursos = curso::select('cursos.cur_codigo', DB::raw('CONCAT(cursos.cur_numero, "&deg;", cursos.cur_letra, " ", niveles.niv_nombre) as name'))
									->join('niveles', 'cursos.niv_codigo', '=', 'niveles.niv_codigo')
									->join('profesores as pj', 'cursos.pro_codigo', '=', DB::raw('pj.pro_codigo'))
									->join('personas AS pr', DB::raw('pr.per_rut'), '=', 'pj.per_rut')
									->where('cursos.cur_activo', '=', 1)
									->groupBy('cursos.cur_codigo', DB::raw('CONCAT(cursos.cur_numero, "&deg;", cursos.cur_letra, " ", niveles.niv_nombre)'))
									->get();
				break;
				case 'Profesor':
					$cursos = curso::select('cursos.cur_codigo', DB::raw('CONCAT(cursos.cur_numero, "&deg;", cursos.cur_letra, " ", niveles.niv_nombre) as name'))
									->join('niveles', 'cursos.niv_codigo', '=', 'niveles.niv_codigo')
									->join('profesores as pj', 'cursos.pro_codigo', '=', DB::raw('pj.pro_codigo'))
									->join('personas AS pr', DB::raw('pr.per_rut'), '=', 'pj.per_rut')
									->where('cursos.cur_activo', '=', 1)
									->where('pr.per_rut', '=', $roles->per_rut)
									->groupBy('cursos.cur_codigo', DB::raw('CONCAT(cursos.cur_numero, "&deg;", cursos.cur_letra, " ", niveles.niv_nombre)'))
									->get();
					break;
				case 'Apoderado':
					$cursos = curso::select('cursos.cur_codigo', DB::raw('CONCAT(cursos.cur_numero, "&deg;", cursos.cur_letra, " ", niveles.niv_nombre) as name'))
									->join('niveles', 'cursos.niv_codigo', '=', 'niveles.niv_codigo')
									->join('alumnos as al', 'cursos.cur_codigo', '=', DB::raw('al.cur_codigo'))
									->join('apoderados_alumnos as aa', DB::raw('al.alu_codigo'), '=', DB::raw('aa.alu_codigo'))
									->join('apoderados as ap', DB::raw('ap.apo_codigo'), '=', DB::raw('aa.apo_codigo'))
									->join('personas AS pr', DB::raw('pr.per_rut'), '=', 'ap.per_rut')
									->where('pr.per_rut', '=', $roles->per_rut)
									->where('cursos.cur_activo', '=', 1)
									->groupBy('cursos.cur_codigo', DB::raw('CONCAT(cursos.cur_numero, "&deg;", cursos.cur_letra, " ", niveles.niv_nombre)'))
									->get();
					break;
				case 'Alumno':
					$cursos = curso::select('cursos.cur_codigo', DB::raw('CONCAT(cursos.cur_numero, "&deg;", cursos.cur_letra, " ", niveles.niv_nombre) as name'))
									->join('niveles', 'cursos.niv_codigo', '=', 'niveles.niv_codigo')
									->join('alumnos as al', 'cursos.cur_codigo', '=', DB::raw('al.cur_codigo'))
									->join('personas AS pr', DB::raw('pr.per_rut'), '=', 'al.per_rut')
									->where('cursos.cur_activo', '=', 1)
									->where('pr.per_rut', '=', $roles->per_rut)
									->groupBy('cursos.cur_codigo', DB::raw('CONCAT(cursos.cur_numero, "&deg;", cursos.cur_letra, " ", niveles.niv_nombre)'))
									->get();
						break;
		}
		$records[] = array('id' => -1, 'name' => ':: Seleccionar ::');
		foreach ($cursos as $curso)
		{
			$records[] = array('id' => $curso['cur_codigo'], 'name' => $curso['name']);
		}
		if ($request->ajax()){
			return response()->json($records);
		}
		else{
			util::print_a($records,0);
		}
	}

	public function alumnos_mostrar(Request $request, $idusuario, $idcurso){
		$roles = VerNotasController::rol_usuario($idusuario);
		switch ($roles->rol_nombre) {
			case 'Administrador':
			case 'Direccion':
			case 'Profesor':
				$alumnos = curso::select(DB::raw('`pr`.per_rut, `pr`.per_dv, `pr`.per_nombre, `pr`.per_apellido_paterno, `pr`.per_apellido_materno'))
				->join('alumnos AS al', 'cursos.cur_codigo', '=', 'al.cur_codigo')
				->join('personas AS pr', DB::raw('pr.per_rut'), '=', 'al.per_rut')
				->where('cursos.cur_activo', '=', 1)
				->where('cursos.cur_codigo', '=', $idcurso)
				->get();
				break;
			case 'Apoderado':
				$alumnos = curso::select(DB::raw('`pr`.per_rut, `pr`.per_dv, `pr`.per_nombre, `pr`.per_apellido_paterno, `pr`.per_apellido_materno'))
				->join('alumnos as al', 'cursos.cur_codigo', '=', DB::raw('al.cur_codigo'))
				->join('apoderados_alumnos as aa', DB::raw('al.alu_codigo'), '=', DB::raw('aa.alu_codigo'))
				->join('apoderados as ap', DB::raw('ap.apo_codigo'), '=', DB::raw('aa.apo_codigo'))
				->join('personas AS pr', DB::raw('pr.per_rut'), '=', 'al.per_rut')
				->where('ap.per_rut', '=', $idusuario)
				->where('cursos.cur_codigo', '=', $idcurso)
				->where('cursos.cur_activo', '=', 1)
				->get();
				break;
			case 'Alumno':
				$alumnos = curso::select(DB::raw('`pr`.per_rut, `pr`.per_dv, `pr`.per_nombre, `pr`.per_apellido_paterno, `pr`.per_apellido_materno'))
				->join('alumnos AS al', 'cursos.cur_codigo', '=', 'al.cur_codigo')
				->join('personas AS pr', DB::raw('pr.per_rut'), '=', 'al.per_rut')
				->where('cursos.cur_activo', '=', 1)
				->where('cursos.cur_codigo', '=', $idcurso)
				->where('al.per_rut', '=', $idusuario)
				->get();
				break;
		}
		$records[] = array('id' => -1, 'name' => ':: Seleccionar ::');
		foreach ($alumnos as $alumno)
		{
			$rut = util::format_rut($alumno['per_rut'], $alumno['per_dv']);
			$records[] = array('id' => $alumno['per_rut'], 'name' => $rut['numero'].'-'.$rut['dv'].' - '.$alumno['per_nombre'].' '.$alumno['per_apellido_paterno'].' '.$alumno['per_apellido_materno']);
		}
		if ($request->ajax()){
			return response()->json($records);
		}
		else{
			util::print_a($records,0);
		}
	}
	
	public function notas_mostrar(Request $request, $idusuario, $per_rut, $idcurso){
		$result = $this->notas_mostrar_get($idusuario, $per_rut, $idcurso);
		if ($request->ajax()){
			return response()->json($result);
		}
		else{
			return $result;
		}
	}
	
	public static function notas_mostrar_get($idusuario, $per_rut, $idcurso){
		$periodos = periodo::join('anyos', 'periodos.any_codigo', '=', 'anyos.any_codigo')
		->where('anyos.any_activo', '=', 1)
		->orderBy('periodos.pri_orden', 'ASC');
		$cantidadperiodo = $periodos->count();
		$periodos = $periodos->get();
		$cursos = curso::where('cursos.cur_codigo', '=', $idcurso)->first();
		$cantidadnotas = $cursos->cur_cantidad_notas;
		$width = floor(90 / ((($cantidadnotas + 1) * $cantidadperiodo) + 1));
		$width_final = 90 - ($width * (((($cantidadnotas + 1) * $cantidadperiodo) + 1)));

		$calificaciones_cantidad = alumno::join('calificaciones', 'calificaciones.alu_codigo', '=', 'alumnos.alu_codigo')
										->join('asign_profe_curso', 'calificaciones.apc_codigo', '=','asign_profe_curso.apc_codigo')
										->join('asignaturas', 'asignaturas.asg_codigo', '=', 'asign_profe_curso.asg_codigo')
										->join('periodos', 'periodos.pri_codigo', '=', 'calificaciones.pri_codigo')
										->where('alumnos.per_rut', '=',$per_rut)
										->count();
		if ($calificaciones_cantidad == 0){
			$ExiteCalificaciones = false;
		}
		else{
			$ExiteCalificaciones = true;
		}
		$result['columnas'] = array('cantidadperiodo' => $cantidadperiodo, 'cantidadnotas' => $cantidadnotas, 'width' => $width, 'width_final' => $width_final, 'exitencalificaciones' => $ExiteCalificaciones);
		
		$pos = 1;
		$asignaturas = asignatura::join('asign_profe_curso', 'asignaturas.asg_codigo', '=', 'asign_profe_curso.asg_codigo')
									->where('asign_profe_curso.cur_codigo', '=', $idcurso)
									->orderBy('asignaturas.asg_orden', 'ASC')
									->get();
		$indasignatura = 0;
		foreach ($asignaturas as $asignatura){
			$indperiodo = 0;
			foreach ($periodos as $periodo){
				for ($i=1; $i <= $cantidadnotas; $i++){
					$calificaciones = alumno::join('calificaciones', 'calificaciones.alu_codigo', '=', 'alumnos.alu_codigo')
											->join('asign_profe_curso', 'calificaciones.apc_codigo', '=', 'asign_profe_curso.apc_codigo')
											->join('asignaturas', 'asignaturas.asg_codigo', '=', 'asign_profe_curso.asg_codigo')
											->join('periodos', 'periodos.pri_codigo', '=', 'calificaciones.pri_codigo')
											->where('asignaturas.asg_codigo', '=', $asignatura->asg_codigo)
											->where('periodos.pri_codigo', '=', $periodo->pri_codigo)
											->where('calificaciones.cal_posicion', '=', $i)
											->where('alumnos.per_rut', '=',$per_rut);
					$calificacionexiste = $calificaciones->count();
					if ($calificacionexiste == 1){
						$calificacion = $calificaciones->first();
						if ((float) $calificacion->cal_numero > 0){
							$records[] = array('posicion' => $i, 'nota' => (float) $calificacion->cal_numero);
						}
						else {
							$records[] = array('posicion' => $i, 'nota' => 'X');
						}
					}
					else{
						$records[] = array('posicion' => $i, 'nota' => 'X');
					}
				}
				$result['notas'][$indasignatura][$indperiodo] = array('asg_nombre' => $asignatura->asg_nombre, $records);
				$indperiodo++;
				unset($records);
			}
			$indasignatura++;
		}
		return $result;
	}
}
