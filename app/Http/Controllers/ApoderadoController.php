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
use App\models\apoderado_alumno;

use App\helpers\util;
use App\helpers\navegador;
use View;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use Illuminate\Support\Facades\Hash;
use Session;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use App\models\error;


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
				if (!(empty($_POST['alu_numero']) && empty($_POST['alu_rut'])&& empty($_POST['apo_rut']) 
						&& empty($_POST['alu_nombre']) && empty($_POST['alu_apellido_paterno']) 
						&& empty($_POST['apo_rut']) && empty($_POST['apo_nombre'])&& empty($_POST['apo_apellido_paterno']))){
							$this->alu_numero 			= $_POST['alu_numero'];
							$this->alu_rut 				= $_POST['alu_rut'];
							$this->alu_nombre 			= $_POST['alu_nombre'];
							$this->alu_apellido_paterno = $_POST['alu_apellido_paterno'];
							$this->apo_rut 				= $_POST['apo_rut'];
							$this->apo_nombre 			= $_POST['apo_nombre'];
							$this->apo_apellido_paterno = $_POST['apo_apellido_paterno'];

							Session::put('search.apoderado_persona', array(
									'alu_numero' 			=> $this->alu_numero,
									'alu_rut'	 			=> $this->alu_rut,
									'alu_nombre' 			=> $this->alu_nombre,
									'alu_apellido_paterno' 	=> $this->alu_apellido_paterno,
									'apo_rut' 				=> $this->apo_rut,
									'apo_nombre' 			=> $this->apo_nombre,
									'apo_apellido_paterno' 	=> $this->apo_apellido_paterno));
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
							$this->alu_rut 				= $_POST['alu_rut'];
							$this->alu_nombre 			= $_POST['alu_nombre'];
							$this->alu_apellido_paterno = $_POST['alu_apellido_paterno'];
							$this->apo_rut 				= $_POST['apo_rut'];
							$this->apo_nombre 			= $_POST['apo_nombre'];
							$this->apo_apellido_paterno = $_POST['apo_apellido_paterno'];
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
							$this->alu_numero 			= $search['alu_numero'];
							$this->alu_rut 				= $search['alu_rut'];
							$this->alu_nombre 			= $search['alu_nombre'];
							$this->alu_apellido_paterno = $search['alu_apellido_paterno'];
							$this->apo_rut 				= $search['apo_rut'];
							$this->apo_nombre 			= $search['apo_nombre'];
							$this->apo_apellido_paterno = $search['apo_apellido_paterno'];
					}
				}
			}
 			else{
				if (Session::has('search.apoderado_persona')){
					$exist = 0;
					$search = Session::get('search.apoderado_persona');
							$this->alu_numero 			= $search['alu_numero'];
							$this->alu_rut 				= $search['alu_rut'];
							$this->alu_nombre 			= $search['alu_nombre'];
							$this->alu_apellido_paterno = $search['alu_apellido_paterno'];
							$this->apo_rut 				= $search['apo_rut'];
							$this->apo_nombre 			= $search['apo_nombre'];
							$this->apo_apellido_paterno = $search['apo_apellido_paterno'];
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
				
				if ($this->alu_numero != ''){
					$personas = $personas->where('al.alu_numero', 'LIKE', '%'.$this->alu_numero.'%');
				}
				if ($this->alu_rut != ''){
					$personas = $personas->where('pa.per_rut', 'LIKE', '%'.$this->alu_rut.'%');
				}
				if ($this->alu_nombre != ''){
					$personas = $personas->where('pa.per_nombre', 'LIKE', '%'.$this->alu_nombre.'%');
				}
				if ($this->alu_apellido_paterno != ''){
					$personas = $personas->where('pa.per_apellido_paterno', 'LIKE', '%'.$this->alu_apellido_paterno.'%');
				}
				if ($this->apo_rut != ''){
					$personas = $personas->where('pe.per_rut', 'LIKE', '%'.$this->apo_rut.'%');
				}
				if ($this->apo_nombre != ''){
					$personas = $personas->where('pe.per_nombre', 'LIKE', '%'.$this->apo_nombre.'%');
				}
				if ($this->apo_apellido_paterno != ''){
					$personas = $personas->where('pe.per_apellido_paterno', 'LIKE', '%'.$this->apo_apellido_paterno.'%');
				}
				$personas = $personas->paginate($this->paginate);
			}
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => '/'.util::obtener_url().'apoderados', 'pk' => 'alu_rut', 'clase' => 'container col-md-12', 'col' => 8);
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
			$persona = new persona();
			$record = Alumno::join('personas as pal', 'alumnos.per_rut', '=', DB::raw('pal.per_rut'))
			->leftjoin('apoderados_alumnos', 'apoderados_alumnos.alu_codigo', '=', 'alumnos.alu_codigo')
			->leftjoin('apoderados', 'apoderados_alumnos.apo_codigo', '=', 'apoderados.apo_codigo')
			->leftjoin('personas as pap', DB::raw('pap.per_rut'), '=', 'apoderados.per_rut')
			->where(DB::raw( 'pal.per_rut' ), '=', $id)
			->select('pap.per_rut AS apo_rut', 'apoderados.apo_codigo')
			->first();
				
			
			$persona = Persona::where('personas.per_rut', '=', $record['apo_rut'])->first();
			
			$asignacion = new asignacion();
			$asignacion = Asignacion::where('asignaciones.per_rut', '=', $record['apo_rut'])->first();
			$asignacion->delete();
			
			
			$apoderado_alumno = new apoderado_alumno();
			$apoderado_alumno = Apoderado_alumno::where('apoderados_alumnos.apa_codigo', '=', $record['apa_codigo'])->first();
			$apoderado_alumno->delete();
			
			$apoderado = new apoderado();
			$apoderado = apoderado::find($record['apo_codigo'])->first();
			$apoderado->delete();
			$persona->delete();
			return redirect()->route('apoderados.index');
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
		$validate = ApoderadoController::validador();
		if ($privilegio->mas_edit == 0){
			return redirect()->route('logout');
		}
		else{
			$tabla = ApoderadoController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'apoderados', 'pk' => 'alu_rut', 'clase' => 'container col-md-12', 'label' => 'container col-md-4');
			$record = Alumno::join('personas as pal', DB::raw('pal.per_rut'), '=', 'alumnos.per_rut')
			->leftjoin('apoderados_alumnos', 'apoderados_alumnos.alu_codigo', '=', 'alumnos.alu_codigo')
			->leftjoin('apoderados', 'apoderados_alumnos.apo_codigo', '=', 'apoderados.apo_codigo')
			->leftjoin('personas as pap', DB::raw('pap.per_rut'), '=', 'apoderados.per_rut')
			->where(DB::raw( 'pal.per_rut' ), '=', $id)
			->select('alumnos.alu_numero AS alu_numero', 'pal.per_rut AS alu_rut', 
				'pal.per_dv AS alu_dv', 'pal.per_dv AS alu_dv', 
				'pal.per_nombre AS alu_nombre', 'pal.per_apellido_paterno AS alu_apellido_paterno', 
				'pal.per_apellido_materno AS alu_apellido_materno', 'pap.per_rut AS apo_rut', 
				'pap.per_dv AS apo_dv', 'pap.per_dv AS apo_dv', 'pap.per_nombre AS apo_nombre', 
				'pap.per_apellido_paterno AS apo_apellido_paterno', 'pap.per_apellido_materno AS apo_apellido_materno', 
					'pap.per_email AS apo_email', 'apoderados.apo_fono AS apo_fono')
			->first();
			//util::print_a($record,0);
			$curso = Curso::join('alumnos', 'cursos.cur_codigo', '=', 'alumnos.cur_codigo')
					->join('niveles', 'cursos.niv_codigo', '=', 'niveles.niv_codigo')
					->join('profesores', 'profesores.pro_codigo', '=', 'cursos.pro_codigo')
					->join('personas', 'profesores.per_rut', '=', 'personas.per_rut')
					->where('alumnos.per_rut', '=', $id)
					->select('cursos.cur_codigo', DB::raw('CONCAT(cursos.cur_numero, "-", cursos.cur_letra, "_", niveles.niv_nombre) as name'),DB::raw('CONCAT(personas.per_rut, "-", personas.per_dv, " : ",personas.per_nombre, " ", personas.per_nombre_segundo, " ", personas.per_apellido_paterno, " ", personas.per_apellido_materno) as profesor'))
					->first();
				
				
			return view('mantenedor.edit_apoderado')
			->with('record',$record)
			->with('curso', $curso)
			->with('menu', $menu)
