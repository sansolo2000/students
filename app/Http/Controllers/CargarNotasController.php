<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\helpers\navegador;
use Session;
use App\Http\Requests;
use App\models\curso;
use App\helpers\util;
use DB;
use App\models\periodo;
use App\models\calificacion;
use Maatwebsite\Excel\Facades\Excel;
use App\models\alumno;



class CargarNotasController extends Controller
{
	public $cur_codigo;
	public $asg_nombre;
	public $per_rut;
	public $per_nombre;
	public $pri_codigo;
	
	public $Privilegio_modulo = 'Cargar Notas';
	public $paginate = 20;
	
	public function index($id = NULL)
	{
		// Menu
	
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$cantidad = 0;
	
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
				$this->cur_codigo 	= $_POST['cur_nombre'];
				$this->pri_codigo	= $_POST['pri_nombre'];
				Session::put('search.cargarnotas', array(
						'cur_codigo' 	=> $this->cur_codigo,
						'pri_codigo'	=> $this->pri_codigo
				));
			}
			else{
				if (Session::has('search.cargarnotas')){
					$exist = 1;
					$search = Session::get('search.cargarnotas');
					$this->cur_codigo 	= $search['cur_codigo'];
					$this->pri_codigo 	= $search['pri_codigo'];
						
				}
				else{
					$exist = 1;
					$this->cur_codigo 	= -1;
					$this->pri_codigo 	= periodo::where('pri_activo', '=', 1)->select('pri_codigo')->first()->toArray();
				}
			}
	
			//$tabla = AsignaturaController::arreglo();
			if ($exist == 1){
				$profesor = curso::select(DB::raw('CONCAT(pr.per_nombre, " ", pr.per_apellido_paterno, " ", pr.per_apellido_materno) as profesor'))
							->join('niveles', 'cursos.niv_codigo', '=', 'niveles.niv_codigo')
							->join('profesores as pj', 'cursos.pro_codigo', '=', DB::raw('pj.pro_codigo'))
							->join('personas AS pr', DB::raw('pr.per_rut'), '=', 'pj.per_rut')
							->where('cursos.cur_codigo', '=', $this->cur_codigo)
							->first();
				$asignaturas = curso::select('asignaturas.asg_codigo', 'asignaturas.asg_nombre')
							->join('asignaturas', 'cursos.cur_codigo', '=', 'asignaturas.cur_codigo')
							->orderBy('asignaturas.asg_orden', 'ASC')
							->where('asignaturas.cur_codigo', '=', $this->cur_codigo)
							->get();
				$alumnos = curso::select(DB::raw('al.alu_codigo'), 'asignaturas.asg_codigo', 'asignaturas.asg_nombre', DB::raw('ag.per_rut as per_rut'), DB::raw('ag.per_dv as per_dv'),
										DB::raw('ag.per_nombre as per_nombre'), DB::raw('ag.per_apellido_paterno as per_apellido_paterno'),
										DB::raw('ag.per_apellido_materno as per_apellido_materno'),
										DB::raw('pr.per_nombre as pro_nombre'), DB::raw('pr.per_apellido_paterno as pro_apellido_paterno'),
										DB::raw('pr.per_apellido_materno as pro_apellido_materno'))
							->join('asignaturas', 'cursos.cur_codigo', '=', 'asignaturas.cur_codigo')
							->join('profesores as pj', 'asignaturas.pro_codigo', '=', DB::raw('pj.pro_codigo'))
							->join('alumnos as al', 'cursos.cur_codigo', '=', DB::raw('al.cur_codigo'))
							->join('personas AS pr', DB::raw('pr.per_rut'), '=', 'pj.per_rut')
							->join('personas AS ag', DB::raw('ag.per_rut'), '=', 'al.per_rut')
							->where('asignaturas.cur_codigo', '=', $this->cur_codigo)
							->orderBy('asignaturas.asg_orden', 'ASC')
							->orderBy(DB::raw('al.alu_numero'), 'ASC')
							->get();
							//util::print_a($curso, 0);
			}
			
