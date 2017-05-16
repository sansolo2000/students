<?php
	namespace App\helpers;
	
	
	use App\models\persona;
	use Session;
	
	class util{

		public static function limpiar_cookies(){
			Session::forget('search.colegio');
			Session::forget('search.usuario');
			Session::forget('search.alumno_persona');
			Session::forget('search.alumno_curso');
			Session::forget('search.alumno_errores');
			Session::forget('search.aplicacion');
			Session::forget('search.apoderado_persona');
			Session::forget('search.apoderado_curso');
			Session::forget('search.apoderado_errores');
			Session::forget('search.asignatura');
			Session::forget('search.cargarnotas');
			Session::forget('search.cargarnotas_errores');
			Session::forget('search.comuna');
			Session::forget('search.curso');
			Session::forget('search.malla_curricular');
			Session::forget('search.modulo_asignados');
			Session::forget('search.modulo');
			Session::forget('search.nivel');
			Session::forget('search.periodo');
			Session::forget('search.profesor');
			Session::forget('search.region');
			Session::forget('search.rol');
			Session::forget('search.colegio_errores');
			Session::forget('search.asignatura_errores');
			Session::forget('search.aplicacion_errores');
			Session::forget('search.profesor_errores');
		}
		
		public static function validaRut($rut){
			if(strpos($rut,"-")==false){
				$RUT[0] = substr($rut, 0, -1);
				$RUT[1] = substr($rut, -1);
			}else{
				$RUT = explode("-", trim($rut));
			}
			$suma = 0;
			$elRut = str_replace(".", "", trim($RUT[0]));
			$factor = 2;
			for($i = strlen($elRut)-1; $i >= 0; $i--):
				$factor = $factor > 7 ? 2 : $factor;
				$suma += $elRut{$i}*$factor++;
			endfor;
			$resto = $suma % 11;
			$dv = 11 - $resto;
			if($dv == 11){
				$dv=0;
			}else if($dv == 10){
				$dv="k";
			}else{
				$dv=$dv;
			}
			if($dv == trim(strtolower($RUT[1]))){
				return true;
			}else{
				return false;
			}
		}
		
		 public static function roles_persona($per_rut){
			$roles = persona::join('asignaciones', 'asignaciones.per_rut', '=', 'personas.per_rut')
		 						->join('roles', 'asignaciones.rol_codigo', '=', 'roles.rol_codigo')
		 						->where('personas.per_rut', '=', $per_rut)
		 						->first();
			return $roles;
		 }
			
		 public static function print_a( $algo='', $exit ){
			echo "<div style='border:1px solid red;clear:both;'><pre>";
			print_r( $algo );
			echo "</pre></div>";
			if ($exit == 0) {
				exit();
			}			
		}
		public static function array_indice($datos, $default){
			if ($default == 3){
				$array_retorno[-1] = ':: Seleccionar ::';
				$array_retorno[0] = 'Todos';
			}
			if ($default == 0){
				$array_retorno[0] = 'Todos';
			}
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
		public static function obtener_url_fija(){
			return 'http://localhost/students/public/';
		}
		public static function intentos(){
			return 3;
		}
		
		public static function alfabeto($position){
			$alfabeto[] = 'A';
			$alfabeto[] = 'B';
			$alfabeto[] = 'C';
			$alfabeto[] = 'D';
			$alfabeto[] = 'E';
			$alfabeto[] = 'F';
			$alfabeto[] = 'G';
			$alfabeto[] = 'H';
			$alfabeto[] = 'I';
			$alfabeto[] = 'J';
			$alfabeto[] = 'K';
			$alfabeto[] = 'L';
			$alfabeto[] = 'M';
			$alfabeto[] = 'N';
			$alfabeto[] = 'O';
			$alfabeto[] = 'P';
			$alfabeto[] = 'Q';
			$alfabeto[] = 'R';
			$alfabeto[] = 'S';
			$alfabeto[] = 'T';
			$alfabeto[] = 'U';
			$alfabeto[] = 'V';
			$alfabeto[] = 'W';
			$alfabeto[] = 'X';
			$alfabeto[] = 'Y';
			$alfabeto[] = 'Z';
			$alfabeto[] = 'AA';
			$alfabeto[] = 'AB';
			$alfabeto[] = 'AC';
			$alfabeto[] = 'AD';
			$alfabeto[] = 'AE';
			$alfabeto[] = 'AF';
			$alfabeto[] = 'AG';
			$alfabeto[] = 'AH';
			$alfabeto[] = 'AI';
			$alfabeto[] = 'AJ';
			$alfabeto[] = 'AK';
			$alfabeto[] = 'AL';
			$alfabeto[] = 'AM';
			$alfabeto[] = 'AN';
			$alfabeto[] = 'AO';
			$alfabeto[] = 'AP';
			$alfabeto[] = 'AQ';
			$alfabeto[] = 'AR';
			$alfabeto[] = 'AS';
			$alfabeto[] = 'AT';
			$alfabeto[] = 'AU';
			$alfabeto[] = 'AV';
			$alfabeto[] = 'AW';
			$alfabeto[] = 'AX';
			$alfabeto[] = 'AY';
			$alfabeto[] = 'AZ';
			if ($position == 0){
				return $alfabeto;
			}
			else{
				return $alfabeto[$position];
			}
			
		}
		public static function generarCodigo($longitud) {
			$key = '';
			$pattern = '1234567890abcdefghijklmnopqrstuvwxyz';
			$max = strlen($pattern)-1;
			for($i=0;$i < $longitud;$i++) $key .= $pattern{mt_rand(0,$max)};
			return $key;
		}
		
		public static function quitar_tildes($cadena) {
			$no_permitidas= array ("á","é","í","ó","ú","Á","É","Í","Ó","Ú","ñ","À","Ã","Ì","Ò","Ù","Ã™","Ã ","Ã¨","Ã¬","Ã²","Ã¹","ç","Ç","Ã¢","ê","Ã®","Ã´","Ã»","Ã‚","ÃŠ","ÃŽ","Ã”","Ã›","ü","Ã¶","Ã–","Ã¯","Ã¤","«","Ò","Ã","Ã„","Ã‹");
			$permitidas= array ("a","e","i","o","u","A","E","I","O","U","n","N","A","E","I","O","U","a","e","i","o","u","c","C","a","e","i","o","u","A","E","I","O","U","u","o","O","i","a","e","U","I","A","E");
			$texto = str_replace($no_permitidas, $permitidas ,$cadena);
			return $texto;
		}
	}