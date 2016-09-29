<?php

use App\helpers\util;
use App\models\roles;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/


	Route::group(['middleware' =>[ 'web']], function () {
		Route::get('/', ['as' => '/', 'uses' => 'IndexController@Root']);
		Route::get('login', ['as' => 'login', 'uses' => 'IndexController@Error_login']);
		
		Route::get('admin', ['as' => 'admin', 'uses' => 'IndexController@Admin']);
	
		Route::get('auth/login', ['as' => 'auth/login', 'uses' => 'Auth\AuthController@authenticate']);
		Route::post('auth/login', ['as' => 'auth/login', 'uses' => 'Auth\AuthController@authenticate']);
		Route::get('logout', ['as' => 'logout', 'uses' => 'IndexController@Root']);
	});

	Route::group(['middleware' => ['web','auth']], function () {
		Route::get('main', ['as' => 'main', 'uses' => 'MainController@main']);
		Route::resource('regiones', 'RegionController');		
		Route::resource('roles', 'RolController');		
		Route::resource('perfiles', 'Modulo_AsignadoController');		
		Route::resource('aplicaciones', 'AplicacionController');
		Route::resource('modulos', 'ModuloController');
		Route::resource('comunas', 'ComunaController');
		Route::resource('colegios', 'ColegioController');
		Route::resource('niveles', 'NivelController');
		Route::resource('administradores', 'AdministradorController');
		Route::resource('alumnos', 'AlumnoController');
		Route::resource('apoderados', 'ApoderadoController');
		Route::resource('profesores', 'ProfesorController');
		Route::resource('cursos', 'CursoController');
		Route::resource('asignaturas', 'AsignaturaController');
		Route::get('perfiles/modulo/{apl_codigo}/{rol_codigo}', ['as' => 'perfiles/modulo', 'uses' => 'Modulo_AsignadoController@getModulo']);
		Route::get('perfiles/{id}/modulo/{apl_codigo}/{rol_codigo}', ['as' => 'perfiles/modulo', 'uses' => 'Modulo_AsignadoController@getModulo']);
		Route::post('modulos/search', ['as' => 'modulos/search', 'uses' => 'ModuloController@index']);
		Route::post('regiones/search', ['as' => 'regiones/search', 'uses' => 'RegionController@index']);
		Route::post('roles/search', ['as' => 'roles/search', 'uses' => 'RolController@index']);
		Route::post('aplicaciones/search', ['as' => 'aplicaciones/search', 'uses' => 'AplicacionController@index']);
		Route::post('comunas/search', ['as' => 'comunas/search', 'uses' => 'ComunaController@index']);
		Route::post('colegios/search', ['as' => 'comunas/search', 'uses' => 'ColegioController@index']);
		Route::post('niveles/search', ['as' => 'niveles/search', 'uses' => 'NivelController@index']);
		Route::post('perfiles/search', ['as' => 'perfiles/search', 'uses' => 'Modulo_AsignadoController@index']);
		Route::post('profesores/search', ['as' => 'profesores/search', 'uses' => 'ProfesorController@index']);
		Route::post('cursos/search', ['as' => 'cursos/search', 'uses' => 'CursoController@index']);
		Route::post('alumnos/search', ['as' => 'alumnos/search', 'uses' => 'AlumnoController@index']);
		Route::post('asignaturas/search_curso', ['as' => 'asignaturas/search_curso', 'uses' => 'AsignaturaController@index']);
		Route::post('alumnos/search_curso', ['as' => 'alumnos/search_curso', 'uses' => 'AlumnoController@index']);
		Route::post('apoderados/search_curso', ['as' => 'apoderados/search_curso', 'uses' => 'ApoderadoController@index']);
		Route::get('alumnos/import/{id}', ['as' => 'alumnos/import', 'uses' => 'AlumnoController@importar_alumnos']);
		Route::post('alumnos/import/{id}', ['as' => 'alumnos/import', 'uses' => 'AlumnoController@importar_alumnos']);
		Route::get('apoderados/import/{id}', ['as' => 'apoderados/import', 'uses' => 'ApoderadoController@importar_apoderados']);
		Route::post('apoderados/import/{id}', ['as' => 'apoderados/import', 'uses' => 'ApoderadoController@importar_apoderados']);
		Route::get('alumnos/export/{id}', ['as' => 'alumnos/export', 'uses' => 'AlumnoController@exportar_alumnos']);
		Route::get('apoderados/export/{id}', ['as' => 'apoderados/export', 'uses' => 'ApoderadoController@exportar_apoderados']);
		Route::post('import_excel/alumno', ['as' => 'import_excel/alumno', 'uses' => 'AlumnoController@save_alumnos']);
		Route::post('import_excel/apoderado', ['as' => 'import_excel/apoderado', 'uses' => 'ApoderadoController@save_apoderados']);
		
		
		Route::get('asignaturas/create/{id}', ['as' => 'asignaturas/create', 'uses' => 'AsignaturaController@create']);
		Route::get('alumnos/create/{id}', ['as' => 'alumnos/create', 'uses' => 'AlumnoController@create']);
		Route::get('colegios/comuna/{reg_codigo}', ['as' => 'colegios/comuna', 'uses' => 'ComunaController@getComuna']);
		Route::get('profesores/persona/{per_rut}', ['as' => 'profesores/persona', 'uses' => 'ProfesorController@getRol']);
		Route::get('profesores_asignado', ['as' => 'profesores_asignado', 'uses' => 'CursoController@getProfesores']);
		Route::get('cursos_disponibles', ['as' => 'cursos_disponibles', 'uses' => 'CursoController@getCursoDisponible']);
		
		Route::get('alumno_matriculado/{per_rut}', ['as' => 'alumno_matriculado', 'uses' => 'AlumnoController@getAlumno']);
		Route::get('alumno_apoderado/{per_rut}', ['as' => 'alumno_apoderado', 'uses' => 'ApoderadoController@getApoderado_alumno']);
		Route::get('apoderado/{per_rut}', ['as' => 'alumno_apoderado', 'uses' => 'ApoderadoController@getApoderado']);
		Route::get('validar_curso/{cur_numero}/{cur_letra}/{niv_codigo}', ['as' => 'validar_curso', 'uses' => 'CursoController@getCurso']);
	});