			$cabecera = '
					<div class="form-group col-sm-12">
					<ul class="nav nav-tabs">';
			$ubicacion = 1;
			foreach ($asignaturas as $asignatura){
				if ($ubicacion == 1){
					$cabecera .= '<li class="active">';
					$ubicacion = 0;
				}
				else{
					$cabecera .= '<li>';
				}
				$cabecera .= '			<a aria-expanded="false" href="#'.$asignatura->asg_codigo.'" data-toggle="tab">'.$asignatura->asg_nombre.'</a>';
				$cabecera .= '</li>';
			}
			$cabecera .= '</ul>';
			
			
 			$cuerpo	= '<div id="myTabContent" class="tab-content">';
 			$asignatura_seleccionada = '';
 			$ubicacion = 1;
 			
 			foreach ($alumnos as $alumno){
 				if ($alumno->asg_nombre != $asignatura_seleccionada){
 				 	$notas = calificacion::join('asignaturas', 'calificaciones.asg_codigo', '=', 'asignaturas.asg_codigo')
						->where('asignaturas.cur_codigo', '=', $this->cur_codigo)
						->where('asignaturas.asg_codigo', '=', $alumno->asg_codigo)
						->groupby('asignaturas.asg_nombre')
						->select(DB::raw('calificaciones.asg_codigo, asignaturas.asg_nombre, count(*) as cantidad'))
						->orderBy(DB::raw('cantidad'), 'ASC')
						->first();
// 				 	util::print_a($notas['cantidad'], 0);
 				 	$cantidad = $notas['cantidad'];
					if($cantidad < 10){
						$cantidad = 10;
					}
 					if ($asignatura_seleccionada != ''){
 						$cuerpo	.= '</tbody>
 									</table>
 									</div>';
 					}
 					$asignatura_seleccionada = $alumno->asg_nombre; 
 					if ($ubicacion == 1){
 						$cuerpo	.= '<div class="tab-pane fade active in" id="'.$alumno->asg_codigo.'">';
 						$ubicacion = 0;
 					}
 					else{
 						$cuerpo	.= '<div class="tab-pane fade" id="'.$alumno->asg_codigo.'">';
 					}
					if ($this->cur_codigo != -1){
						$clase = '';
					}
					else{
						$clase = "disabled";
					}
 					$cuerpo .= '
						<div class="container col-md-12">
							&nbsp;
						</div>
						<div class="container col-md-12">
 							<div class="container col-md-3 center_pers">
								<a href="'.$alumno->asg_codigo.'" class="btn btn-primary '.$clase.'">Bajar Notas</a>
					  		</div>
 							<div class="container col-md-6 center_pers">
								<div class="panel panel-primary">
									<div class="panel-heading">
						    			<h3 class="panel-title">Profesor de '.$alumno->asg_nombre.'</h3>
						  			</div>
						  			<div class="panel-body">
										<input class="form-control" id="asignatura" name="asignatura" type="text" disabled="disabled" value="'.$alumno->pro_nombre.' '.$alumno->pro_apellido_paterno.' '.$alumno->pro_apellido_materno.'">
									</div>
								</div>
							</div>
							<div class="container col-md-3 center_pers">
								<a href="uploadscore/'.$this->cur_codigo.'/'.$alumno->asg_codigo.'" class="btn btn-primary '.$clase.'">Subir Notas</a>
					  		</div>
						</div>';
 					$cuerpo	.= '<table class="table table-striped table-hover table-bordered">
				 					<thead>
					 					<tr>
						 					<th style="width:12%">Rut</th>
						 					<th style="width:22%">Alumno</th>
						 					<th style="width:6%">N1</th>
						 					<th style="width:6%">N2</th>
						 					<th style="width:6%">N3</th>
						 					<th style="width:6%">N4</th>
						 					<th style="width:6%">N5</th>
						 					<th style="width:6%">N6</th>
						 					<th style="width:6%">N7</th>
						 					<th style="width:6%">N8</th>
						 					<th style="width:6%">N9</th>
						 					<th style="width:6%">N10</th>
						 					<th style="width:6%">Pro</th>
 										</tr>
					 				</thead>
 									<tbody>';
 				}
 				$rut = util::format_rut($alumno->per_rut, $alumno->per_dv);
 				$cuerpo	.= '
					 					<tr>
						 					<td>'.$rut['numero'].'-'.$rut['dv'].'</th>
						 					<td>'.$alumno->per_nombre.' '.$alumno->per_apellido_paterno.' '.$alumno->per_apellido_paterno.'</th>';
 				$calificaciones = calificacion::join('asignaturas', 'calificaciones.asg_codigo', '=', 'asignaturas.asg_codigo')
 											->where('asignaturas.cur_codigo', '=', $this->cur_codigo)
											->where('asignaturas.asg_codigo', '=', $alumno->asg_codigo)
											->where('calificaciones.alu_codigo', '=', $alumno->alu_codigo)
											->where('calificaciones.pri_codigo', '=', $this->pri_codigo)
											->select('calificaciones.cal_numero')
 											->get();
				$i = 1;
				$suma = 0;
 				foreach ($calificaciones as $calificacion) {
						$cuerpo	.= '				<td>'.number_format($calificacion->cal_numero, 1, ',', ' ').'</th>';
						$suma = $suma + $calificacion->cal_numero;
						$i++;
 				}
 				$cantidad_notas = $i - 1;
 				if ($cantidad_notas != 0){
 					$suma = $suma / $cantidad_notas;
 				}
				while ($i <= $cantidad) {
					$cuerpo	.= '				<td>&nbsp;</th>';
					$i++;
 				} 				
				$cuerpo	.= '				<td>'.number_format($suma, 1, ',', ' ').'</th>
 										</tr>';
 						
 				
 				
 			}
 			$cuerpo	.= '</tbody>
 						</table>
 						</div>';
 			$cuerpo	.= '</div>
					</div>';
			
