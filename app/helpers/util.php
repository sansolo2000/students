<?php
	namespace App\helpers;
	
	
	use App\models\persona;
	
	
	class util{
		 public static function print_a( $algo='', $exit ){
			echo "<div style='border:1px solid red;clear:both;'><pre>";
			print_r( $algo );
			echo "</pre></div>";
			if ($exit == 0) {
				exit();
			}			
		}
		public static function array_indice($datos, $default){
			if ($default == -1){
				$array_retorno[-1] = ':: Seleccionar ::';
			}
			if (!empty($datos)){
				foreach ($datos as $clave=>$fila){
					$array_retorno[$clave] =$fila;
				}
			}
			return $array_retorno;
		}
		public static function format_rut($rut, $dv = null){
			if (!isset($dv)){
				$rut = str_replace('-', '', str_replace('.', '', $rut));
				$resultado['numero'] = substr($rut, 0, -1);
				$resultado['dv'] = substr($rut, -1);
			}
			else{
				if ($dv == 'X'){
					$persona = new persona();
					$persona = Persona::find($rut);	
					$resultado['numero'] = number_format((float)$persona['per_rut'],0,',','.');
					$resultado['dv'] = $persona['per_dv'];
				}
				else {
					$resultado['numero'] = number_format((float)$rut,0,',','.');
					$resultado['dv'] = $dv;
				}
			}
			return $resultado;	
		}
		public static function obtener_url(){
			return '/localhost/students/public/';
		}
	}