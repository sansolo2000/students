<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\helpers\util;
use App\helpers\navegador;
use View;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use App\models\Aplicacion;
use Session;
use App\models\asignatura;
use App\models\asign_profe_curso;

class AsignaturaController extends Controller
{
	public $asg_nombre;
	public $Privilegio_modulo = 'Asignaturas';
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
				$this->asg_nombre = $_POST['asg_nombre'];
				Session::put('search.asignatura', array('asg_nombre' => $this->asg_nombre));
			}
			else {
				if (Session::has('search.asignatura')){
					$exist = 1;
					$search = Session::get('search.asignatura');
					$this->asg_nombre = $search['asg_nombre'];
				}
			}
			$tabla = AsignaturaController::arreglo();

			if ($exist == 0){
				$asignaturas = asignatura::orderBy('asg_orden', 'ASC')
				->paginate($this->paginate);
			}
			else{
				$asignaturas = asignatura::select()
				->orderBy('asg_orden', 'ASC');

				if ($this->asg_nombre != ''){
					$asignaturas = $asignaturas->where('asg_nombre', 'LIKE', '%'.$this->asg_nombre.'%');
				}
				$asignaturas = $asignaturas->paginate($this->paginate);
			}
			$entidad = array('Nombre' => 'Asignaturas', 'controller' => '/'.util::obtener_url().'asignaturas', 'pk' => 'asg_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'col' => 5);
			return view('mantenedor.index')
			->with('menu', $menu)
			->with('tablas', $tabla)
			->with('records', $asignaturas)
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
			$cantidad = asign_profe_curso::where('asg_codigo', '=', $id);
			if ($cantidad == 0){
				$asignatura = asignatura::find($id);
				$asignatura->delete();
			}
			return redirect()->route('asignaturas.index');
		}
	}

	public function create()
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = AsignaturaController::validador();
		if ($privilegio->mas_add == 0){
			return redirect()->route('logout');
		}
		else{
			$tabla = AsignaturaController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'asignaturas', 'pk' => 'apl_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			return view('mantenedor.add')
			->with('menu', $menu)
			->with('validate', $validate)
			->with('title', 'Ingresar Asignatura')
			->with('tablas', $tabla)
			->with('entidad', $entidad);
		}
	}

	public function store()
	{
		$asignatura = new asignatura();
		$input = Input::all();
		$asignatura->asg_nombre 		= $input['asg_nombre'];
		$asignatura->asg_nivel  		= $input['asg_nivel'];
		$asignatura->asg_orden  		= $input['asg_orden'];
		$asignatura->asg_activo  		= isset($input['asg_activo']) ? 1 : 0;
		$asignatura->save();
		return redirect()->route('asignaturas.index');
	}

	public function edit($id)
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = AsignaturaController::validador();
		if ($privilegio->mas_edit == 0){
			return redirect()->route('logout');
		}
		else{
			$tabla = AsignaturaController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'asignaturas', 'pk' => 'asg_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			$record = Asignatura::find($id);
			return view('mantenedor.edit')
			->with('record',$record)
			->with('menu', $menu)
			->with('validate', $validate)
			->with('entidad', $entidad)
			->with('tablas', $tabla)
			->with('title', 'Ingresar Asignatura');
		}
	}

	public function update($id)
	{
		// validate
		// read more on validation at http://laravel.com/docs/validation

		// store
		$asignatura = new Asignatura();
		$asignatura = Asignatura::find($id);
		$input = Input::all();
		$asignatura->asg_nombre 		= $input['asg_nombre'];
		$asignatura->asg_nivel 			= $input['asg_nivel'];
		$asignatura->asg_orden  		= $input['asg_orden'];
		$asignatura->asg_activo  		= isset($input['asg_activo']) ? 1 : 0;
		$asignatura->save();
		return redirect()->route('asignaturas.index');
	}

	public function getAsignaturas(Request $request, $nivel, $curso){
		$datos = asignatura::where('asignaturas.asg_nivel', '=', $nivel)
							->whereNotIn('asg_codigo', function($q) use ($curso){
								$q->select('asg_codigo')
								->from('asign_profe_curso')
								->where('cur_codigo', '=', $curso);
								// more where conditions
							})
							->where('asignaturas.asg_activo', '=', 1)
							->get();
		$records[] = array('id' => -1, 'name' => ':: Seleccionar ::');
		foreach ($datos as $dato)
		{
			$records[] = array('id' => $dato->asg_codigo, 'name' => $dato->asg_nombre);
		}
		//			util::print_a($records,0);
		if ($request->ajax()){
			return response()->json($records);
		}
		else{
			util::print_a($records,0);
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
		$tabla[] = array(	'nombre' 		=> 'Nivel',
				'campo'			=> 'asg_nivel',
				'clase' 		=> 'container col-md-3',
				'validate'		=> '',
				'descripcion'	=> 'Nivel',
				'value'			=> '',
				'tipo'			=> 'input',
				'select'		=> 0,
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
	public function validador(){
		$validate = "
				$().ready(function () {
					$('#myform').validate({
						rules: {

							'asg_nombre'		:	{required: true, minlength: 5, maxlength: 50},
							'asg_orden'			:	{required: true, number: true},
							}
					});

				});";
		return $validate;
	}

}
