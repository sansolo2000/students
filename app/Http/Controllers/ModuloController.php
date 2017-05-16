<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
Use App\models\modulo;
Use App\models\aplicacion;
use App\helpers\util;
use App\helpers\navegador;
use View;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use Session;
use App\models\modulo_asignado;

class ModuloController extends Controller
{
	public $apl_codigo;
	public $apl_nombre;
	public $mod_nombre;
	public $mod_descripcion;
	public $mod_url;
	public $Privilegio_modulo = 'modulos';
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
				$this->mod_nombre = $_POST['mod_nombre'];
				$this->apl_codigo = $_POST['apl_nombre'];
				$this->mod_descripcion = $_POST['mod_descripcion'];
				$this->mod_url = $_POST['mod_url'];
				Session::put('search.modulo', array(	'mod_nombre' => $this->mod_nombre, 
												'apl_codigo' => $this->apl_codigo, 
												'mod_descripcion' => $this->mod_descripcion, 
												'mod_url' => $this->mod_url));
			}
			else{
				if (Session::has('search.modulo')){
					$exist = 1;
					$search = Session::get('search.modulo');
					$this->mod_nombre = $search['mod_nombre'];
					$this->apl_codigo = $search['apl_codigo'];
					$this->mod_descripcion = $search['mod_descripcion'];
					$this->mod_url = $search['mod_url'];
				}
			}
			
			$tabla = ModuloController::arreglo();
		
			if ($exist == 0){
				$modulos = Modulo::join('aplicaciones', 'aplicaciones.apl_codigo', '=', 'modulos.apl_codigo')
								->orderBy('aplicaciones.apl_codigo', 'ASC')
								->orderBy('mod_orden', 'ASC')
								->paginate($this->paginate);
			}
			else{
				$modulos = modulo::select()
							->join('aplicaciones', 'aplicaciones.apl_codigo', '=', 'modulos.apl_codigo')
							->orderBy('aplicaciones.apl_codigo', 'ASC')
							->orderBy('mod_orden', 'ASC');
				
				if ($this->mod_nombre != ''){
					$modulos = $modulos->where('modulos.mod_nombre', 'LIKE', '%'.$this->mod_nombre.'%');
				}
				if ($this->apl_codigo != ''){
					$modulos = $modulos->where('aplicaciones.apl_nombre', 'LIKE', '%'.$this->apl_codigo.'%');
				}
				if ($this->mod_descripcion != ''){
					$modulos = $modulos->where('modulos.mod_descripcion', 'LIKE', '%'.$this->mod_descripcion.'%');
				}				
				if ($this->mod_url != ''){
					$modulos = $modulos->where('modulos.mod_url', 'LIKE', '%'.$this->mod_url.'%');
				}
				
				$modulos = $modulos->paginate($this->paginate);
			}
			$errores = '';
			if (Session::has('search.modulo_errores')){
				$errores = Session::get('search.modulo_errores');
			}
			$entidad = array('Filter' => 1, 'Nombre' => $this->Privilegio_modulo, 'controller' => '/'.util::obtener_url().'modulos', 'pk' => 'mod_codigo', 'clase' => 'container col-md-12', 'col' => 7);
			$renderactive = true;
			return view('mantenedor.index')
			->with('menu', $menu)
			->with('tablas', $tabla)
			->with('records', $modulos)
			->with('entidad', $entidad)
			->with('privilegio', $privilegio)
			->with('renderactive', $renderactive)
			->with('errores', $errores);
		}
	}
	
	public function show(){
		return redirect()->route('modulos.index');
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
			$cantidad = modulo_asignado::where('mod_codigo', '=', $id)->count();
			if ($cantidad == 0){
				$modulo = Modulo::find($id);
				$modulo->delete();
			}
			else{
				$errores = 'No puede eliminar el Modulo, puesto que esta asignado a un perfil';
				Session::put('search.modulo_errores', $errores);
			}
			return redirect()->route('modulos.index');
		}
	}
	
	public function create()
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = ModuloController::validador();
		if ($privilegio->mas_add == 0){
			return redirect()->route('logout');
		}
		else{
			$this->apl_nombre = 	aplicacion::where('apl_activo', '=', '1')
										->orderBy('apl_orden', 'ASC')
										->lists('apl_nombre', 'apl_codigo');
			$this->apl_nombre = util::array_indice($this->apl_nombre, -1);
			$tabla = ModuloController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'modulos', 'pk' => 'mod_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			return view('mantenedor.add')
						->with('menu', $menu)
						->with('validate', $validate)
						->with('title', 'Ingresar Modulos')
						->with('tablas', $tabla)
						->with('entidad', $entidad);
		}
	}
	
	public function store()
	{
		$modulo = new modulo;
		$input = Input::all();
		$modulo->mod_nombre 		= $input['mod_nombre'];
		$modulo->apl_codigo 		= $input['apl_nombre'];
		$modulo->mod_descripcion 	= $input['mod_descripcion'];
		$modulo->mod_url 			= $input['mod_url'];
		$modulo->mod_orden  		= $input['mod_orden'];
		$modulo->mod_activo  		= isset($input['mod_activo']) ? 1 : 0;
		$modulo->save();
		return redirect()->route('modulos.index');
	}
	
	public function edit($id)
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = ModuloController::validador();
		if ($privilegio->mas_edit == 0){
			return redirect()->route('logout');
		}
		else{
			$this->apl_nombre = aplicacion::where('apl_activo', '=', '1')
														->orderBy('apl_orden', 'ASC')
														->lists('apl_nombre', 'apl_codigo');
			$this->apl_nombre = util::array_indice($this->apl_nombre, -1);
			$this->apl_codigo = 'apl_codigo';
			$tabla = ModuloController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'modulos', 'pk' => 'mod_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			$record = Modulo::find($id);
			return view('mantenedor.edit')
						->with('record',$record)
						->with('menu', $menu)
						->with('validate', $validate)
						->with('entidad', $entidad)
						->with('tablas', $tabla)
						->with('title', 'Ingresar Modulos');
		}
	}
	
	public function update($id)
	{
		// validate
		// read more on validation at http://laravel.com/docs/validation
	
		// store
		$modulo = new modulo;
		$modulo = Modulo::find($id);
		$input = Input::all();
		$modulo->mod_nombre = $input['mod_nombre'];
		$modulo->apl_codigo = $input['apl_nombre'];
		$modulo->mod_descripcion = $input['mod_descripcion'];
		$modulo->mod_url = $input['mod_url'];
		$modulo->mod_orden  = $input['mod_orden'];
		$modulo->mod_activo  = isset($input['mod_activo']) ? 1 : 0;
		$modulo->save();
		return redirect()->route('modulos.index');
	}
	
	public function getModulo(Request $request, $apl_codigo, $rol_codigo){
		if ($request->ajax()){
			$datos = rol::join('modulos_asignados', 'roles.rol_codigo', '=', 'modulos_asignados.rol_codigo')
				->where('roles.rol_codigo', '=', $rol_codigo)
				->select('modulos_asignados.mod_codigo')
				->get();
			$records = Modulo::where('apl_codigo', '=', $apl_codigo)
				->where('mod_nombre', '!=', '**---**')
				->wherenotin('mod_codigo', $datos)
				->select('mod_codigo', 'mod_nombre')
				->get();
			return response()->json($records);
		}
	}
	
	public function arreglo(){
		$tabla[] = array(	'nombre' 		=> 'Nombre',
							'campo'			=> 'mod_nombre',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Nombre',
							'tipo'			=> 'input',
							'select'		=> 0,
							'value'			=> $this->mod_nombre,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Aplicacion',
							'campo'			=> 'apl_nombre',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Aplicaci&oacute;n',
							'value'			=> $this->apl_codigo,
							'tipo'			=> 'select',
							'select'		=> $this->apl_nombre,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Descripcion',
							'campo'			=> 'mod_descripcion',
							'clase' 		=> 'container col-md-8',
							'validate'		=> '',
							'descripcion'	=> 'Descripci&oacute;n',
							'value'			=> $this->mod_descripcion,
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Url',
							'campo'			=> 'mod_url',
							'clase' 		=> 'container col-md-8',
							'validate'		=> '',
							'descripcion'	=> 'Url',
							'value'			=> $this->mod_url,
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Orden',
							'campo'			=> 'mod_orden',
							'clase' 		=> 'container col-md-3',
							'validate'		=> '',
							'descripcion'	=> 'Orden',
							'value'			=> '',
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 0,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Estado',
							'campo'			=> 'mod_activo',
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
							'mod_nombre'			:	{required: true, minlength: 5, maxlength: 50},
							'apl_nombre'			:	{required: true, min:1},
							'mod_descripcion'		:	{required: true, minlength: 5, maxlength: 50},
							'mod_url'				:	{required: true, minlength: 5, maxlength: 255},
							'mod_orden'				:	{required: true, number: true}
						},
	  					messages: {
							'apl_nombre'			: { min: 'Seleccione aplicacion' }
						},
					});
	
				});";
		return $validate;
	}
	

}
