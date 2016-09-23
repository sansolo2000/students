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

class AplicacionController extends Controller
{
	public $apl_nombre;
	public $Privilegio_modulo = 'Aplicaciones';
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
				$this->apl_nombre = $_POST['apl_nombre'];
				Session::put('search.aplicacion', array('apl_nombre' => $this->apl_nombre));
			}
			else {
				if (Session::has('search.aplicacion')){
					$exist = 1;
					$search = Session::get('search.aplicacion');
					$this->apl_nombre = $search['apl_nombre'];
				}
			}			
			$tabla = AplicacionController::arreglo();
		
			if ($exist == 0){
				$aplicaciones = Aplicacion::orderBy('apl_orden', 'ASC')
										->paginate($this->paginate);
			}
			else{
				$aplicaciones = Aplicacion::select()
							->orderBy('apl_orden', 'ASC');
				
				if ($this->apl_nombre != ''){
					$aplicaciones = $aplicaciones->where('apl_nombre', 'LIKE', '%'.$this->apl_nombre.'%');
				}
				$aplicaciones = $aplicaciones->paginate($this->paginate);
			}
			$entidad = array('Nombre' => 'Apliaciones', 'controller' => '/'.util::obtener_url().'aplicaciones', 'pk' => 'apl_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'col' => 4);
			return view('mantenedor.index')
			->with('menu', $menu)
			->with('tablas', $tabla)
			->with('records', $aplicaciones)
			->with('entidad', $entidad)
			->with('privilegio', $privilegio);
		}
	}
	
	public function show(){
		return redirect()->route('aplicaciones.index');
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
			$aplicacion = Aplicacion::find($id);
			$aplicacion->delete();
			return redirect()->route('aplicaciones.index');
		}
	}
	
	public function create()
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = AplicacionController::validador();
		if ($privilegio->mas_add == 0){
			return redirect()->route('logout');
		}
		else{
			$tabla = AplicacionController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'aplicaciones', 'pk' => 'apl_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			return view('mantenedor.add')
					->with('menu', $menu)
					->with('validate', $validate)
					->with('title', 'Ingresar Aplicaciones')
					->with('tablas', $tabla)
					->with('entidad', $entidad);
		}
	}
	
	public function store()
	{
		$aplicacion = new aplicacion;
		$input = Input::all();
		$aplicacion->apl_nombre 		= $input['apl_nombre'];
		$aplicacion->apl_orden  		= $input['apl_orden'];
		$aplicacion->apl_activo  		= isset($input['apl_activo']) ? 1 : 0;
		$aplicacion->save();
		return redirect()->route('aplicaciones.index');
	}
	
	public function edit($id)
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = AplicacionController::validador();
		if ($privilegio->mas_edit == 0){
			return redirect()->route('logout');
		}
		else{
			$tabla = AplicacionController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'aplicaciones', 'pk' => 'apl_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			$record = Aplicacion::find($id);
			return view('mantenedor.edit')
					->with('record',$record)
					->with('menu', $menu)
					->with('validate', $validate)
					->with('entidad', $entidad)
					->with('tablas', $tabla)
					->with('title', 'Ingresar Aplicaciones');
		}
	}
	
	public function update($id)
	{
		// validate
		// read more on validation at http://laravel.com/docs/validation
	
		// store
		$aplicacion = new Aplicacion;
		$aplicacion = Aplicacion::find($id);
		$input = Input::all();
		$aplicacion->apl_nombre = $input['apl_nombre'];
		$aplicacion->apl_orden  = $input['apl_orden'];
		$aplicacion->apl_activo  = isset($input['apl_activo']) ? 1 : 0;
		$aplicacion->save();
		return redirect()->route('aplicaciones.index');
	}
	
	public function arreglo(){
		$tabla[] = array(	'nombre' 		=> 'Nombre',
							'campo'			=> 'apl_nombre',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Nombre',
							'tipo'			=> 'input',
							'select'		=> 0,
							'value'			=> $this->apl_nombre,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Orden',
							'campo'			=> 'apl_orden',
							'clase' 		=> 'container col-md-3',
							'validate'		=> '',
							'descripcion'	=> 'Orden',
							'value'			=> '',
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 0,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Estado',
							'campo'			=> 'apl_activo',
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
	
							'apl_nombre'		:	{required: true, minlength: 5, maxlength: 50},
							'apl_orden'			:	{required: true, number: true},
							}
					});
	
				});";
		return $validate;
	}
	
}
