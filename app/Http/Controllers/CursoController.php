<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
Use App\models\curso;
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
use App\models\anyo;
use App\models\asignatura;
use App\models\rol;
use App\models\asign_profe_curso;

class CursoController extends Controller
{
	
	public $cur_codigo;
	public $cur_letra;
	public $cur_numero;
	public $col_codigo;
	public $col_nombre;
	public $cur_anyo;
	public $per_rut;
	public $per_nombre;
	public $niv_codigo;
	public $niv_nombre;
	public $cur_cantidad_notas;
	
	public $Privilegio_modulo = 'cursos';
	public $paginate = 10;

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
				$this->cur_letra 	= $_POST['cur_letra'];
				$this->cur_numero 	= $_POST['cur_numero'];
				$this->per_rut 		= $_POST['per_rutbak'];
				$this->niv_codigo 	= $_POST['niv_nombre'];
				Session::put('search.curso', array(	
						'cur_letra' 	=> $this->cur_letra,
						'cur_numero' 	=> $this->cur_numero,
						'per_rut' 		=> $this->per_rut,
						'niv_codigo' 	=> $this->niv_codigo
				));
			}
			else{
				if (Session::has('search.curso')){
					$exist = 1;
					$search = Session::get('search.curso');
					$this->cur_letra 	= $search['cur_letra'];
					$this->cur_numero 	= $search['cur_numero'];
					$this->per_rut 		= $search['per_rut'];
					$this->niv_codigo 	= $search['niv_codigo'];
				}
			}

			$tabla = CursoController::arreglo();

			if ($exist == 0){
				$curso = Curso::join('colegios', 'colegios.col_codigo', '=', 'cursos.col_codigo')
								->join('niveles', 'niveles.niv_codigo', '=', 'cursos.niv_codigo')
								->join('anyos', 'cursos.any_codigo', '=', 'anyos.any_codigo')
								->join('profesores', 'profesores.pro_codigo', '=', 'cursos.pro_codigo')
								->join('personas', 'personas.per_rut', '=', 'profesores.per_rut')
								->where('anyos.any_activo', '=', 1)
								->orderBy('colegios.col_codigo', 'ASC')
								->paginate($this->paginate);
			}
			else{
				$curso = Curso::select()
								->join('colegios', 'colegios.col_codigo', '=', 'cursos.col_codigo')
								->join('niveles', 'niveles.niv_codigo', '=', 'cursos.niv_codigo')
								->join('anyos', 'cursos.any_codigo', '=', 'anyos.any_codigo')
								->join('profesores', 'profesores.pro_codigo', '=', 'cursos.pro_codigo')
								->join('personas', 'personas.per_rut', '=', 'profesores.per_rut')
								->where('anyos.any_activo', '=', 1)
								->orderBy('colegios.col_codigo', 'ASC');

				if ($this->cur_letra != ''){
					$curso = $curso->where('cursos.cur_letra', 'LIKE', '%'.$this->cur_letra.'%');
				}
				if ($this->cur_numero != ''){
					$curso = $curso->where('cursos.cur_numero', 'LIKE', '%'.$this->cur_numero.'%');
				}
				if ($this->niv_codigo != ''){
					$curso = $curso->where('niveles.niv_nombre', 'LIKE', '%'.$this->niv_codigo.'%');
				}
				if ($this->per_rut != ''){
					$curso = $curso->where(DB::raw('CONCAT(personas.per_rut, " ",personas.per_nombre, " ", personas.per_apellido_paterno)'), 'LIKE', '%'.$this->per_rut.'%');
				}
				
				$curso = $curso->paginate($this->paginate);
			}
			$entidad = array('Filter' => 1, 'Nombre' => $this->Privilegio_modulo, 'controller' => '/'.util::obtener_url().'cursos', 'pk' => 'cur_codigo', 'clase' => 'container col-md-10 col-md-offset-1', 'col' => 6);
			return view('mantenedor.index_curso')
						->with('menu', $menu)
						->with('tablas', $tabla)
						->with('records', $curso)
						->with('entidad', $entidad)
						->with('privilegio', $privilegio);
		}
	}

	public function show(){
		return redirect()->route('cursos.index');
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
			$curso = Curso::find($id);
			$curso->delete();
			return redirect()->route('cursos.index');
		}
	}

	public function create()
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		if ($privilegio->mas_add == 0){
			return redirect()->route('logout');
		}
		else{
			$colegio = new colegio();
			$colegio = Colegio::where('col_activo', '=', 1)->first();
			
			$this->niv_nombre = Nivel::where('niv_activo', '=', '1')
										->lists('niv_nombre', 'niv_codigo');
			$this->niv_nombre = util::array_indice($this->niv_nombre, -1);
			$tabla = CursoController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'cursos', 'pk' => 'com_codigo', 'clase' => 'container col-sm-8 col-sm-offset-2', 'label' => 'container col-md-4');
			return view('mantenedor.add_curso')
						->with('menu', $menu)
						->with('niv_nombre', $this->niv_nombre)
						->with('col_nombre', $colegio->col_nombre)
						->with('title', 'Ingresar Cursos')
						->with('tablas', $tabla)
						->with('entidad', $entidad);
		}
	}

	public function store()
	{
		$input = Input::all();
		
		$profesor = new profesor; 
		$profesor = Profesor::where('profesores.per_rut', '=', $input['pro_nombre'])->first();
		
		$colegio = new colegio;
		$colegio = Colegio::where('colegios.col_activo', '=', 1)->first();
		
		$anyo = new anyo();
		$anyo = anyo::where('anyos.any_activo', '=', 1)->first();
		
		
		$curso = new curso;
		$curso->cur_numero 			= $input['cur_numero'];
		$curso->cur_letra 			= strtoupper($input['cur_letra']);
		$curso->niv_codigo  		= $input['niv_nombre'];
		$curso->pro_codigo  		= $profesor['pro_codigo'];
		$curso->col_codigo  		= $colegio['col_codigo'];
		$curso->any_codigo			= $anyo->any_codigo;
		$curso->cur_cantidad_notas 	= $input['cur_cantidad_notas']; 
		$curso->cur_activo  		= isset($input['cur_activo']) ? 1 : 0;
		$curso->save();
		return redirect()->route('cursos.index');
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
			$this->niv_nombre = Nivel::where('niv_activo', '=', '1')
										->lists('niv_nombre', 'niv_codigo');
			$this->niv_nombre = util::array_indice($this->niv_nombre, -1);
			$this->niv_codigo = 'niv_codigo';
			$colegio = new colegio();
			$colegio = Colegio::where('col_activo', '=', 1)->first();
			$tabla = CursoController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'cursos', 'pk' => 'cur_codigo', 'clase' => 'container col-md-8 col-md-offset-2', 'label' => 'container col-md-4');
			$record = Curso::join('colegios', 'colegios.col_codigo', '=', 'cursos.col_codigo')
				->join('niveles', 'niveles.niv_codigo', '=', 'cursos.niv_codigo')
				->join('anyos', 'cursos.any_codigo', '=', 'anyos.any_codigo')
				->join('profesores', 'profesores.pro_codigo', '=', 'cursos.pro_codigo')
				->join('personas', 'personas.per_rut', '=', 'profesores.per_rut')
				->select('cursos.cur_codigo', 'cursos.cur_letra', 'cursos.cur_numero', 'cursos.pro_codigo', 'cursos.niv_codigo', 'niveles.niv_nombre', 'cursos.any_codigo', 'anyos.any_numero', 'personas.per_rut', 'personas.per_dv', 'personas.per_nombre', 'personas.per_apellido_paterno', 'cursos.cur_cantidad_notas', 'cursos.cur_activo')
				->where('cur_codigo', '=', $id)
				->first();
			
			return view('mantenedor.edit_curso')
						->with('record',$record)
						->with('niv_nombre', $this->niv_nombre)
						->with('col_nombre', $colegio->col_nombre)
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
		
		$colegio = new colegio;
		$colegio = Colegio::where('colegios.col_activo', '=', 1)->first();

		$curso = new curso;
		$curso = Curso::find($id);
		$curso->cur_numero 			= $input['cur_numero'];
		$curso->cur_letra 			= strtoupper($input['cur_letra']);
		$curso->niv_codigo  		= $input['niv_nombre'];
		$curso->pro_codigo  		= $profesor['pro_codigo'];
		$curso->col_codigo  		= $colegio['col_codigo'];
		$curso->cur_activo  		= isset($input['cur_activo']) ? 1 : 0;
		$curso->save();
		
		
		return redirect()->route('cursos.index');
	}


	public function getProfesores(Request $request){
		if ($request->ajax()){
			$datos = Persona::join('profesores', 'profesores.per_rut', '=', 'personas.per_rut')
							->where('profesores.pro_activo', '=', 1)
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

	
	public function getCurso(Request $request, $cur_numero, $cur_letra, $niv_codigo){
		if ($request->ajax()){
			$datos = Curso::where('cursos.cur_numero', '=', $cur_numero)
			->where('cursos.cur_letra', '=', strtoupper($cur_letra))
			->where('cursos.niv_codigo', '=', $niv_codigo)
			->get();
			//			util::print_a($records,0);
			return response()->json($datos);
		}
	}

	public function getCursoDisponible(Request $request){
		$datos = Curso::join('niveles', 'cursos.niv_codigo', '=', 'niveles.niv_codigo')
						->select('cursos.cur_codigo as id', DB::raw('CONCAT(cursos.cur_numero, "&deg;", cursos.cur_letra, " ", niveles.niv_nombre) as name'))
						->where('cursos.cur_activo', '=', 1)
						->get();
		$records[] = array('id' => -1, 'name' => ':: Seleccionar ::');
		foreach ($datos as $dato)
		{
			$records[] = array('id' => $dato['id'], 'name' => $dato['name']);
		}	
		if ($request->ajax()){
			return response()->json($records);
		}
		else{
			util::print_a($records,0);
		}
	}

	public function getCursoDisponibleProfesor(Request $request, $per_rut){
		
		$usuario = rol::join('asignaciones', 'roles.rol_codigo', '=', 'asignaciones.rol_codigo')
					->join('personas', 'asignaciones.per_rut', '=', 'personas.per_rut')
					->where('personas.per_rut', '=', $per_rut)
					->where('roles.rol_nombre', '=', 'Profesor');
		
		$curso = Curso::join('profesores', 'cursos.pro_codigo', '=', 'profesores.pro_codigo')
		->select('cursos.cur_codigo')
		->where('cursos.cur_activo', '=', 1)
		->where('profesores.per_rut', '=', $per_rut);

		$asignatura = asign_profe_curso::join('cursos', 'asign_profe_curso.cur_codigo', '=', 'cursos.cur_codigo')
		->join('profesores', 'asign_profe_curso.pro_codigo', '=', 'profesores.pro_codigo')
		->select('cursos.cur_codigo')
		->where('cursos.cur_activo', '=', 1)
		->where('profesores.per_rut', '=', $per_rut);
		
		$curso_asignatura = $curso->union($asignatura)->Get()->toArray();
		
		$datos = Curso::join('niveles', 'cursos.niv_codigo', '=', 'niveles.niv_codigo')
		->join('profesores', 'cursos.pro_codigo', '=', 'profesores.pro_codigo')
		->select('cursos.cur_codigo as id', DB::raw('CONCAT(cursos.cur_numero, "&deg;", cursos.cur_letra, " ", niveles.niv_nombre) as name'));
		if ($usuario->count()>0){
			$datos = $datos->wherein('cursos.cur_codigo', $curso_asignatura);
		}
		else{
			$datos = $datos->where('cursos.cur_activo', '=', 1);
		}
		$datos = $datos->get();
		
		$records[] = array('id' => -1, 'name' => ':: Seleccionar ::');
		foreach ($datos as $dato)
		{
			$records[] = array('id' => $dato['id'], 'name' => $dato['name']);
		}
		if ($request->ajax()){
			return response()->json($records);
		}
		else{
			util::print_a($records,0);
		}
	}
	
	
	public function arreglo(){
		$tabla[] = array(	'nombre' 		=> 'Numero',
							'campo'			=> 'cur_numero',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Numero',
							'tipo'			=> 'input',
							'select'		=> 0,
							'value'			=> $this->cur_numero,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Letra',
							'campo'			=> 'cur_letra',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Letra',
							'tipo'			=> 'input',
							'select'		=> 0,
							'value'			=> $this->cur_letra,
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
		$tabla[] = array(	'nombre' 		=> 'Nivel',
							'campo'			=> 'niv_nombre',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Nivel',
							'value'			=> $this->niv_codigo,
							'tipo'			=> 'select',
							'select'		=> $this->niv_nombre,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Cantidad Notas',
							'campo'			=> 'cur_cantidad_notas',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Cantidad Notas',
							'tipo'			=> 'input',
							'select'		=> 0,
							'value'			=> $this->cur_cantidad_notas,
							'filter'		=> 3,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Estado',
							'campo'			=> 'cur_activo',
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
