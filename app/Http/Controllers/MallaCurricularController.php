<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
Use App\models\curso;
Use App\models\asign_profe_curso;
Use App\models\Asignatura;
Use App\models\profesor;
Use App\models\persona;
Use App\models\colegio;
Use App\models\nivel;
use App\helpers\util;
use App\helpers\navegador;
use View;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use DB;
use Session;
use App\models\calificacion;

class MallaCurricularController extends Controller
{
	public $cur_codigo;
	public $asg_codigo;
	public $asg_nombre;
	public $per_rut;
	public $per_nombre;
	public $errores;
	
	public $Privilegio_modulo = 'malla_curricular';
	public $paginate = 20;

	public function index($id = NULL)
	{
		// Menu

		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);

		//Privilegios

		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		if ($privilegio->mas_read == 0){
			return redirect()->route('logout');
		}
		else{
			// Descripcion de tabla.
			$exist = 0;
			
			if (!empty($_POST)){
				$exist = 1;
				$this->cur_codigo 	= $_POST['cur_nombre'];
				Session::put('search.malla_curricular', array(
						'cur_codigo' 	=> $this->cur_codigo
				));
			}
			else{
				if (Session::has('search.malla_curricular')){
					$exist = 1;
					$search = Session::get('search.malla_curricular');
					$this->cur_codigo 	= $search['cur_codigo'];
				}
				else{
					$exist = 1;
					$this->cur_codigo 	= -1;
				}
			}

			$tabla = MallaCurricularController::arreglo();
			if ($exist == 1){
				$asignatura = asign_profe_curso::join('cursos', 'asign_profe_curso.cur_codigo', '=', 'cursos.cur_codigo')
										->join('anyos', 'cursos.any_codigo', '=', 'anyos.any_codigo')
										->join('profesores', 'asign_profe_curso.pro_codigo', '=', 'profesores.pro_codigo')
										->join('asignaturas', 'asign_profe_curso.asg_codigo', '=', 'asignaturas.asg_codigo')
										->join('personas', 'personas.per_rut', '=', 'profesores.per_rut')
										->where('asign_profe_curso.cur_codigo', '=', $this->cur_codigo)
										->where('anyos.any_activo', '=', 1)
										->orderBy('asignaturas.asg_orden', 'ASC')
										->select()
										->paginate($this->paginate);
			}
			$curso = $this->cur_codigo;
			$cantidad = 0;
			$disable = 0;
			if ($curso != -1){
				$cursos = curso::where('cur_codigo', '=', $this->cur_codigo)->first();
				
				$cantidad_asignatura = asignatura::join('anyos', 'asignaturas.any_codigo', '=', 'anyos.any_codigo')
										->where('anyos.any_activo', '=', 1)
										->where('asignaturas.asg_numero', '=', $cursos->cur_numero)
										->where('asignaturas.niv_codigo', '=', $cursos->niv_codigo)
										->count();
				if ($cantidad_asignatura == 0) {
					$this->errores = 'No tiene asignaturas cargadas';
				}
				else {
					$cantidad = asignatura::join('anyos', 'asignaturas.any_codigo', '=', 'anyos.any_codigo')
											->where('anyos.any_activo', '=', 1)
											->where('asignaturas.asg_numero', '=', $cursos->cur_numero)
											->where('asignaturas.niv_codigo', '=', $cursos->niv_codigo)
											->whereNotIn('asg_codigo', function($q) use ($curso){
												$q->select('asg_codigo')
												->from('asign_profe_curso')
												->where('cur_codigo', '=', $curso);
						// more where conditions
					})
					->where('asignaturas.asg_activo', '=', 1)
					->count();
					if ($cantidad == 0){
						$disable = 1;
					}
				}
			}
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => '/'.util::obtener_url().'malla_curricular', 'pk' => 'apc_codigo', 'clase' => 'container col-md-8 col-md-offset-2', 'col' => 3, 'disable' => $disable);
			return view('mantenedor.index_asignatura')
						->with('menu', $menu)
						->with('tablas', $tabla)
						->with('cur_codigo', $this->cur_codigo)
						->with('errores', $this->errores)
						->with('records', $asignatura)
						->with('entidad', $entidad)
						->with('privilegio', $privilegio);
		}
	}

	public function show(){
		return redirect()->route('malla_curricular.index');
	}

	public function destroy($id)
	{
		$idusuario = Auth::user()->per_rut;
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		if ($privilegio->mas_delete == 0){
			return redirect()->route('logout');
		}
		else{
			$cantidad = calificacion::where('apc_codigo', '=', $id)->count();
			if ($cantidad == 0){
				$asign_profe_curso = asign_profe_curso::find($id);
				$asign_profe_curso->delete();
			}
			return redirect()->route('asignaturas.index');
		}
	}

	public function create($id)
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		if ($privilegio->mas_add == 0){
			return redirect()->route('logout');
		}
		else{
			$curso = Curso::join('niveles', 'niveles.niv_codigo', '=', 'cursos.niv_codigo')
							->join('profesores', 'profesores.pro_codigo', '=', 'cursos.pro_codigo')
							->join('personas', 'personas.per_rut', '=', 'profesores.per_rut')
							->where('cursos.cur_codigo', '=', $id)
							->select('cursos.cur_codigo', DB::raw('CONCAT(cursos.cur_numero, "&deg;", cursos.cur_letra, " ", niveles.niv_nombre, " - Profesor Jefe: ", personas.per_nombre, " ", personas.per_apellido_paterno) as name'), 'cursos.cur_numero', 'cursos.niv_codigo')
							->first();
			$tabla = MallaCurricularController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'malla_curricular', 'pk' => 'apc_codigo', 'clase' => 'container col-sm-6 col-sm-offset-3', 'label' => 'container col-md-4');
			return view('mantenedor.add_asignatura')
						->with('menu', $menu)
						->with('title', 'Ingresar Asignaturas')
						->with('curso', $curso)
						->with('tablas', $tabla)
						->with('entidad', $entidad);
		}
	}

	public function store()
	{
		$input = Input::all();
		
		$profesor = new profesor();
		$profesor = Profesor::where('profesores.per_rut', '=', $input['pro_nombre'])->first();
		
		$asign_profe_curso = new asign_profe_curso();
		$asign_profe_curso->pro_codigo = $profesor->pro_codigo;
		$asign_profe_curso->asg_codigo = $input['asg_nombre'];
		$asign_profe_curso->cur_codigo = $input['cur_codigo'];
		$asign_profe_curso->save();
		return redirect()->route('malla_curricular.index');
	}

	public function edit($id)
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		if ($privilegio->mas_edit == 0){
			return redirect()->route('logout');
		}
		else{
			$curso = asign_profe_curso::join('asignaturas', 'asign_profe_curso.asg_codigo', '=', 'asignaturas.asg_codigo')
									->join('profesores', 'profesores.pro_codigo', '=', 'asign_profe_curso.pro_codigo')
									->join('cursos', 'asign_profe_curso.cur_codigo', '=', 'cursos.cur_codigo')
									->join('personas', 'personas.per_rut', '=', 'profesores.per_rut')
									->join('niveles', 'niveles.niv_codigo', '=', 'cursos.niv_codigo')
									->where('asign_profe_curso.apc_codigo', '=', $id)
									->select('asign_profe_curso.apc_codigo', 'asignaturas.asg_codigo', 'cursos.cur_codigo', DB::raw('CONCAT(cursos.cur_numero, "&deg;", cursos.cur_letra, " ", niveles.niv_nombre, " - Profesor Jefe: ", personas.per_nombre, " ", personas.per_apellido_paterno) as name'), 'cursos.cur_numero', 'cursos.niv_codigo', 'profesores.per_rut')
									->first();
										
				
			$tabla = MallaCurricularController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'malla_curricular', 'pk' => 'apc_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
				
			return view('mantenedor.edit_asignatura')
						->with('curso', $curso)
						->with('menu', $menu)
						->with('entidad', $entidad)
						->with('tablas', $tabla)
						->with('title', 'Ingresar Cursos');
		}
	}

	public function update($id)
	{
		$input = Input::all();
		
		$profesor = new profesor();
		$profesor = Profesor::where('profesores.per_rut', '=', $input['pro_nombre'])->first();
		
		$asign_profe_curso = new asign_profe_curso();
		$asign_profe_curso = asign_profe_curso::find($id);
		$asign_profe_curso->pro_codigo = $profesor->pro_codigo;
		$asign_profe_curso->save();

		return redirect()->route('malla_curricular.index');
	}


	public function getProfesores(Request $request){
		if ($request->ajax()){
			$datos = Persona::join('profesores', 'profesores.per_rut', '=', 'personas.per_rut')
								->get();
			$records[] = array('id' => -1, 'name' => ':: Seleccionar ::');
			foreach ($datos as $dato)
			{
				$rut = util::format_rut($dato->per_rut, $dato->per_dv);
				$nombre = $dato->per_nombre.' '.$dato->per_apellido_paterno;
				$records[] = array('id' => $dato->per_rut, 'name' => $rut['numero'].'-'.$rut['dv'].' :: '.$nombre);
			}
			//			util::print_a($records,0);
			return response()->json($records);
		}
	}


	public function arreglo(){
		$tabla[] = array(	'nombre' 		=> 'Asignatura',
							'campo'			=> 'asg_nombre',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Asignatura',
							'value'			=> $this->asg_codigo,
							'tipo'			=> 'select',
							'select'		=> $this->asg_nombre,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Profesor',
							'campo'			=> 'per_rut',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Profesor',
							'value'			=> $this->per_rut,
							'tipo'			=> 'select',
							'select'		=> $this->per_nombre,
							'filter'		=> 1,
							'enable'		=> true);
		return $tabla;

	}



}
