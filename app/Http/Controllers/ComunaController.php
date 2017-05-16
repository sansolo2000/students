<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
Use App\models\comuna;
Use App\models\region;
use App\helpers\util;
use App\helpers\navegador;
use View;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use Session;
use App\models\colegio;

class ComunaController extends Controller
{
	public $com_nombre;
	public $reg_codigo;
	public $reg_nombre;
	public $Privilegio_modulo = 'comunas';
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
				$this->com_nombre = $_POST['com_nombre'];
				$this->reg_codigo = $_POST['reg_nombre'];
				Session::put('search.comuna', array(	'com_nombre' => $this->com_nombre,
														'reg_codigo' => $this->reg_codigo));
			}
			else{
				if (Session::has('search.comuna')){
					$exist = 1;
					$search = Session::get('search.comuna');
					$this->com_nombre = $search['com_nombre'];
					$this->reg_codigo = $search['reg_codigo'];
				}
			}
				
			$tabla = ComunaController::arreglo();

			if ($exist == 0){
				$comuna = Comuna::join('regiones', 'comunas.reg_codigo', '=', 'regiones.reg_codigo')
				->orderBy('regiones.reg_orden', 'ASC')
				->orderBy('com_orden', 'ASC')
				->paginate($this->paginate);
			}
			else{
				$comuna = Comuna::select()
				->join('regiones', 'comunas.reg_codigo', '=', 'regiones.reg_codigo')
				->orderBy('regiones.reg_orden', 'ASC')
				->orderBy('com_orden', 'ASC');

				if ($this->com_nombre != ''){
					$comuna = $comuna->where('comunas.com_nombre', 'LIKE', '%'.$this->com_nombre.'%');
				}
				if ($this->reg_codigo != ''){
					$comuna = $comuna->where('regiones.reg_nombre', 'LIKE', '%'.$this->reg_codigo.'%');
				}

				$comuna = $comuna->paginate($this->paginate);
			}
			$renderactive = true;
			$errores = '';
			if (Session::has('search.comuna_errores')){
				$errores = Session::get('search.comuna_errores');
			}
			$entidad = array('Filter' => 1, 'Nombre' => $this->Privilegio_modulo, 'controller' => '/'.util::obtener_url().'comunas', 'pk' => 'com_codigo', 'clase' => 'container col-md-10 col-md-offset-1', 'col' => 5);
			return view('mantenedor.index')
			->with('menu', $menu)
			->with('tablas', $tabla)
			->with('records', $comuna)
			->with('entidad', $entidad)
			->with('privilegio', $privilegio)
			->with('renderactive', $renderactive)
			->with('errores', $errores);
		}
	}

	public function show(){
		return redirect()->route('comunas.index');
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
			$cantidad = colegio::where('com_codigo', '=', $id)->count();
			if ($cantidad == 0){
				$comuna = Comuna::find($id);
				$comuna->delete();
			}
			else{
				$errores = 'No puede eliminar la comuna, puesto que esta asociada con un colegio';
				Session::put('search.comuna_errores', $errores);
			}
			return redirect()->route('comunas.index');
		}
	}

	public function create()
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = ComunaController::validador();
		if ($privilegio->mas_add == 0){
			return redirect()->route('logout');
		}
		else{
			$this->reg_nombre = Region::where('reg_activo', '=', '1')
									->orderBy('reg_orden', 'ASC')
									->lists('reg_nombre', 'reg_codigo');
			$this->reg_nombre = util::array_indice($this->reg_nombre, -1);
			$tabla = ComunaController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'comunas', 'pk' => 'com_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			return view('mantenedor.add')
			->with('menu', $menu)
			->with('validate', $validate)
			->with('title', 'Ingresar Comunas')
			->with('tablas', $tabla)
			->with('entidad', $entidad);
		}
	}

	public function store()
	{
		$comuna = new comuna;
		$input = Input::all();
		$comuna->com_nombre 		= $input['com_nombre'];
		$comuna->reg_codigo 		= $input['reg_nombre'];
		$comuna->com_orden  		= $input['com_orden'];
		$comuna->com_activo  		= isset($input['com_activo']) ? 1 : 0;
		$comuna->save();
		return redirect()->route('comunas.index');
	}

	public function edit($id)
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = ComunaController::validador();
		if ($privilegio->mas_edit == 0){
			return redirect()->route('logout');
		}
		else{
			$this->reg_nombre = Region::where('reg_activo', '=', '1')
								->orderBy('reg_orden', 'ASC')
								->lists('reg_nombre', 'reg_codigo');
			$this->reg_nombre = util::array_indice($this->reg_nombre, -1);
			$this->reg_codigo = 'reg_codigo';
			$tabla = ComunaController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'comunas', 'pk' => 'com_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			$record = Comuna::find($id);
			return view('mantenedor.edit')
						->with('record',$record)
						->with('validate', $validate)
						->with('menu', $menu)
						->with('entidad', $entidad)
						->with('tablas', $tabla)
						->with('title', 'Ingresar Comunas');
		}
	}

	public function update($id)
	{
		// validate
		// read more on validation at http://laravel.com/docs/validation

		// store
		$comuna = new comuna;
		$comuna = Comuna::find($id);
		$input = Input::all();
		$comuna->com_nombre = $input['com_nombre'];
		$comuna->reg_codigo = $input['reg_nombre'];
		$comuna->com_orden  = $input['com_orden'];
		$comuna->com_activo  = isset($input['com_activo']) ? 1 : 0;
		$comuna->save();
		return redirect()->route('comunas.index');
	}

	
	public function getComuna(Request $request, $reg_codigo){
		if ($request->ajax()){
			$datos = Comuna::where('comunas.reg_codigo', '=', $reg_codigo)
									->get();
//			util::print_a($datos, 0);
			return response()->json($datos);
		}
	}
	

	public function arreglo(){
		$tabla[] = array(	'nombre' 		=> 'Nombre',
							'campo'			=> 'com_nombre',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Nombre',
							'tipo'			=> 'input',
							'select'		=> 0,
							'value'			=> $this->com_nombre,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Region',
							'campo'			=> 'reg_nombre',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Regi&oacute;n',
							'value'			=> $this->reg_codigo,
							'tipo'			=> 'select',
							'select'		=> $this->reg_nombre,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Orden',
							'campo'			=> 'com_orden',
							'clase' 		=> 'container col-md-3',
							'validate'		=> '',
							'descripcion'	=> 'Orden',
							'value'			=> '',
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 0,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Estado',
							'campo'			=> 'com_activo',
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
							'com_nombre'			:	{required: true, minlength: 5, maxlength: 50},
							'reg_nombre'			:	{required: true, min:1},
							'com_orden'				:	{required: true, number: true}
						},
	  					messages: {
							'reg_nombre'			: { min: 'Seleccione region' }
						},
					});
	
				});";
		return $validate;
	}
	

}