//			->with('validate', $validate)
			->with('entidad', $entidad)
			->with('tablas', $tabla)
			->with('title', 'Modificar Apoderados');
		}
	}

	public function exportar_apoderados($id)
	{
		$curso = Curso::join('niveles', 'niveles.niv_codigo', '=', 'cursos.niv_codigo')
						->join('profesores', 'profesores.pro_codigo', '=', 'cursos.pro_codigo')
						->join('personas', 'personas.per_rut', '=', 'profesores.per_rut')
						->where('cursos.cur_codigo', '=', $id)
						->select('cursos.cur_codigo', DB::raw('CONCAT(cursos.cur_numero, "-", cursos.cur_letra, "_", niveles.niv_nombre) as name'),DB::raw('CONCAT(personas.per_rut, "-", personas.per_dv, " : ",personas.per_nombre, " ", personas.per_nombre_segundo, " ", personas.per_apellido_paterno, " ", personas.per_apellido_materno) as profesor'))
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
									'pe.per_email AS apo_email', 'ap.apo_fono AS apo_fono')
						->get();
		if (count($personas) > 0){
			Excel::create($curso->name.' - Apoderado', function($excel) use($personas, $curso) {
				$excel->sheet($curso->name, function($sheet) use($personas, $curso){
					foreach ($personas as $key => $persona) {
						$rut_alu = util::format_rut($persona->alu_rut, $persona->alu_dv);
						$rut_apo = '';
						if ($persona->apo_rut != ''){
							$rut_apo = util::format_rut($persona->apo_rut, $persona->apo_dv);
							$rut_apo = $rut_apo['numero'].'-'.$rut_apo['dv'];
						}
						$data[] = array(
								'Numero'						=> $persona->alu_numero,
								'Rut Alumno'					=> $rut_alu['numero'].'-'.$rut_alu['dv'],
								'Nombre Alumno' 				=> $persona->alu_nombre,
								'Apellido Alumno' 				=> $persona->alu_apellido_paterno,
								'Rut Apoderado'					=> $rut_apo,
								'Nombre Apoderado' 				=> $persona->apo_nombre,
								'Apellido Paterno Apoderado' 	=> $persona->apo_apellido_paterno,
								'Apellido Materno Apoderado' 	=> $persona->apo_apellido_materno,
								'E-Mail Apoderado'				=> $persona->apo_email,
								'Fono Apoderado'				=> $persona->apo_fono
						);
					}
						
					//descripción de curso 
					$sheet->row(2, array(
							'','Curso: ', $curso->name
					));						
					$sheet->row(3, array(
							'','Profesor: ', $curso->profesor
					));
					$sheet->mergeCells('C1:D1');
					$sheet->mergeCells('C2:D2');
					$sheet->cells('B2:B3', function($cells) {
						$cells->setBackground('#2fa4e7');
						$cells->setFontColor('#ffffff');
					});
					
					
					$persona = Persona::join('alumnos', 'personas.per_rut', '=', 'alumnos.per_rut')
										->join('asignaciones', 'personas.per_rut', '=', 'asignaciones.per_rut')
										->where('alumnos.cur_codigo', '=', $curso->cur_codigo)
										->select(DB::raw('max(alumnos.alu_numero) maximo'))
										->first();
					$numero = $persona->maximo + 1;
					for ($i = $numero; $i <= 50; $i++) {
						$sheet->row($i+5, array(
								$i
						));
					}
					
					//Titulo
					$sheet->row(5, array(
							'Alumno','', '', '', 'Apoderado' 
					));
					
					$sheet->mergeCells('A5:D5');
					$sheet->mergeCells('E5:J5');
	
					
					$sheet->fromArray($data, null, 'A6', false, true);
					$sheet->setBorder('B2:D3', 'thin');
					$sheet->setBorder('A5:J55', 'thin');
					$sheet->cells('A5:J6', function($cells) {
						$cells->setBackground('#2fa4e7');
						$cells->setFontColor('#ffffff');
					});
					$sheet->setWidth(array(
							'A'     =>  9,
							'B'     =>  15,
							'C'     =>  25,
							'D'     =>  25,
							'E'     =>  15,
							'F'     =>  25,
							'G'     =>  25,
							'H'		=> 	25,
							'I'		=> 	45,
							'J'		=> 	20
					));
					
				});
			})->download('xls');
		}
		else {
			$errores = 'Debe cargar primero los alumnos';
			if (Session::has('search.apoderado_curso')){
				$search = Session::get('search.apoderado_curso');
				$this->cur_codigo	= $search['cur_codigo'];
				Session::put('search.apoderado_errores', array(
						'errores'	=>	$errores));
		
			}
			return redirect()->route('apoderados.index');
		
		}
		
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
/*		try {*/
			$input = Input::all();
			if(Input::hasFile('import_file')){
				$path = Input::file('import_file')->getRealPath();
				$data = Excel::load($path, function($reader) {
				})->noHeading()->toarray();
				if ($data[5][0] == 'Numero' && $data[5][1] == 'Rut Alumno' && 
					$data[5][2] == 'Nombre Alumno' && $data[5][3] == 'Apellido Alumno' &&
					$data[5][4] == 'Rut Apoderado' && $data[5][5] == 'Nombre Apoderado' &&
					$data[5][6] == 'Apellido Paterno Apoderado' && $data[5][7] == 'Apellido Materno Apoderado' &&
					$data[5][8] == 'E-Mail Apoderado' && $data[5][9] == 'Fono Apoderado')
				{
					if(!empty($data) && count($data) > 0){
						foreach ($data as $key => $value) {
							if ($key >= 6){
								if (isset($value[1]) || isset($value[4])){
									$rut_alu = util::format_rut($value[1]);
									//if ()
									$rut_apo = util::format_rut($value[4]);
									//Rut Alumno
									
									$cantidad = Persona::where('per_rut', '=', $rut_apo['numero'])->count();
									if ($cantidad == 0){
										$persona = new persona();
										$persona->per_rut 				= $rut_apo['numero'];
										$persona->per_dv 				= $rut_apo['dv'];
										$persona->per_nombre 			= $value[5];
										$persona->per_apellido_paterno 	= $value[6];
										$persona->per_apellido_materno 	= $value[7];
										$persona->per_email				= $value[8];
										$persona->save();
										$apoderado = new apoderado();
										$apoderado->per_rut  = $rut_apo['numero'];
										$apoderado->apo_fono = $value[9];
										$apoderado->save();
										$id_apoderado = $apoderado->apo_codigo;
									}
									else {
										$persona_upd = new persona();
										$persona_upd = Persona::where('personas.per_rut', '=', $rut_apo['numero'])->first();
										$persona_upd->per_nombre			= $value[5];
										$persona_upd->per_apellido_paterno 	= $value[6];
										$persona_upd->per_apellido_materno 	= $value[7];
										$persona_upd->per_email				= $value[8];
										$persona_upd->save();
										$apoderado_upd = new apoderado();
										$apoderado_upd = Apoderado::where('apoderados.per_rut', '=', $rut_apo['numero'])->first();
										$apoderado_upd->apo_fono				= $value[9];
										$apoderado_upd->save();
										$id_apoderado = $apoderado_upd->apo_codigo;
									}
										
									$rol = new rol;
									$rol = Rol::where('rol_nombre', '=', 'Apoderado')->first();
									
									$cantidad = asignacion::where('per_rut', '=', $rut_apo['numero'])->where('rol_codigo', '=', $rol->rol_codigo)->count();
									if ($cantidad == 0){
										$asignacion = new asignacion();
										$asignacion->rol_codigo = $rol->rol_codigo;
										$asignacion->per_rut = $rut_apo['numero'];
										$asignacion->save();
									}
										

									$alumno = Alumno::where('alumnos.per_rut', '=', $rut_alu['numero'])->first();
										
									$cantidad = apoderado_alumno::where('apo_codigo', '=', $id_apoderado)->where('alu_codigo', '=', $alumno->alu_codigo)->count();
									if ($cantidad == 0){
										$apoderado_alumno = new apoderado_alumno();
										$apoderado_alumno->apo_codigo = $apoderado->apo_codigo;
										$apoderado_alumno->alu_codigo = $alumno->alu_codigo;
										$apoderado_alumno->save();
									}
									unset($persona_new);
									unset($asignacion_new);
									unset($apoderado_new);
									unset($apoderado_alumno_new);
								}
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
/*		} catch (\Exception $e) {
			$errores = 'Se produjo un error en el proceso\nDescargue el archivo y vuelva a ingresar los valores';
			$error = new error();
			$error_new[] = [
					'err_datos' 		=> $e,
					'err_fecha'			=> DB::raw('NOW()'),
					'per_rut'			=> $idusuario = Auth::user()->per_rut,
					'mod_codigo' 		=> $this->Privilegio_modulo,
					'err_procedure'		=> 'save_apoderados'];
				
			$error = error::insert($error_new);
				
			if (Session::has('search.apoderado_curso')){
				$search = Session::get('search.apoderado_curso');
				$this->cur_codigo	= $search['cur_codigo'];
				Session::put('search.apoderado_errores', array(
						'errores'	=>	$errores));
	
			}
			return redirect()->route('apoderados.index');
		}
*/	
	}
	
	
	
	
	public function update($id)
	{
		$input = Input::all();
		$persona_alu = new persona;
		$persona_alu = alumno::join('apoderados_alumnos', 'alumnos.alu_codigo', '=', 'apoderados_alumnos.alu_codigo')
							->join('apoderados', 'apoderados.apo_codigo', '=', 'apoderados_alumnos.apo_codigo')
							->select('apoderados.per_rut')
							->where('alumnos.per_rut', '=', $id)
							->first();
		
		if (count($persona_alu) == 0){
			$rut_apo = util::format_rut($input['apo_rut']);
			$persona = new persona;
			$persona_new = new persona;
			$persona = persona::where('personas.per_rut', '=', $rut_apo['numero'])->first();
			if (count($persona) == 0){
				$persona_new->per_rut = $rut_apo['numero'];
				$persona_new->per_dv = $rut_apo['dv'];
				$persona_new->per_nombre = $input['apo_nombre'];
				$persona_new->per_apellido_paterno = $input['apo_apellido_paterno'];
				$persona_new->per_apellido_materno = $input['apo_apellido_materno'];
				if (isset($input['dat_password'])){
					$persona_new->per_password = Hash::make($input['apo_password']);
				}
				if (isset($input['dat_adicionales'])){
					$persona_new->per_email = $input['apo_email'];
				}
				$persona_new->save();
			}
			$asignacion = new asignacion();
			$asignacion_new = new asignacion();
			$asignacion = asignacion::where('asignaciones.per_rut', '=', $rut_apo['numero'])->first();
			if (count($asignacion) == 0){
				$rol = new rol();
				$rol = rol::where('roles.rol_nombre', '=', 'Apoderado')->first();
				$asignacion_new->rol_codigo = $rol->rol_codigo;
				$asignacion_new->per_rut = $rut_apo['numero'];
				$asignacion_new->save();
			}
			$apoderado = new apoderado();
			$apoderado_new = new apoderado();
			$apoderado = Apoderado::where('apoderados.per_rut', '=', $rut_apo['numero'])->first();
			if (count($apoderado) == 0){
				$apoderado_new->apo_fono  	= $input['apo_fono'];
				$apoderado_new->save();
			}
			$apoderado_alumno = new apoderado_alumno();
			$apoderado_alumno_new = new apoderado_alumno();
			$apoderado_alumno = apoderado_alumno::join('apoderados', 'apoderados_alumnos.apo_codigo', '=', 'apoderados.apo_codigo')
											->join('alumnos', 'apoderados_alumnos.alu_codigo', '=', 'alumnos.alu_codigo')
											->where('alumnos.per_rut', '=', $id)
											->where('apoderados.per_rut', '=', $rut_apo['numero'])
											->first();
			if (count($apoderado_alumno) == 0){
				$alumno = new alumno();
				$alumno = alumno::where('alumnos.per_rut', '=', $id)->first();
				$apoderado_alumno_new->apo_codigo = $apoderado->apo_codigo;
				$apoderado_alumno_new->alu_codigo = $alumno->alu_codigo;
				$apoderado_alumno_new->save();
			}
		}
		return redirect()->route('apoderados.index');
	}

	public function getApoderado_alumno(Request $request, $per_rut){
		$rut = util::format_rut($per_rut);
		$persona = new persona();
		$records = Persona::leftjoin('alumnos', 'alumnos.per_rut', '=', 'personas.per_rut')
							->leftjoin('profesores', 'profesores.per_rut', '=', 'personas.per_rut')
							->leftjoin('apoderados', 'apoderados.per_rut', '=', 'personas.per_rut')
							->where('personas.per_rut', '=', $rut['numero'])
							->select(DB::raw('alumnos.per_rut as alumno, profesores.per_rut as profesor, apoderados.per_rut as apoderado'))
							->get();
		if ($request->ajax()){
			return response()->json($records);
		}
		else{
			util::print_a($records,0);
		}
	}
	public function getApoderado(Request $request, $per_rut){
		$rut = util::format_rut($per_rut);
		$persona = new persona();
		$records = Persona::join('apoderados', 'apoderados.per_rut', '=', 'personas.per_rut')
		->where('apoderados.per_rut', '=', $rut['numero'])
		->select('personas.per_nombre', 'personas.per_apellido_paterno', 'personas.per_apellido_materno', 'personas.per_email', 'apoderados.apo_fono')
		->get();
		if ($request->ajax()){
			return response()->json($records);
		}
		else{
			util::print_a($records,0);
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
							'value'			=> '',
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 3,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Fono',
							'campo'			=> 'apo_fono',
							'clase' 		=> 'container col-md-8',
							'validate'		=> '',
							'descripcion'	=> 'Fono',
							'value'			=> '',
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 3,
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
		$validate = "";
/*
				$().ready(function () {
					$('#myform').validate({
						rules: {
							'apo_rut'				:	{required: true, minlength: 5, maxlength: 50},
							'apo_nombre'			:	{required: true, minlength: 2, maxlength: 50},
							'apo_apellido_paterno'	:	{required: true, minlength: 2, maxlength: 50},
							'apo_apellido_materno'	:	{required: true, minlength: 2, maxlength: 50},
							'apo_email'				:	{required: true, email: true,  minlength: 2, maxlength: 50},
							'apo_password'			:	{required: true, minlength: 2, maxlength: 15},
							'apo_password_re'		:	{required: true, minlength: 2, maxlength: 15, equalTo : '#per_password'}
						}
					});";
/*					if ($('#mod_password').is(':checked')){
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

				});";*/
		return $validate;
	}


}
