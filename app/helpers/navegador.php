<?php
namespace App\helpers;

use DB;
use Session;
use App\helpers\util;
use App\models\persona;


class navegador{
	public static function crear_menu( $rut, $enable = true ){
		$opciones = DB::table('asignaciones')
		->join('roles', 'asignaciones.rol_codigo', '=', 'roles.rol_codigo')
		->join('modulos_asignados', 'roles.rol_codigo', '=', 'modulos_asignados.rol_codigo')
		->join('modulos', 'modulos_asignados.mod_codigo', '=', 'modulos.mod_codigo')
		->join('aplicaciones', 'modulos.apl_codigo', '=', 'aplicaciones.apl_codigo')
		->where('asignaciones.per_rut', '=', $rut)
		->where('modulos_asignados.mas_read', '=', 1)
		->where('modulos_asignados.mas_activo', '=', 1)
		->where('aplicaciones.apl_activo', '=', 1)
		->where('modulos.mod_activo', '=', 1)
		->select('aplicaciones.apl_descripcion', 'modulos.mod_nombre', 'modulos.mod_url')
		->orderBy('apl_orden', 'ASC')
		->orderBy('mod_orden', 'ASC');
//		$consulta = $opciones->tosql();
//		util::print_a($consulta,0);
		$opciones = $opciones->get();
		$persona = persona::join('asignaciones', 'personas.per_rut', '=', 'asignaciones.per_rut')
						->join('roles', 'asignaciones.rol_codigo', '=', 'roles.rol_codigo')
						->where('personas.per_rut', '=', $rut)
						->first();
		$menu = '<ul class="nav navbar-nav">';
		$aplicacion = '';
		if ($enable){
			foreach ($opciones as $opcion){
				if ($aplicacion != $opcion->apl_descripcion){
					if ($aplicacion != ''){
						$menu .= '</ul>';
						$menu .= '</li>';
					}
					$aplicacion = $opcion->apl_descripcion;
					$menu .= '<li class="dropdown">';
					$menu .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">'.$aplicacion.'<span class="caret"></span></a>';
					$menu .= '<ul class="dropdown-menu" role="menu">';
				}
				if ($opcion->mod_nombre == '**---**'){
					$menu .= '<li class="divider"></li>';
				}	
				else {
					$menu .= '<li>';
					$menu .= '<a href="/'.util::obtener_url().$opcion->mod_url.'">'.$opcion->mod_nombre.'</a>';
					$menu .= '</li>';
				}
			}
			$menu .= '</ul>';
			$menu .= '</li>';
		}
		$menu .= '</ul>';
		$menu .= '<ul class="nav navbar-nav navbar-right">';
		$menu .= '<li class="dropdown">';
		$menu .= '<a href="#" class="dropdown-toggle icon-user" data-toggle="dropdown" role="button" aria-expanded="false">Usuario<span class="caret"></span></a>';
		$menu .= '<ul class="dropdown-menu" role="menu">';
		$menu .= '<li style="padding-left:10px;">';
		$menu .= 'Usuario:';
		$menu .= '</li>';
		$menu .= '<li style="padding-left:20px;">';
		$menu .= $persona->per_nombre.' '.$persona->per_apellido_paterno;
		$menu .= '</li>';
		$menu .= '<li class="divider"></li>';
		$menu .= '<li style="padding-left:10px;">';
		$menu .= 'Perfil:';
		$menu .= '</li>';
		$menu .= '<li style="padding-left:20px;">';
		$menu .= $persona->rol_nombre;
		$menu .= '</li>';
		$ruta = '';
		if (!$enable){
			$ruta = '../';
		}
		$menu .= '<li class="divider"></li>';
		$menu .= '<li style="padding-left:10px;">';
		$menu .= '<a href="'.$ruta.'cambiopassword">Cambiar Password</a>';
		$menu .= '</li>';
		$menu .= '<li class="divider"></li>';
		$menu .= '<li>';
		$menu .= '<a href="'.$ruta.'logout">Salir</a>';
		$menu .= '</li>';
		$menu .= '</ul>';
		$menu .= '</li>';
		
		$menu .= '</ul>';		
		return $menu;	
	}
	public static function privilegios( $rut, $modulo){
		$opcion = DB::table('asignaciones')
		->join('roles', 'asignaciones.rol_codigo', '=', 'roles.rol_codigo')
		->join('modulos_asignados', 'roles.rol_codigo', '=', 'modulos_asignados.rol_codigo')
		->join('modulos', 'modulos_asignados.mod_codigo', '=', 'modulos.mod_codigo')
		->join('aplicaciones', 'modulos.apl_codigo', '=', 'aplicaciones.apl_codigo')
		->where('asignaciones.per_rut', '=', $rut)
		->where('aplicaciones.apl_activo', '=', 1)
		->where('modulos.mod_activo', '=', 1)
		->where('roles.rol_activo', '=', 1)
		->where('modulos_asignados.mas_activo', '=', 1)
		->where('modulos.mod_nombre', '=', $modulo)
		->select('modulos_asignados.mas_codigo', 'modulos_asignados.mas_add', 'modulos_asignados.mas_read', 
				'modulos_asignados.mas_edit', 'modulos_asignados.mas_delete', 'modulos_asignados.mas_especial',
				'roles.rol_nombre', 'asignaciones.per_rut')
		->orderBy('apl_orden', 'ASC')
		->orderBy('mod_orden', 'ASC')
		->get();
		return $opcion;
	}
}
