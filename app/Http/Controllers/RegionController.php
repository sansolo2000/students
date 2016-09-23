<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
Use App\models\region;
Use App\models\aplicacion;
use App\helpers\util;
use App\helpers\navegador;
use View;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use Session;

class RegionController extends Controller
{
	public $reg_nombre;
	public $Privilegio_modulo = 'Regiones';
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
				$this->reg_nombre = $_POST['reg_nombre'];
				Session::put('search.region', array('reg_nombre' => $this->reg_nombre));
			}
			else {
				if (Session::has('search.region')){
					$exist = 1;
					$search = Session::get('search.region');
					$this->reg_nombre = $search['reg_nombre'];
				}
			}
				
			$tabla = RegionController::arreglo();

			if ($exist == 0){
				$regiones = Region::orderBy('reg_orden', 'ASC')
				->paginate($this->paginate);
			}
			else{
				$regiones = Region::select()
				->orderBy('reg_orden', 'ASC');

				if ($this->reg_nombre != ''){
					$regiones = $regiones->where('regiones.reg_nombre', 'LIKE', '%'.$this->reg_nombre.'%');
				}

				$regiones = $regiones->paginate($this->paginate);
			}
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => '/'.util::obtener_url().'regiones', 'pk' => 'reg_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'col' => 4);
			return view('mantenedor.index')
						->with('menu', $menu)
						->with('tablas', $tabla)
						->with('records', $regiones)
						->with('entidad', $entidad)
						->with('privilegio', $privilegio);
		}
	}
	
	public function show(){
		return redirect()->route('regiones.index');
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
			$region = Region::find($id);
			$region->delete();
			return redirect()->route('regiones.index');
		}
	}
	
	public function create()
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = RegionController::validador();
		if ($privilegio->mas_add == 0){
			return redirect()->route('logout');
		}
		else{
			$tabla = RegionController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'regiones', 'pk' => 'reg_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			return view('mantenedor.add')
						->with('menu', $menu)
						->with('validate', $validate)
						->with('title', 'Ingresar Regiones')
						->with('tablas', $tabla)
						->with('entidad', $entidad);
		}
	}

	public function store()
	{
		$region = new region;
		$input = Input::all();
		$region->reg_nombre 		= $input['reg_nombre'];
		$region->reg_orden  		= $input['reg_orden'];
		$region->reg_activo  		= isset($input['reg_activo']) ? 1 : 0;
		$region->save();
		return redirect()->route('regiones.index');
	}

	public function edit($id)
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = RegionController::validador();
		if ($privilegio->mas_edit == 0){
			return redirect()->route('logout');
		}
		else{
			$tabla = RegionController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'regiones', 'pk' => 'reg_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			$record = Region::find($id);
			return view('mantenedor.edit')
						->with('record',$record)
						->with('menu', $menu)
						->with('validate', $validate)
						->with('entidad', $entidad)
						->with('tablas', $tabla)
						->with('title', 'Ingresar Regiones');
		}
	}

	public function update($id)
	{
		// validate
		// read more on validation at http://laravel.com/docs/validation

		// store
		$region = new region;
		$region = Region::find($id);
		$input = Input::all();
		$region->reg_nombre = $input['reg_nombre'];
		$region->reg_orden  = $input['reg_orden'];
		$region->reg_activo  = isset($input['reg_activo']) ? 1 : 0;
		$region->save();
		return redirect()->route('regiones.index');
	}

	public function arreglo(){
		$tabla[] = array(	'nombre' 		=> 'Nombre',
							'campo'			=> 'reg_nombre',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Nombre',
							'tipo'			=> 'input',
							'select'		=> 0,
							'value'			=> $this->reg_nombre,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Orden',
							'campo'			=> 'reg_orden',
							'clase' 		=> 'container col-md-3',
							'validate'		=> '',
							'descripcion'	=> 'Orden',
							'value'			=> '',
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 0,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Estado',
							'campo'			=> 'reg_activo',
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
						'reg_nombre'			:	{required: true, minlength: 5, maxlength: 50},
						'reg_orden'				:	{required: true, number: true}
					},
				});
	
			});";
		return $validate;
	}
		
}
	
	