 			$periodo = new periodo();
 			$periodo = periodo::orderBy('pri_orden', 'ASC')
 									->lists('pri_nombre', 'pri_codigo')
 									->toArray();
 			$periodo2 = $periodo;
 			$periodo2 = util::array_indice($periodo, 0);
 			$periodo = util::array_indice($periodo, -1);
 			
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => '/'.util::obtener_url().'cargarnotas', 'pk' => 'cur_codigo', 'clase' => 'container col-md-12', 'col' => 5);
			return view('main.cargarnotas')
					->with('menu', $menu)
					->with('cuerpo', $cuerpo)
					->with('cur_codigo', $this->cur_codigo)
					->with('pri_codigo', $this->pri_codigo)
					->with('periodo2', $periodo2)
					->with('periodo', $periodo)
					->with('profesor', $profesor)
					->with('record', $asignaturas)
					->with('cabecera', $cabecera)
					->with('entidad', $entidad)
					->with('CantidadNotas', $cantidad)
					->with('privilegio', $privilegio);
		}
	}
	
	public function show(){
		return redirect()->route('main.cargarnotas');
	}
	
	public function exportar_calificaciones($curso, $asignatura=0, $periodoMostrar=0, $notas)
	{
		$profesor = curso::select('cursos.cur_codigo', DB::raw('CONCAT(cursos.cur_numero, "-", cursos.cur_letra, " ", niveles.niv_nombre) as name'),
								DB::raw('CONCAT("Profesor Jefe: ", pr.per_nombre, " ", pr.per_apellido_paterno) as profesor'))
							->join('niveles', 'cursos.niv_codigo', '=', 'niveles.niv_codigo')
							->join('profesores as pj', 'cursos.pro_codigo', '=', DB::raw('pj.pro_codigo'))
							->join('personas AS pr', DB::raw('pr.per_rut'), '=', 'pj.per_rut')
							->where('cursos.cur_codigo', '=', $curso)
							->first();
		$asignaturas = curso::select('asignaturas.asg_codigo', 'asignaturas.asg_nombre')
							->join('asignaturas', 'cursos.cur_codigo', '=', 'asignaturas.cur_codigo')
							->orderBy('asignaturas.asg_orden', 'ASC')
							->where('asignaturas.cur_codigo', '=', $curso);
		if ($asignatura != 0){
			$asignaturas = $asignaturas->where('asignaturas.cur_codigo', '=', $asignatura);
			$asignaturas = $asignaturas->first();
		}
		else{
			$asignaturas = $asignaturas->get();
		}
		
		//$nombre = $curso->name;
		$alumnos = alumno::where('alumnos.cur_codigo', '=', $curso)->get();
		$CantidadAlumnos = $alumnos->count(); 
		if ($CantidadAlumnos> 0){
			if ($asignatura == 0){
				$libros = $profesor->name.' - Libro de clases';
			}
			else{
				$libros = $profesor->name.' - Asignatura'.$asignaturas->asg_nombre;
			}
			//util::print_a($alumnos, 0);
			$alfabeto = util::alfabeto(0);						
			Excel::create($libros, function($excel) use($profesor, $asignaturas, $notas, $periodoMostrar, $curso, $CantidadAlumnos, $alfabeto) {
				if ($periodoMostrar == 0){
					$periodos = periodo::orderBy('periodos.pri_orden', 'ASC')->get();
				}
				else{
					$periodos = periodo::orderBy('periodos.pri_orden', 'ASC')->Where('periodos.pri_codigo', '=', $periodoMostrar)->get();
				}
				$columnas_excel = CargarNotasController::columnas_excel($notas, $periodos->count());
				foreach ($asignaturas as $asignaturalistar){
					$excel->sheet($asignaturalistar->asg_nombre, function($sheet) use($profesor, $asignaturalistar, $periodoMostrar, $notas, $curso, $CantidadAlumnos, $columnas_excel, $periodos, $alfabeto){
						$ind = 0;
						$alumnos = curso::select(DB::raw('al.alu_numero'), DB::raw('al.alu_codigo'), 'asignaturas.asg_codigo', 'asignaturas.asg_nombre', DB::raw('ag.per_rut as per_rut'), DB::raw('ag.per_dv as per_dv'),
											DB::raw('ag.per_nombre as per_nombre'), DB::raw('ag.per_apellido_paterno as per_apellido_paterno'),
											DB::raw('ag.per_apellido_materno as per_apellido_materno'),
											DB::raw('pr.per_nombre as pro_nombre'), DB::raw('pr.per_apellido_paterno as pro_apellido_paterno'),
											DB::raw('pr.per_apellido_materno as pro_apellido_materno'))
											->join('asignaturas', 'cursos.cur_codigo', '=', 'asignaturas.cur_codigo')
											->join('profesores as pj', 'asignaturas.pro_codigo', '=', DB::raw('pj.pro_codigo'))
											->join('alumnos as al', 'cursos.cur_codigo', '=', DB::raw('al.cur_codigo'))
											->join('personas AS pr', DB::raw('pr.per_rut'), '=', 'pj.per_rut')
											->join('personas AS ag', DB::raw('ag.per_rut'), '=', 'al.per_rut')
											->where('asignaturas.cur_codigo', '=', $curso)
											->where('asignaturas.asg_codigo', '=', $asignaturalistar->asg_codigo)
											->orderBy('asignaturas.asg_orden', 'ASC')
											->orderBy(DB::raw('al.alu_numero'), 'ASC')
											->get();
						foreach ($alumnos as $key => $alumno) {
							$pos = 0;
							$rut = util::format_rut($alumno->per_rut, $alumno->per_dv);
							$data[$ind][$columnas_excel[$pos]['name']] 	= $alumno->alu_numero;
							$pos++;
							$data[$ind][$columnas_excel[$pos]['name']] 	= $rut['numero'].'-'.$rut['dv'];
							$pos++;
							$data[$ind][$columnas_excel[$pos]['name']] 	= $alumno->per_nombre.' '.$alumno->per_apellido_paterno.' '.$alumno->per_apellido_materno;
							$pos++;
							foreach ($periodos as $periodo){
								$calificaciones = calificacion::join('asignaturas', 'calificaciones.asg_codigo', '=', 'asignaturas.asg_codigo')
											->where('asignaturas.cur_codigo', '=', $profesor->cur_codigo)
											->where('asignaturas.asg_codigo', '=', $alumno->asg_codigo)
											->where('calificaciones.alu_codigo', '=', $alumno->alu_codigo)
											->where('calificaciones.pri_codigo', '=', $periodo['pri_codigo'])
											->select('calificaciones.cal_numero')
											->get();
								$col = $pos;
								foreach ($calificaciones as $calificacion) {
									$data[$ind][$columnas_excel[$pos]['name']] = (float) $calificacion->cal_numero;
									$pos++;
								}
								while ($pos <= ($notas+$col-1)) {
									$data[$ind][$columnas_excel[$pos]['name']] = '';
									$pos++;
								}
								$row = $ind + 6;
								
								$data[$ind][$columnas_excel[$pos]['name']] = "=if(sum({$alfabeto[$col]}{$row}:{$alfabeto[$pos - 1]}{$row})=0, 0, round(average({$alfabeto[$col]}{$row}:{$alfabeto[$pos - 1]}{$row}),1)";
								$pos++;
							}
							$celda = '';
							foreach ($columnas_excel as $columna_excel){
								if ($columna_excel['type'] == 2){
									$celda .= "{$columna_excel['letter']}{$row};";
								}
							}
							$celda = substr($celda, 0, strlen($celda)-1);
							$celda = "=if(sum({$celda})=0, 0, round(average({$celda}),1)";
							$data[$ind][$columnas_excel[$pos]['name']] = $celda;
							$ind++;
						}
						$sheet->row(2, array(
								'','Curso: ', $profesor->name
						));
						$sheet->row(3, array(
								'','Profesor: ', $profesor->profesor
						));
						$sheet->fromArray($data, null, 'A5', false, true);
						$sheet->setBorder('B2:D3', 'thin');
						$sheet->mergeCells('C2:D2');
						$sheet->mergeCells('C3:D3');
						$celda = $CantidadAlumnos+5;
						$letter1 = $columnas_excel[0]['letter'];
						$letter2 = $columnas_excel[count($columnas_excel)-1]['letter'];
						$celda = "{$letter1}5:{$letter2}{$celda}";
						$sheet->setBorder($celda, 'thin');
						$sheet->cells('B2:B3', function($cells) {
							$cells->setBackground('#2fa4e7');
							$cells->setFontColor('#ffffff');
						});
						foreach ($columnas_excel as $clave => $columna_excel){
							if ($columna_excel['type'] == 2 || $columna_excel['type'] == 3){
								$celda = $CantidadAlumnos+5;
								$letter1 = $columna_excel['letter'];
								$celda = "{$letter1}6:{$letter1}{$celda}";
								$sheet->cells("{$celda}", function($cells) {
									$cells->setBackground('#E8E8E8');
									$cells->setFontColor('#000000');
								});
							}
							if ($columna_excel['type'] > 0){
								$celda = $CantidadAlumnos+5;
								$celda = "{$columna_excel['letter']}6:{$columna_excel['letter']}{$celda}";
								$decimal[$celda] = '0.0';
							}
							$width[$columna_excel['letter']] = $columna_excel['width'];
						}
						$sheet->setColumnFormat($decimal);
						$sheet->setWidth($width);
					});
					unset($data);
				}
				$excel->setActiveSheetIndex(0);
			})->download('xls');
		}
		else{
			//mensaje
		}
	}
	
	public function columnas_excel($CantidadNotas, $CantidadPeriodos){
		$columnas[] = array(
				'name' 			=> 'Numero',
				'background'	=> false,
				'width'			=> 9,
				'letter'		=> 'A',	
				'type'			=> 0
		);
		$columnas[] = array(
				'name' 			=> 'Run',
				'background'	=> false,
				'width'			=> 15,
				'letter'		=> 'B',	
				'type'			=> 0
		);
		$columnas[] = array(
				'name' 			=> 'Nombre Alumno',
				'background'	=> false,
				'width'			=> 30,
				'letter'		=> 'C',	
				'type'			=> 0
		);
		$WidthNotas = 6;
		$pos = 2;
		$notas = 1;
		for ($i = 1; $i <= $CantidadPeriodos; $i++) {
			for ($j = 1; $j <= $CantidadNotas; $j++) {
				$columnas[] = array(
						'name' 			=> 'N'.$notas,
						'background'	=> false,
						'width'			=> $WidthNotas,
						'position'		=> $j + $pos,
						'letter'		=> util::alfabeto($j + $pos),	
						'type'			=> 1
				);
				$notas++;
			}
			$columnas[] = array(
					'name' 			=> 'Prom.'.$i,
					'background'	=> false,
					'width'			=> $WidthNotas + 1,
					'position'		=> $j + $pos,
					'letter'		=> util::alfabeto($j + $pos),	
					'type'			=> 2
			);
			$pos = $pos + $j; 
		}
		$pos++;
		if ($CantidadPeriodos > 1){
			$columnas[] = array(
					'name' 			=> 'Prom. Final',
					'background'	=> false,
					'width'			=> $WidthNotas + 5,
					'letter'		=> util::alfabeto($pos),	
					'type'			=> 3
			);
		}
		return $columnas;		
	}
	
	
}
