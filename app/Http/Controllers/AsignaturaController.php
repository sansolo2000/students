<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
Use App\models\curso;
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

class AsignaturaController extends Controller
{

	public $cur_codigo;
	public $asg_nombre;
	public $per_rut;
	public $per_nombre;
	
	public $Privilegio_modulo = 'Asignaturas';
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
				Session::put('search.asignatura', array(
						'cur_codigo' 	=> $this->cur_codigo
				));
			}
			else{
				if (Session::has('search.asignatura')){
					$exist = 1;
					$search = Session::get('search.asignatura');
					$this->cur_codigo 	= $search['cur_codigo'];
				}
				else{
					$exist = 1;
					$this->cur_codigo 	= -1;
				}
			}

			$tabla = AsignaturaController::arreglo();
			if ($exist == 1){
				$asignatura = Asignatura::select()
										->join('profesores', 'profesores.pro_codigo', '=', 'asignaturas.pro_codigo')
										->join('personas', 'personas.per_rut', '=', 'profesores.per_rut')
										->orderBy('asignaturas.asg_orden', 'ASC')
										->where('asignaturas.cur_codigo', '=', $this->cur_codigo)
										->paginate($this->paginate);
			}
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => '/'.util::obtener_url().'asignaturas', 'pk' => 'asg_codigo', 'clase' => 'container col-md-12', 'col' => 5);
			return view('mantenedor.index_asignatura')
						->with('menu', $menu)
						->with('tablas', $tabla)
						->with('cur_codigo', $this->cur_codigo)
						->with('records', $asignatura)
						->with('entidad', $entidad)
						->with('privilegio', $privilegio);
		}
	}

	public function show(){
		return redirect()->route('asignaturas.index');
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
			$asignatura = Asignatura::find($id);
			$asignatura->delete();
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
							->select('cursos.cur_codigo', DB::raw('CONCAT(cursos.cur_numero, "&deg;", cursos.cur_letra, " ", niveles.niv_nombre, " - Profesor Jefe: ", personas.per_nombre, " ", personas.per_apellido_paterno) as name'))
							->first();
			$tabla = AsignaturaController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'asignaturas', 'pk' => 'asg_codigo', 'clase' => 'container col-sm-6 col-sm-offset-3', 'label' => 'container col-md-4');
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
		
		$profesor = new profesor;
		$profesor = Profesor::where('profesores.per_rut', '=', $input['pro_nombre'])->first();

		$asignatura = new asignatura();
		$asignatura->asg_nombre = $input['asg_nombre'];
		$asignatura->cur_codigo = $input['cur_codigo'];
		$asignatura->pro_codigo = $profesor->pro_codigo;
		$asignatura->asg_orden = $input['asg_orden'];
		$asignatura->asg_activo = isset($input['asg_activo']) ? 1 : 0;
		$asignatura->save();
		return redirect()->route('asignaturas.index');
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
			$asignatura = Asignatura::join('profesores', 'profesores.pro_codigo', '=', 'asignaturas.pro_codigo')
									->join('cursos', 'asignaturas.cur_codigo', '=', 'cursos.cur_codigo')
									->join('personas', 'personas.per_rut', '=', 'profesores.per_rut')
									->where('asignaturas.asg_codigo', '=', $id)
									->first();
										
			$curso = Curso::join('niveles', 'niveles.niv_codigo', '=', 'cursos.niv_codigo')
									->join('profesores', 'profesores.pro_codigo', '=', 'cursos.pro_codigo')
									->join('personas', 'personas.per_rut', '=', 'profesores.per_rut')
									->select('cursos.cur_codigo', DB::raw('CONCAT(cursos.cur_numero, "&deg;", cursos.cur_letra, " ", niveles.niv_nombre, " - Profesor Jefe: ", personas.per_nombre, " ", personas.per_apellido_paterno) as name'))
									->where('cursos.cur_codigo', '=', $asignatura->cur_codigo)
									->first();
				
			$tabla = AsignaturaController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'asignaturas', 'pk' => 'asg_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
				
			return view('mantenedor.edit_asignatura')
						->with('asignatura',$asignatura)
						->with('curso', $curso)
						->with('menu', $menu)
						->with('entidad', $entidad)
						->with('tablas', $tabla)
						->with('title', 'Ingresar Cursos');
		}
	}

	public function update($id)
	{
		// validate
		// read more on validation at http://laravel.com/docs/validation

		// store
		$input = Input::all();

		$profesor = new profesor;
		$profesor = Profesor::where('profesores.per_rut', '=', $input['pro_nombre'])->first();

		$asignatura = new asignatura();
		$asignatura = Asignatura::find($id);
		$asignatura->asg_nombre = $input['asg_nombre'];
		$asignatura->cur_codigo = $input['cur_codigo'];
		$asignatura->pro_codigo = $profesor->pro_codigo;
		$asignatura->asg_orden = $input['asg_orden'];
		$asignatura->asg_activo = isset($input['asg_activo']) ? 1 : 0;
		$asignatura->save();
		return redirect()->route('asignaturas.index');
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
		$tabla[] = array(	'nombre' 		=> 'Nombre',
							'campo'			=> 'asg_nombre',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Nombre',
							'tipo'			=> 'input',
							'select'		=> 0,
							'value'			=> $this->asg_nombre,
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
		$tabla[] = array(	'nombre' 		=> 'Orden',
							'campo'			=> 'asg_orden',
							'clase' 		=> 'container col-md-3',
							'validate'		=> '',
							'descripcion'	=> 'Orden',
							'value'			=> '',
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 0,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Estado',
							'campo'			=> 'asg_activo',
							'clase' 		=> 'container col-md-1',
							'validate'		=> '',
							'descripcion'	=> 'Activo',
							'value'			=> '',
							'tipo'			=> 'check',
							'select'		=> 0,
							'filter'		=> 0,
							'enable'		=> true);
		return $tabla;

	}



}
