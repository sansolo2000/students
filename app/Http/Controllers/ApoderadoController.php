<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\models\persona;
use App\models\rol;
use App\models\asignacion;

use App\models\administrador;
use App\models\alumno;
use App\models\curso;
use App\models\profesor;
use App\models\apoderado;

use App\helpers\util;
use App\helpers\navegador;
use View;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use Illuminate\Support\Facades\Hash;
use Session;
use DB;
use Maatwebsite\Excel\Facades\Excel;


class ApoderadoController extends Controller
{

	public $alu_numero;
	public $alu_rut;
	public $alu_dv;
	public $alu_nombre;
	public $alu_apellido_paterno;
	public $apo_rut;
	public $apo_dv;
	public $apo_nombre;
	public $apo_apellido_paterno;
	public $apo_email;
	
	
	public $apo_fono; 
	
	public $remenber_token;
	public $errores;
	public $Privilegio_modulo = 'Apoderados';
	public $paginate = 10;

	public function index($id = NULL)
	{
		// Menu
	
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
	
		//Privilegios
	
		$errores = '';
		if (Session::has('search.apoderado_errores')){
			$search = Session::get('search.apoderado_errores');
			$this->errores = $search['errores'];
			Session::forget('search.apoderado_errores');
		}
	
	
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$this->cur_codigo = -1;
		if ($privilegio->mas_read == 0){
			return redirect()->route('logout');
		}
		else{
			// Descripcion de tabla.
			$exist = 0;
			if (!empty($_POST)){
				if (!(empty($_POST['alu_numero']) && empty($_POST['alu_rutbak'])&& empty($_POST['apo_rutbak']) 
						&& empty($_POST['per_nombre']) && empty($_POST['per_apellido_paterno']) 
						&& empty($_POST['per_apellido_materno']) && empty($_POST['per_email']))){
							$this->alu_numero 			= $_POST['alu_numero'];
							$this->alu_rut 				= $_POST['alu_rutbak'];
							$this->alu_nombre 			= $_POST['alu_nombre'];
							$this->alu_apellido_paterno = $_POST['alu_apellido_paterno'];
							$this->apo_rut 				= $_POST['apo_rutbak'];
							$this->apo_nombre 			= $_POST['apo_nombre'];
							$this->apo_apellido_paterno = $_POST['apo_apellido_paterno'];
							$this->apo_email 			= $_POST['apo_email'];

							Session::put('search.apoderado_persona', array(
									'alu_numero' 			=> $this->alu_numero,
									'alu_rutbak' 			=> $this->per_rut,
									'alu_nombre' 			=> $this->per_nombre,
									'alu_apellido_paterno' 	=> $this->per_apellido_paterno,
									'apo_rutbak' 			=> $this->apo_rutbak,
									'apo_nombre' 			=> $this->apo_nombre,
									'apo_apellido_paterno' 	=> $this->apo_apellido_paterno,
									'apo_email' 			=> $this->apo_email));
							if (Session::has('search.apoderado_curso')){
								$exist = 1;
								$search = Session::get('search.apoderado_curso');
								$this->cur_codigo = $search['cur_codigo'];
							}
							$exist = 2;
				}
				else {
					if (Session::has('search.apoderado_persona')){
						$exist = 2;
						$search = Session::get('search.apoderado_persona');
							$this->alu_numero 			= $_POST['alu_numero'];
							$this->alu_rut 				= $_POST['alu_rutbak'];
							$this->alu_nombre 			= $_POST['alu_nombre'];
							$this->alu_apellido_paterno = $_POST['alu_apellido_paterno'];
							$this->apo_rut 				= $_POST['apo_rutbak'];
							$this->apo_nombre 			= $_POST['apo_nombre'];
							$this->apo_apellido_paterno = $_POST['apo_apellido_paterno'];
							$this->apo_email 			= $_POST['apo_email'];
					}
					if (Session::has('search.apoderado_curso')){
						$exist = 1;
						$search = Session::get('search.apoderado_curso');
						$this->cur_codigo				= $search['cur_codigo'];
					}
				}
				if (!empty($_POST['cur_nombre'])){
					$this->cur_codigo = $_POST['cur_nombre'];
					Session::put('search.apoderado_curso', array(
							'cur_codigo' 				=> $this->cur_codigo));
					$exist = 1;
					if (Session::has('search.apoderado_persona')){
						$exist = 2;
						$search = Session::get('search.apoderado_persona');
							$this->alu_numero 			= $_POST['alu_numero'];
							$this->alu_rut 				= $_POST['alu_rutbak'];
							$this->alu_nombre 			= $_POST['alu_nombre'];
							$this->alu_apellido_paterno = $_POST['alu_apellido_paterno'];
							$this->apo_rut 				= $_POST['apo_rutbak'];
							$this->apo_nombre 			= $_POST['apo_nombre'];
							$this->apo_apellido_paterno = $_POST['apo_apellido_paterno'];
							$this->apo_email 			= $_POST['apo_email'];
					}
				}
			}
			else{
				if (Session::has('search.apoderado_persona')){
					$exist = 2;
					$search = Session::get('search.apoderado_persona');
							$this->alu_numero 			= $_POST['alu_numero'];
							$this->alu_rut 				= $_POST['alu_rutbak'];
							$this->alu_nombre 			= $_POST['alu_nombre'];
							$this->alu_apellido_paterno = $_POST['alu_apellido_paterno'];
							$this->apo_rut 				= $_POST['apo_rutbak'];
							$this->apo_nombre 			= $_POST['apo_nombre'];
							$this->apo_apellido_paterno = $_POST['apo_apellido_paterno'];
							$this->apo_email 			= $_POST['apo_email'];
				}
				if (Session::has('search.apoderado_curso')){
					$exist = 1;
					$search = Session::get('search.apoderado_curso');
					$this->cur_codigo			= $search['cur_codigo'];
				}
			}
	
			$tabla = ApoderadoController::arreglo();
			if ($exist == 0){
				$personas = DB::table('alumnos as al')
				->join('personas AS pa', DB::raw('pa.per_rut'), '=', DB::raw('al.per_rut'))
				->join('cursos AS cu', DB::raw('al.cur_codigo'), '=', DB::raw('cu.cur_codigo'))
				->leftjoin('apoderados_alumnos AS aa', DB::raw('al.alu_codigo'), '=', DB::raw('aa.alu_codigo'))
				->leftjoin('apoderados AS ap', DB::raw('aa.apo_codigo'), '=', DB::raw('ap.apo_codigo'))
				->leftjoin('personas AS pe', DB::raw('ap.per_rut'), '=', DB::raw('pe.per_rut'))
				->where('al.cur_codigo', '=', -1)
				->orderBy('al.alu_numero', 'ASC')
				->orderBy('pa.per_apellido_paterno', 'ASC')
				->select('al.alu_numero AS alu_numero', 'pa.per_rut AS alu_rut', 'pa.per_dv AS alu_dv', 'pa.per_nombre AS alu_nombre', 'pa.per_apellido_paterno AS alu_apellido_paterno', 'pe.per_rut AS apo_rut', 'pe.per_dv AS apo_dv', 'pe.per_nombre AS apo_nombre', 'pe.per_apellido_paterno AS apo_apellido_paterno', 'pe.per_email AS apo_email')
				->paginate($this->paginate);
	
			}
			if ($exist == 1){
				$personas = DB::table('alumnos as al')
								->join('personas AS pa', DB::raw('pa.per_rut'), '=', DB::raw('al.per_rut'))
								->join('cursos AS cu', DB::raw('al.cur_codigo'), '=', DB::raw('cu.cur_codigo'))
								->leftjoin('apoderados_alumnos AS aa', DB::raw('al.alu_codigo'), '=', DB::raw('aa.alu_codigo'))
								->leftjoin('apoderados AS ap', DB::raw('aa.apo_codigo'), '=', DB::raw('ap.apo_codigo'))
								->leftjoin('personas AS pe', DB::raw('ap.per_rut'), '=', DB::raw('pe.per_rut'))
								->where('al.cur_codigo', '=', $this->cur_codigo)
								->orderBy('al.alu_numero', 'ASC')
								->orderBy('pa.per_apellido_paterno', 'ASC')
								->select('al.alu_numero', 'pa.per_rut AS alu_rut', 'pa.per_dv AS alu_dv', 'pa.per_nombre AS alu_nombre', 'pa.per_apellido_paterno AS alu_apellido_paterno', 'pe.per_rut AS apo_rut', 'pe.per_dv AS apo_dv', 'pe.per_nombre AS apo_nombre', 'pe.per_apellido_paterno AS apo_apellido_paterno', 'pe.per_email AS apo_email')
								->paginate($this->paginate);
//				$personas = $personas->toArray();
			}
			if ($exist == 2){
				$personas = DB::table('alumnos as al') 
								->join('personas AS pa', DB::raw('pa.per_rut'), '=', DB::raw('al.per_rut'))
								->join('cursos AS cu', DB::raw('al.cur_codigo'), '=', DB::raw('cu.cur_codigo'))
								->leftjoin('apoderados_alumnos AS aa', DB::raw('al.alu_codigo'), '=', DB::raw('aa.alu_codigo'))
								->leftjoin('apoderados AS ap', DB::raw('aa.apo_codigo'), '=', DB::raw('ap.apo_codigo'))
								->leftjoin('personas AS pe', DB::raw('ap.per_rut'), '=', DB::raw('pe.per_rut'))
								->where('al.cur_codigo', '=', $this->cur_codigo)
								->orderBy('al.alu_numero', 'ASC')
								->orderBy('pa.per_apellido_paterno', 'ASC')
								->select('al.alu_numero AS alu_numero', 'pa.per_rut AS alu_rut', 'pa.per_dv AS alu_dv', 'pa.per_nombre AS alu_nombre', 'pa.per_apellido_paterno AS alu_apellido_paterno', 'pe.per_rut AS apo_rut', 'pe.per_dv AS apo_dv', 'pe.per_nombre AS apo_nombre', 'pe.per_apellido_paterno AS apo_apellido_paterno', 'pe.per_email AS apo_email');
				
				if ($this->alu_rut != ''){
					$personas = $personas->where('pa.per_rut', 'LIKE', '%'.$this->alu_rut.'%');
				}
				if ($this->alu_nombre != ''){
					$personas = $personas->where('pa.per_nombre', 'LIKE', '%'.$this->alu_nombre.'%');
				}
				if ($this->alu_apellido_paterno != ''){
					$personas = $personas->where('pa.per_apellido_paterno', 'LIKE', '%'.$this->alu_apellido_paterno.'%');
				}
				if ($this->alu_nombre != ''){
					$personas = $personas->where('pa.per_nombre', 'LIKE', '%'.$this->alu_nombre.'%');
				}
				if ($this->alu_apellido_paterno != ''){
					$personas = $personas->where('pa.per_apellido_paterno', 'LIKE', '%'.$this->alu_apellido_paterno.'%');
				}
				if ($this->apo_nombre != ''){
					$personas = $personas->where('pe.per_nombre', 'LIKE', '%'.$this->apo_nombre.'%');
				}
				if ($this->apo_apellido_paterno != ''){
					$personas = $personas->where('pe.per_apellido_paterno', 'LIKE', '%'.$this->apo_apellido_paterno.'%');
				}
				if ($this->apo_email != ''){
					$personas = $personas->where('pe.per_email', 'LIKE', '%'.$this->apo_email.'%');
				}
	
				$personas = $personas->paginate($this->paginate);
			}
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => '/'.util::obtener_url().'apoderados', 'pk' => 'apo_rut', 'clase' => 'container col-md-12', 'col' => 9);
			return view('mantenedor.index_apoderado')
						->with('menu', $menu)
						->with('tablas', $tabla)
						->with('cur_codigo', $this->cur_codigo)
						->with('records', $personas)
						->with('errores', $this->errores)
						->with('entidad', $entidad)
						->with('privilegio', $privilegio);
		}
	}
	
	
	
	public function show(){
		return redirect()->route('apoderados.index');
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
			$profesor = Profesor::where('per_rut', '=', $id);
			$profesor->delete();
			$asignacion = Asignacion::where('per_rut', '=', $id);
			$asignacion->delete();
			$persona = Persona::find($id);
			$persona->delete();
			return redirect()->route('profesores.index');
		}
	}

	public function create()
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = ProfesorController::validador();
		if ($privilegio->mas_add == 0){
			return redirect()->route('logout');
		}
		else{
			$tabla = ProfesorController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'profesores', 'pk' => 'per_rut', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			return view('mantenedor.add')
			->with('menu', $menu)
			->with('title', 'Ingresar Profesor')
			->with('validate', $validate)
			->with('tablas', $tabla)
			->with('entidad', $entidad);
		}
	}

	public function store()
	{
		$input = Input::all();
		$persona = new persona;
		$rut = util::format_rut($input['per_rut']);

		$cantidad = Persona::where('per_rut', '=', $rut['numero'])->count();
		if ($cantidad == 0){
			$persona->per_rut = $rut['numero'];
			$persona->per_dv = $rut['dv'];
			$persona->per_nombre = $input['per_nombre'];
			$persona->per_nombre_segundo = $input['per_nombre_segundo'];
			$persona->per_apellido_paterno = $input['per_apellido_paterno'];
			$persona->per_apellido_materno = $input['per_apellido_materno'];
			$persona->per_password = Hash::make($input['per_password']);
			$persona->per_email = $input['per_email'];
			$persona->save();
		}

		$rol = new rol;
		$rol = Rol::where('rol_nombre', '=', 'Profesor')->first();
		$asignacion = new asignacion;
		$asignacion->rol_codigo = $rol->rol_codigo;
		$asignacion->per_rut = $rut['numero'];
		$asignacion->save();

		$profesor = new profesor;
		$profesor->per_rut 		= $rut['numero'];
		$profesor->pro_activo  	= isset($input['pro_activo']) ? 1 : 0;
		$profesor->save();


		return redirect()->route('profesores.index');
	}

	public function edit($id)
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = ProfesorController::validador();
		if ($privilegio->mas_edit == 0){
			return redirect()->route('logout');
		}
		else{
			$tabla = ProfesorController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'profesores', 'pk' => 'per_rut', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			$record = Persona::select('personas.per_rut', 'personas.per_dv', 'personas.per_nombre', 'personas.per_apellido_paterno', 'personas.per_apellido_materno', 'personas.per_email', DB::raw('"1" as mod_password'), 'profesores.pro_activo')
			->join('asignaciones', 'asignaciones.per_rut', '=', 'personas.per_rut')
			->join('roles', 'roles.rol_codigo', '=', 'asignaciones.rol_codigo')
			->join('profesores', 'profesores.per_rut', '=', 'personas.per_rut')
			->where('roles.rol_nombre', '=', 'Profesor')
			->find($id);
				
				
			return view('mantenedor.edit')
			->with('record',$record)
			->with('menu', $menu)
			->with('validate', $validate)
			->with('entidad', $entidad)
			->with('tablas', $tabla)
			->with('title', 'Ingresar Profesores');
		}
	}

	public function exportar_apoderados($id)
	{
		$curso = Curso::join('niveles', 'niveles.niv_codigo', '=', 'cursos.niv_codigo')
						->join('profesores', 'profesores.pro_codigo', '=', 'cursos.pro_codigo')
						->join('personas', 'personas.per_rut', '=', 'profesores.per_rut')
						->where('cursos.cur_codigo', '=', $id)
						->select('cursos.cur_codigo', DB::raw('CONCAT(cursos.cur_numero, "-", cursos.cur_letra, "_", niveles.niv_nombre) as name'))
						->first();
		$personas = DB::table('alumnos as al')
						->join('personas AS pa', DB::raw('pa.per_rut'), '=', DB::raw('al.per_rut'))
						->join('cursos AS cu', DB::raw('al.cur_codigo'), '=', DB::raw('cu.cur_codigo'))
						->leftjoin('apoderados_alumnos AS aa', DB::raw('al.alu_codigo'), '=', DB::raw('aa.alu_codigo'))
						->leftjoin('apoderados AS ap', DB::raw('aa.apo_codigo'), '=', DB::raw('ap.apo_codigo'))
						->leftjoin('personas AS pe', DB::raw('ap.per_rut'), '=', DB::raw('pe.per_rut'))
						->where('al.cur_codigo', '=', $id)
						->orderBy('al.alu_numero', 'ASC')
						->orderBy('pa.per_apellido_paterno', 'ASC')
						->select(	'al.alu_numero', 'pa.per_rut AS alu_rut', 'pa.per_dv AS alu_dv', 
									'pa.per_nombre AS alu_nombre', 'pa.per_apellido_paterno AS alu_apellido_paterno', 
									'pe.per_rut AS apo_rut', 'pe.per_dv AS apo_dv', 'pe.per_nombre AS apo_nombre', 
									'pe.per_apellido_paterno AS apo_apellido_paterno', 'pe.per_apellido_materno AS apo_apellido_materno',
									'pe.per_email AS apo_email')
						->get();
		$nombre = $curso->name;
		Excel::create('Curso', function($excel) use($personas) {
			$excel->sheet('Curso', function($sheet) use($personas){
				foreach ($personas as $key => $persona) {
					$rut_alu = util::format_rut($persona->alu_rut, $persona->alu_dv);
					$rut_apo = '';
					if ($persona->apo_rut != ''){
						$rut_apo = util::format_rut($persona->apo_rut, $persona->apo_dv);
						$rut_apo = $rut_apo['numero'].'-'.$rut_apo['dv'];
					}
					$data[] = array(
							'numero'						=> $persona->alu_numero,
							'rut_alumno'					=> $rut_alu['numero'].'-'.$rut_alu['dv'],
							'nombre_alumno' 				=> $persona->alu_nombre,
							'apellido_alumno' 				=> $persona->alu_apellido_paterno,
							'rut_apoderado'					=> $rut_apo,
							'nombre_apoderado' 				=> $persona->apo_nombre,
							'apellido_paterno_apoderado' 	=> $persona->apo_apellido_paterno,
							'apellido_materno_apoderado' 	=> $persona->apo_apellido_materno,
							'e-mail'						=> $persona->apo_email
					);
				}
				$sheet->fromArray($data);
			});
		})->download('xls');
	}

	public function importar_apoderados($id)
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
			$tabla = ApoderadoController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'apoderados', 'pk' => $id, 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			return view('mantenedor.importar_apoderado')
					->with('menu', $menu)
					->with('title', 'Importar Apoderados')
					->with('curso', $curso)
					->with('tablas', $tabla)
					->with('entidad', $entidad);
		}
	}


	public function save_apoderados()
	
	{
		$input = Input::all();
		if(Input::hasFile('import_file')){
			$path = Input::file('import_file')->getRealPath();
			$data = Excel::load($path, function($reader) {
								})->get();
			if (($data[0]->getTitle() == 'numero') && ($data[1]->getTitle() == 'rut alumno') &&
					($data[2]->getTitle() == 'nombre alumno') && ($data[3]->getTitle() == 'apellido alumno') &&
					($data[4]->getTitle() == 'rut apoderado') && ($data[5]->getTitle() == 'nombre apoderado') &&
					($data[6]->getTitle() == 'nombre paterno apoderado') && 
					($data[7]->getTitle() == 'nombre materno apoderado') && ($data[8]->getTitle() == 'e-mail'))
			{
	
				$persona = new persona();
				$persona_old = new persona();
				$persona = Persona::join('apoderados', 'personas.per_rut', '=', 'apoderados.per_rut')
									->join('apoderados_alumos', 'apoderados.apo_codigo', '=', 'apoderados_alumos.apo_codigo')
									->join('alumnos', 'apoderados_alumos.alu_codigo', '=', 'alumnos.alu_codigo')
									->where('alumnos.cur_codigo', '=', $input['cur_codigo'])
									->select('personas.per_rut')
									->get();
				if ($persona->count()>0){
					$asignacion = new asignacion();
					$asignacion->wherein('per_rut', $persona)->delete();
					$alumno = new alumno();
					$alumno->wherein('per_rut', $persona)->delete();
					$persona_old->wherein('per_rut', $persona)->delete();
				}
				if(!empty($data) && $data->count()){
					foreach ($data as $key => $value) {
						$rut_alu = util::format_rut($value->rut);
						//if ()
						$rut_apo = util::format_rut($value->rut);
						//Rut Alumno
						
						$cantidad = Persona::where('per_rut', '=', $rut['numero'])->count();
						if ($cantidad == 0){
							$persona_new[] = [	'per_rut' 				=> $rut['numero'],
									'per_dv' 				=> $rut['dv'],
									'per_nombre'			=> $value->nombre,
									'per_apellido_paterno' 	=> $value->paterno,
									'per_apellido_materno' 	=> $value->materno,
									'per_email'				=> $value->email];
						}
						$rol = new rol;
						$rol = Rol::where('rol_nombre', '=', 'Alumno')->first();
						$asignacion_new[] = [	'rol_codigo'			=> $rol->rol_codigo,
								'per_rut'				=> $rut['numero']];
							
						$alumno_new[]	= [		'per_rut'				=> $rut['numero'],
								'alu_numero'			=> $value->numero,
								'cur_codigo'			=> $input['cur_codigo']];
					}
					if(!empty($persona_new)){
						$persona = Persona::insert($persona_new);
					}
					if(!empty($asignacion_new)){
						$asignacion = Asignacion::insert($asignacion_new);
					}
					if(!empty($alumno_new)){
						$alumno = Alumno::insert($alumno_new);
					}
					if ($input['groupOrganiza'] == 'orgRut'){
						$alumnos = new alumno();
						$alumno_new = new alumno();
						$alumnos = Alumno::join('personas', 'alumnos.per_rut', '=', 'personas.per_rut')
						->select('personas.per_rut')
						->where('alumnos.cur_codigo', '=', $input['cur_codigo'])
						->orderBy('per_rut', 'ASC')
						->orderBy('per_apellido_paterno', 'ASC')
						->get();
						foreach ($alumnos as $key => $alumno) {
							$alumno_new = new alumno();
							$alumno_new = $alumno_new::where('alumnos.per_rut', '=', $alumno->per_rut)->update(array('alu_numero' => $numero));
							$numero = $numero + 1;
						}
							
					}
					if ($input['groupOrganiza'] == 'orgAlfabetico'){
						$alumnos = new alumno();
						$alumnos = Alumno::join('personas', 'alumnos.per_rut', '=', 'personas.per_rut')
						->select('personas.per_rut')
						->where('alumnos.cur_codigo', '=', $input['cur_codigo'])
						->orderBy('per_apellido_paterno', 'ASC')
						->orderBy('per_apellido_materno', 'ASC')
						->orderBy('per_nombre', 'ASC')
						->get();
						foreach ($alumnos as $key => $alumno) {
							$alumno_new = new alumno();
							$alumno_new = $alumno_new::where('alumnos.per_rut', '=', $alumno->per_rut)->update(array('alu_numero' => $numero));
							$numero = $numero + 1;
						}
					}
				}
			}
			else {
				$errores = 'La primera columna debe contener: "numero", "rut", "nombre", "paterno", "materno", "email"';
			}
		}
		else {
			$errores = 'Error con el archivo';
		}
		if(empty($errores)){
			return redirect()->route('apoderados.index');
		}
		else {
			if (Session::has('search.apoderado_curso')){
				$search = Session::get('search.apoderado_curso');
				$this->cur_codigo	= $search['cur_codigo'];
				Session::put('search.apoderado_errores', array(
						'errores'	=>	$errores));
	
			}
			return redirect()->route('apoderados.index');
	
		}
	
	}
	
	
	
	public function update($id)
	{
		$input = Input::all();
		$persona = new persona;
		$persona = Persona::find($id);

		$persona->per_nombre = $input['per_nombre'];
		$persona->per_nombre_segundo = $input['per_nombre_segundo'];
		$persona->per_apellido_paterno = $input['per_apellido_paterno'];
		$persona->per_apellido_materno = $input['per_apellido_materno'];
		if (!isset($input['mod_password'])){
			$persona->per_password = Hash::make($input['per_password']);
		}
		$persona->per_email = $input['per_email'];
		$persona->save();

		$profesor = new profesor();
		$profesor = Profesor::where('profesores.per_rut', '=', $id)->first();
		$profesor->pro_activo  	= isset($input['pro_activo']) ? 1 : 0;
		$profesor->save();

		return redirect()->route('profesores.index');
	}

	public function getRol(Request $request, $per_rut){
		$rut = util::format_rut($per_rut);
		if ($request->ajax()){
			$persona = new persona();
			$records = Persona::join('asignaciones', 'asignaciones.per_rut', '=', 'personas.per_rut')
			->join('roles', 'roles.rol_codigo', '=', 'asignaciones.rol_codigo')
			->where('personas.per_rut', '=', $rut['numero'])->get();
			return response()->json($records);
		}
	}
	public function arreglo(){
		
		
		$tabla[] = array(	'nombre' 		=> 'Numero',
							'campo'			=> 'alu_numero',
							'clase' 		=> 'container col-md-5 required',
							'validate'		=> '',
							'descripcion'	=> 'Numero',
							'tipo'			=> 'input',
							'select'		=> 0,
							'value'			=> $this->alu_numero,
							'filter'		=> 1,
							'enable'		=> false);
		$tabla[] = array(	'nombre' 		=> 'Run',
							'campo'			=> 'alu_rut',
							'clase' 		=> 'container col-md-5 required',
							'validate'		=> '',
							'descripcion'	=> 'Run',
							'tipo'			=> 'input',
							'select'		=> 0,
							'value'			=> $this->alu_rut,
							'filter'		=> 1,
							'enable'		=> false);
		$tabla[] = array(	'nombre' 		=> 'P. Nombre',
							'campo'			=> 'alu_nombre',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Nombre',
							'value'			=> $this->alu_nombre,
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'A. Paterno',
							'campo'			=> 'alu_apellido_paterno',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Paterno',
							'value'			=> $this->alu_apellido_paterno,
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Run',
							'campo'			=> 'apo_rut',
							'clase' 		=> 'container col-md-5 required',
							'validate'		=> '',
							'descripcion'	=> 'Run',
							'tipo'			=> 'input',
							'select'		=> 0,
							'value'			=> $this->apo_rut,
							'filter'		=> 1,
							'enable'		=> false);
		$tabla[] = array(	'nombre' 		=> 'P. Nombre',
							'campo'			=> 'apo_nombre',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Nombre',
							'value'			=> $this->apo_nombre,
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'A. Paterno',
							'campo'			=> 'apo_apellido_paterno',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Paterno',
							'value'			=> $this->apo_apellido_paterno,
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'E-Mail',
							'campo'			=> 'apo_email',
							'clase' 		=> 'container col-md-8',
							'validate'		=> '',
							'descripcion'	=> 'E-Mail',
							'value'			=> $this->apo_email,
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Modificar Password',
							'campo'			=> 'mod_password',
							'clase' 		=> 'container col-md-1',
							'validate'		=> '',
							'descripcion'	=> 'Activo',
							'value'			=> 1,
							'tipo'			=> 'check',
							'select'		=> 0,
							'filter'		=> 3,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Password',
							'campo'			=> 'per_password',
							'clase' 		=> 'container col-md-4',
							'validate'		=> '',
							'descripcion'	=> 'Password',
							'value'			=> '',
							'tipo'			=> 'password',
							'select'		=> 0,
							'filter'		=> 3,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Re-Password',
							'campo'			=> 'per_password_re',
							'clase' 		=> 'container col-md-4',
							'validate'		=> '',
							'descripcion'	=> 'Re-password',
							'value'			=> '',
							'tipo'			=> 'password',
							'select'		=> 0,
							'filter'		=> 3,
							'enable'		=> true);
		return $tabla;
	}

	public function validador(){
		$validate = "

				$().ready(function () {
					$('#myform').validate({
						rules: {
							'per_rut'				:	{required: true, minlength: 5, maxlength: 50},
							'per_nombre'			:	{required: true, minlength: 2, maxlength: 50},
							'per_apellido_paterno'	:	{required: true, minlength: 2, maxlength: 50},
							'per_apellido_materno'	:	{required: true, minlength: 2, maxlength: 50},
							'per_email'				:	{required: true, email: true,  minlength: 2, maxlength: 50},
							'per_password'			:	{required: true, minlength: 2, maxlength: 15},
							'per_password_re'		:	{required: true, minlength: 2, maxlength: 15, equalTo : '#per_password'}
						}
					});
					if ($('#mod_password').is(':checked')){
						$('#per_password').prop('disabled', true);
						$('#per_password_re').prop('disabled', true);
					}
					$('#mod_password').change(function(event){
						if ($('#mod_password').is(':checked')){
							$('#per_password').prop('disabled', true);
							$('#per_password_re').prop('disabled', true);
						}
						else {
							$('#per_password').prop('disabled', false);
							$('#per_password_re').prop('disabled', false);
						}
					});

				});";
		return $validate;
	}


}
