<?php

namespace App\Http\Controllers;

//use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request;
use Auth;
Use App\models\colegio;
Use App\models\comuna;
Use App\models\region;
use App\helpers\util;
use App\helpers\navegador;
use View;
use Illuminate\Support\Facades\Input;
//use App\Http\Requests;
use Session;


class ColegioController extends Controller
{
	public $com_codigo;
	public $com_nombre;
	public $reg_codigo;
	public $reg_nombre;
	public $col_nombre;
	public $col_email;
	public $col_direccion;
	public $Privilegio_modulo = 'Colegios';
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
				$this->col_nombre 		= $_POST['col_nombre'];
				$this->col_email 		= $_POST['col_email'];
				$this->reg_codigo 		= $_POST['reg_nombre'];
				$this->com_codigo 		= $_POST['com_nombre'];
				$this->col_direccion 	= $_POST['col_direccion'];
				Session::put('search.colegio', array(	'col_nombre' 	=> $this->col_nombre,
														'col_email' 	=> $this->col_email,
														'reg_codigo' 	=> $this->reg_codigo,
														'com_codigo' 	=> $this->com_codigo,
														'col_direccion' => $this->col_direccion));
			}
			else{
				if (Session::has('search.colegio')){
					$exist = 1;
					$search = Session::get('search.colegio');
					$this->col_nombre = $search['col_nombre'];
					$this->col_email = $search['col_email'];
					$this->reg_codigo = $search['reg_codigo'];
					$this->com_codigo = $search['com_codigo'];
					$this->col_direccion = $search['col_direccion'];
				}
			}
				
			$tabla = ColegioController::arreglo();

			if ($exist == 0){
				$colegios = Colegio::join('comunas', 'colegios.com_codigo', '=', 'comunas.com_codigo')
									->join('regiones', 'regiones.reg_codigo', '=', 'comunas.reg_codigo')
									->orderBy('colegios.col_activo', 'DESC')
									->paginate($this->paginate);
			}
			else{
				$colegios = Colegio::select()
				->join('comunas', 'colegios.com_codigo', '=', 'comunas.com_codigo')
				->join('regiones', 'regiones.reg_codigo', '=', 'comunas.reg_codigo')
				->orderBy('colegios.col_activo', 'DESC');

				if ($this->col_nombre != ''){
					$colegios = $colegios->where('colegios.col_nombre', 'LIKE', '%'.$this->col_nombre.'%');
				}
				if ($this->col_email != ''){
					$colegios = $colegios->where('colegios.col_email', 'LIKE', '%'.$this->col_email.'%');
				}
				if ($this->col_direccion != ''){
					$colegios = $colegios->where('colegios.col_direccion', 'LIKE', '%'.$this->col_direccion.'%');
				}
				if ($this->reg_codigo != ''){
					$colegios = $colegios->where('regiones.reg_nombre', 'LIKE', '%'.$this->reg_codigo.'%');
				}
				if ($this->com_codigo != ''){
					$colegios = $colegios->where('comunas.com_nombre', 'LIKE', '%'.$this->com_codigo.'%');
				}

				$colegios = $colegios->paginate($this->paginate);
			}
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => '/'.util::obtener_url().'colegios', 'pk' => 'col_codigo', 'clase' => 'container col-md-12', 'col' => 8);
			return view('mantenedor.index')
						->with('menu', $menu)
						->with('tablas', $tabla)
						->with('records', $colegios)
						->with('entidad', $entidad)
						->with('privilegio', $privilegio);
		}
	}

	public function show(){
		return redirect()->route('colegios.index');
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
			$colegio = Colegio::find($id);
			$colegio->delete();
			return redirect()->route('colegios.index');
		}
	}

	public function create()
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = ColegioController::validador('create');
		if ($privilegio->mas_add == 0){
			return redirect()->route('logout');
		}
		else{
			$this->reg_nombre = 	Region::where('reg_activo', '=', '1')
									->orderBy('reg_orden', 'ASC')
									->lists('reg_nombre', 'reg_codigo');
			$this->reg_nombre = util::array_indice($this->reg_nombre, -1);
			$this->com_nombre = util::array_indice($this->com_nombre, -1);
			$tabla = ColegioController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'colegios', 'pk' => 'mod_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			return view('mantenedor.add')
				->with('menu', $menu)
				->with('title', 'Ingresar Colegio')
				->with('validate', $validate)
				->with('tablas', $tabla)
				->with('entidad', $entidad);
		}
	}

	public function store()
	{
		$input = Input::all();
		$mime = Input::file('col_logo');
		$mime = Input::file('col_logo')->getMimeType();
		$extension = strtolower(Input::file('col_logo')->getClientOriginalExtension());
		$fileName = uniqid().'.'.$extension;
		$path = "files_uploaded/logo";
		
		switch ($mime)
		{
			case "image/jpeg":
			case "image/png":
			case "image/gif":
				if (Request::file('col_logo')->isValid())
	            {
					$activo = isset($input['col_activo']) ? true : false;
					if($activo){
						Colegio::where('col_activo', '=', 1)->update(['col_activo' => 0]);
					}
	            	Request::file('col_logo')->move($path, $fileName);
					$colegio = new colegio;
					$input = Input::all();
					$colegio->col_nombre 		= $input['col_nombre'];
					$colegio->col_email 		= $input['col_email'];
					$colegio->col_direccion 	= $input['col_direccion'];
					$colegio->com_codigo 		= $input['com_nombre'];
					$colegio->col_activo  		= isset($input['col_activo']) ? 1 : 0;
					$colegio->col_logo			= $fileName;
					$colegio->save();
	            }
            default:
				return redirect()->route('colegios.index');
		}
		return redirect()->route('colegios.index');
	}

	public function edit($id)
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = ColegioController::validador('edit');
		if ($privilegio->mas_edit == 0){
			return redirect()->route('logout');
		}
		else{
			$record = Colegio::join('comunas', 'colegios.com_codigo', '=', 'comunas.com_codigo')
								->join('regiones', 'regiones.reg_codigo', '=', 'comunas.reg_codigo')
								->orderBy('colegios.col_activo', 'DESC')
								->find($id);
			$this->reg_nombre = 	Region::where('reg_activo', '=', '1')
									->orderBy('reg_orden', 'ASC')
									->lists('reg_nombre', 'reg_codigo');
			$this->reg_nombre = util::array_indice($this->reg_nombre, -1);
			$this->com_nombre = 	Comuna::where('com_activo', '=', '1')
									->where('reg_codigo', '=', $record['reg_codigo'])
									->orderBy('com_orden', 'ASC')
									->lists('com_nombre', 'com_codigo');
			$this->com_nombre = util::array_indice($this->com_nombre, -1);
			$this->reg_codigo = 'reg_codigo';
			$this->com_codigo = 'com_codigo';
			$tabla = ColegioController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'colegios', 'pk' => 'col_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');

			return view('mantenedor.edit')
					->with('record',$record)
					->with('menu', $menu)
					->with('entidad', $entidad)
					->with('validate', $validate)
					->with('tablas', $tabla)
					->with('title', 'Ingresar '.$this->Privilegio_modulo);
		}
	}

	public function update($id)
	{
		$input = Input::all();
		$mime = Input::file('col_logo');
		if (!isset($mime)){
			$activo = isset($input['col_activo']) ? true : false;
			if($activo){
				Colegio::where('col_activo', '=', 1)->update(['col_activo' => 0]);
			}
			$colegio = new colegio;
			$colegio = Colegio::find($id);
			$input = Input::all();
			$colegio->col_nombre 		= $input['col_nombre'];
			$colegio->col_email 		= $input['col_email'];
			$colegio->col_direccion 	= $input['col_direccion'];
			$colegio->com_codigo 		= $input['com_nombre'];
			$colegio->col_activo  		= isset($input['col_activo']) ? 1 : 0;
			$colegio->save();
		}
		else{
			$mime = Input::file('col_logo')->getMimeType();
			$extension = strtolower(Input::file('col_logo')->getClientOriginalExtension());
			$fileName = uniqid().'.'.$extension;
			$path = "files_uploaded/logo";
			
			switch ($mime)
			{
				case "image/jpeg":
				case "image/png":
				case "image/gif":
					if (Request::file('col_logo')->isValid())
					{
						Colegio::where('col_activo', '=', 1)->update(['col_activo' => 0]);
						$colegio = new colegio;
						$colegio = Colegio::find($id);
						Request::file('col_logo')->move($path, $fileName);
						$input = Input::all();
						$colegio->col_nombre 		= $input['col_nombre'];
						$colegio->col_email 		= $input['col_email'];
						$colegio->col_direccion 	= $input['col_direccion'];
						$colegio->com_codigo 		= $input['com_nombre'];
						$colegio->col_activo  		= isset($input['col_activo']) ? 1 : 0;
						$colegio->col_logo			= $fileName;
						$colegio->save();
					}
				default:
					return redirect()->route('colegios.index');
			}
		}
		return redirect()->route('colegios.index');
	}

	public function arreglo(){
		$tabla[] = array(	'nombre' 		=> 'Nombre',
							'campo'			=> 'col_nombre',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Nombre',
							'tipo'			=> 'input',
							'select'		=> 0,
							'value'			=> $this->col_nombre,
							'filter'		=> 1,
							'enable'		=> 1);
		$tabla[] = array(	'nombre' 		=> 'E-Mail',
							'campo'			=> 'col_email',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'E-Mail',
							'tipo'			=> 'input',
							'select'		=> 0,
							'value'			=> $this->col_email,
							'filter'		=> 1,
							'enable'		=> 1);
		$tabla[] = array(	'nombre' 		=> 'Direccion',
							'campo'			=> 'col_direccion',
							'clase' 		=> 'container col-md-7',
							'validate'		=> '',
							'descripcion'	=> 'Direcci&oacute;n',
							'tipo'			=> 'input',
							'select'		=> 0,
							'value'			=> $this->col_direccion,
							'filter'		=> 1,
							'enable'		=> 1);
		$tabla[] = array(	'nombre' 		=> 'Region',
							'campo'			=> 'reg_nombre',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Regi&oacute;n',
							'value'			=> $this->reg_codigo,
							'tipo'			=> 'select',
							'select'		=> $this->reg_nombre,
							'filter'		=> 1,
							'enable'		=> 1);
		$tabla[] = array(	'nombre' 		=> 'Comuna',
							'campo'			=> 'com_nombre',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Comuna',
							'value'			=> $this->com_codigo,
							'tipo'			=> 'select',
							'select'		=> $this->com_nombre,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Estado',
							'campo'			=> 'col_activo',
							'clase' 		=> 'container col-md-1',
							'validate'		=> '',
							'descripcion'	=> 'Activo',
							'value'			=> '',
							'tipo'			=> 'check',
							'select'		=> 0,
							'filter'		=> 0,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Archivo',
							'campo'			=> 'col_logo',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Archivo',
							'tipo'			=> 'file',
							'select'		=> 0,
							'value'			=> 0,
							'filter'		=> 0,
							'enable'		=> false);
		return $tabla;
	}
	
	public function validador($form){
		if ($form == 'create'){
			$validate = "
					$().ready(function () {
						$('#myform').validate({
							rules: {
								'col_nombre'			:	{required: true, minlength: 5, maxlength: 50},
								'col_email'				:	{required: true, email: true,  minlength: 2, maxlength: 50},
								'col_direccion'			:	{required: true, minlength: 2, maxlength: 50},
								'reg_nombre'			:	{required: true, min:1},
								'com_nombre'			:	{required: true, min:1},
								'col_logo'				:	{required: true, extension: 'jpg|png'}
							},
		  					messages: {
								'reg_nombre'			: { min: 'Seleccione region' },
					
								'com_nombre'			: { min: 'Seleccione comuna' }
							},
						});
		
					});";
		}
		if ($form == 'edit'){
			$validate = "
					$().ready(function () {
						$('#myform').validate({
							rules: {
								'col_nombre'			:	{required: true, minlength: 5, maxlength: 50},
								'col_email'				:	{required: true, email: true,  minlength: 2, maxlength: 50},
								'col_direccion'			:	{required: true, minlength: 2, maxlength: 50},
								'reg_nombre'			:	{required: true, min:1},
								'com_nombre'			:	{required: true, min:1},
							},
		  					messages: {
								'reg_nombre'			: { min: 'Seleccione region' },
			
								'com_nombre'			: { min: 'Seleccione comuna' }
							},
						});
		
					});";
		}
		return $validate;
	}
	
}
