<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
Use App\models\modulo;
Use App\models\aplicacion;
Use App\models\rol;
use App\helpers\util;
use App\helpers\navegador;
use View;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use Session;
use App\models\modulo_asignado;

class Modulo_AsignadoController extends Controller
{
	public $rol_codigo;
	public $rol_nombre;
	public $mod_codigo;
	public $mod_nombre;
	public $apl_codigo;
	public $apl_nombre;
	public $mas_add;
	public $mas_edit;
	public $mas_delete;
	public $mas_read;
	public $mas_orden;
	public $mas_activo;
	public $Privilegio_modulo = 'perfiles';
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
				$this->rol_codigo = $_POST['rol_nombre'];
				$this->mod_codigo = $_POST['mod_nombre'];
				$this->apl_codigo = $_POST['apl_nombre'];
				Session::put('search.modulo_asignados', array(	'rol_codigo' => $this->rol_codigo, 
																'mod_codigo' => $this->mod_codigo, 
																'apl_codigo' => $this->apl_codigo));
			}
			else{
				if (Session::has('search.modulo_asignados')){
					$exist = 1;
					$search = Session::get('search.modulo_asignados');
					$this->rol_codigo = $search['rol_codigo'];
					$this->mod_codigo = $search['mod_codigo'];
					$this->apl_codigo = $search['apl_codigo'];
				}
			}
			
			$tabla = Modulo_AsignadoController::arreglo();
		
			if ($exist == 0){
				$modulos_asignados = Modulo_Asignado::join('roles', 'roles.rol_codigo', '=', 'modulos_asignados.rol_codigo')
								->join('modulos', 'modulos.mod_codigo', '=', 'modulos_asignados.mod_codigo')
								->join('aplicaciones', 'aplicaciones.apl_codigo', '=', 'modulos.apl_codigo')
								->orderBy('roles.rol_orden', 'ASC')
								->orderBy('aplicaciones.apl_orden', 'ASC')
								->orderBy('modulos.mod_orden', 'ASC')
								->paginate($this->paginate);
			}
			else{
				$modulos_asignados = Modulo_Asignado::select()
								->join('roles', 'roles.rol_codigo', '=', 'modulos_asignados.rol_codigo')
								->join('modulos', 'modulos.mod_codigo', '=', 'modulos_asignados.mod_codigo')
								->join('aplicaciones', 'aplicaciones.apl_codigo', '=', 'modulos.apl_codigo')
								->orderBy('aplicaciones.apl_orden', 'ASC')
								->orderBy('modulos.mod_orden', 'ASC')
								->orderBy('mod_orden', 'ASC');
				
				if ($this->rol_codigo != ''){
					$modulos_asignados = $modulos_asignados->where('roles.rol_nombre', 'LIKE', '%'.$this->rol_codigo.'%');
				}
				if ($this->mod_codigo != ''){
					$modulos_asignados = $modulos_asignados->where('modulos.mod_nombre', 'LIKE', '%'.$this->mod_codigo.'%');
				}
				if ($this->apl_codigo != ''){
					$modulos_asignados = $modulos_asignados->where('aplicaciones.apl_nombre', 'LIKE', '%'.$this->apl_codigo.'%');
				}				
				$modulos_asignados = $modulos_asignados->paginate($this->paginate);
			}
			$entidad = array('Filter' => 1, 'Nombre' => $this->Privilegio_modulo, 'controller' => '/'.util::obtener_url().'perfiles', 'pk' => 'mas_codigo', 'clase' => 'container col-md-12', 'col' => 9);
			$renderactive = true;
			return view('mantenedor.index')
			->with('menu', $menu)
			->with('tablas', $tabla)
			->with('records', $modulos_asignados)
			->with('entidad', $entidad)
			->with('privilegio', $privilegio)
			->with('renderactive', $renderactive);
		}
	}
	
	public function show(){
		return redirect()->route('perfiles.index');
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
			$modulo_asignado = Modulo_Asignado::find($id);
			$modulo_asignado->delete();
			return redirect()->route('perfiles.index');
		}
	}
	
	public function create()
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = Modulo_AsignadoController::validador();
		if ($privilegio->mas_add == 0){
			return redirect()->route('logout');
		}
		else{
			$this->apl_nombre = 	aplicacion::where('apl_activo', '=', '1')
										->orderBy('apl_orden', 'ASC')
										->lists('apl_nombre', 'apl_codigo');
			$this->apl_nombre = util::array_indice($this->apl_nombre, -1);
			$this->mod_nombre = util::array_indice($this->mod_nombre, -1);
			$this->rol_nombre = 	rol::where('rol_activo', '=', '1')
										->orderBy('rol_orden', 'ASC')
										->lists('rol_nombre', 'rol_codigo');
			$this->rol_nombre = util::array_indice($this->rol_nombre, -1);
			$tabla = Modulo_AsignadoController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'perfiles', 'pk' => 'mas_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			return view('mantenedor.add')
						->with('menu', $menu)
						->with('title', 'Ingresar Perfiles')
						->with('validate', $validate)
						->with('tablas', $tabla)
						->with('entidad', $entidad);
		}
	}
	
	public function store()
	{
		$input = Input::all();
		$modulo_asignado = new modulo_asignado;
		$modulo = new modulo;
		$modulo = Modulo::find($input['mod_nombre']);
		
		$modulo_asignado->mod_codigo 		= $input['mod_nombre'];
		$modulo_asignado->rol_codigo 		= $input['rol_nombre'];
		if ($modulo->mod_nombre == '**---**'){
			$modulo_asignado->mas_read		 	= 1;
			$modulo_asignado->mas_add 			= 3;
			$modulo_asignado->mas_edit		 	= 3;
			$modulo_asignado->mas_delete		= 3;
			$modulo_asignado->mas_especial		= 3;
			$modulo_asignado->mas_activo  		= 1;
		}
		else{
			$modulo_asignado->mas_read		 	= isset($input['mas_read']) ? 1 : 0;
			$modulo_asignado->mas_add 			= isset($input['mas_add']) ? 1 : 0;
			$modulo_asignado->mas_edit		 	= isset($input['mas_edit']) ? 1 : 0;
			$modulo_asignado->mas_delete		= isset($input['mas_delete']) ? 1 : 0;
			$modulo_asignado->mas_especial		= isset($input['mas_especial']) ? 1 : 0;
			$modulo_asignado->mas_activo  		= isset($input['mas_activo']) ? 1 : 0;
		}
		$modulo_asignado->save();
		return redirect()->route('perfiles.index');
	}
	
	public function edit($id)
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = Modulo_AsignadoController::validador();
		if ($privilegio->mas_edit == 0){
			return redirect()->route('logout');
		}
		else{
			$this->apl_nombre = 	aplicacion::where('apl_activo', '=', '1')
										->orderBy('apl_orden', 'ASC')
										->lists('apl_nombre', 'apl_codigo');
			$this->apl_nombre = util::array_indice($this->apl_nombre, -1);
			$this->mod_nombre = 	modulo::where('mod_activo', '=', '1')
										->orderBy('mod_orden', 'ASC')
										->lists('mod_nombre', 'mod_codigo');
			$this->mod_nombre = util::array_indice($this->mod_nombre, -1);
			$this->rol_nombre = 	rol::where('rol_activo', '=', '1')
										->orderBy('rol_orden', 'ASC')
										->lists('rol_nombre', 'rol_codigo');
			$this->rol_nombre = util::array_indice($this->rol_nombre, -1);
			$this->apl_codigo = 'apl_codigo';
			$this->mod_codigo = 'mod_codigo';
			$this->rol_codigo = 'rol_codigo';
			$tabla = Modulo_AsignadoController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'perfiles', 'pk' => 'mas_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			$record = Modulo_Asignado::join('roles', 'roles.rol_codigo', '=', 'modulos_asignados.rol_codigo')
											->join('modulos', 'modulos.mod_codigo', '=', 'modulos_asignados.mod_codigo')
											->join('aplicaciones', 'aplicaciones.apl_codigo', '=', 'modulos.apl_codigo')
											->find($id);
			return view('mantenedor.edit')
						->with('record',$record)
						->with('menu', $menu)
						->with('validate', $validate)
						->with('entidad', $entidad)
						->with('tablas', $tabla)
						->with('title', 'Ingresar Perfiles');
		}
	}
	
	public function update($id)
	{
		// validate
		// read more on validation at http://laravel.com/docs/validation
	
		// store
		$modulo_asignado = new modulo_asignado;
		$modulo_asignado = Modulo_Asignado::find($id);
		$input = Input::all();
		$modulo_asignado->mas_read		 	= isset($input['mas_read']) ? 1 : 0;
		$modulo_asignado->mas_add 			= isset($input['mas_add']) ? 1 : 0;
		$modulo_asignado->mas_edit		 	= isset($input['mas_edit']) ? 1 : 0;
		$modulo_asignado->mas_delete		= isset($input['mas_delete']) ? 1 : 0;
		$modulo_asignado->mas_activo  		= isset($input['mas_activo']) ? 1 : 0;
		$modulo_asignado->mas_especial 		= isset($input['mas_especial']) ? 1 : 0;
		$modulo_asignado->save();
		return redirect()->route('perfiles.index');
	}
	
	public function getModulo(Request $request, $apl_codigo, $rol_codigo){
		if ($request->ajax()){
			$datos = rol::join('modulos_asignados', 'roles.rol_codigo', '=', 'modulos_asignados.rol_codigo')
				->where('roles.rol_codigo', '=', $rol_codigo)
				->select('modulos_asignados.mod_codigo')
				->get();
			$records = Modulo::where('apl_codigo', '=', $apl_codigo)
				->wherenotin('mod_codigo', $datos)
				->select('mod_codigo', 'mod_nombre')
				->get();
			return response()->json($records);
		}
	}
	
	public function arreglo(){
		$tabla[] = array(	'nombre' 		=> 'Roles',
							'campo'			=> 'rol_nombre',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Roles',
							'value'			=> $this->rol_codigo,
							'tipo'			=> 'select',
							'select'		=> $this->rol_nombre,
							'filter'		=> 1,
							'enable'		=> false);
		$tabla[] = array(	'nombre' 		=> 'Aplicacion',
							'campo'			=> 'apl_nombre',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Aplicaci&oacute;n',
							'value'			=> $this->apl_codigo,
							'tipo'			=> 'select',
							'select'		=> $this->apl_nombre,
							'filter'		=> 1,
							'enable'		=> false);
		$tabla[] = array(	'nombre' 		=> 'Modulo',
							'campo'			=> 'mod_nombre',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Modulos',
							'value'			=> $this->mod_codigo,
							'tipo'			=> 'select',
							'select'		=> $this->mod_nombre,
							'filter'		=> 1,
							'enable'		=> false);
		$tabla[] = array(	'nombre' 		=> 'Leer',
							'campo'			=> 'mas_read',
							'clase' 		=> 'container col-md-1',
							'validate'		=> '',
							'descripcion'	=> 'Leer',
							'value'			=> '',
							'tipo'			=> 'check',
							'select'		=> 0,
							'filter'		=> 0,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Agregar',
							'campo'			=> 'mas_add',
							'clase' 		=> 'container col-md-1',
							'validate'		=> '',
							'descripcion'	=> 'Activo',
							'value'			=> '',
							'tipo'			=> 'check',
							'select'		=> 0,
							'filter'		=> 0,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Editar',
							'campo'			=> 'mas_edit',
							'clase' 		=> 'container col-md-1',
							'validate'		=> '',
							'descripcion'	=> 'Activo',
							'value'			=> '',
							'tipo'			=> 'check',
							'select'		=> 0,
							'filter'		=> 0,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Eliminar',
							'campo'			=> 'mas_delete',
							'clase' 		=> 'container col-md-1',
							'validate'		=> '',
							'descripcion'	=> 'Borrar',
							'value'			=> '',
							'tipo'			=> 'check',
							'select'		=> 0,
							'filter'		=> 0,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Especial',
							'campo'			=> 'mas_especial',
							'clase' 		=> 'container col-md-1',
							'validate'		=> '',
							'descripcion'	=> 'Especial',
							'value'			=> '',
							'tipo'			=> 'check',
							'select'		=> 0,
							'filter'		=> 3,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Estado',
							'campo'			=> 'mas_activo',
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
							'rol_nombre'			:	{required: true, min:1},
							'apl_nombre'			:	{required: true, min:1},
							'mod_nombre'			:	{required: true, min:1}
						},
	  					messages: {
							'rol_nombre'			: { min: 'Seleccione roles' },
							'apl_nombre'			: { min: 'Seleccione aplicaciones' },
							'mod_nombre'			: { min: 'Seleccione modulo' }
	},
					});
	
				});";
		return $validate;
	}

}
