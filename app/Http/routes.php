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
/* \Event::listen('Illuminate\Database\Events\QueryExecuted', function ($query) {
	util::print_a($query->sql, 1);
	util::print_a($query->bindings, 1);
	util::print_a($query->time, 1);
});
 */
	Route::group(['middleware' =>[ 'web']], function () {
		Route::get('/', ['as' => '/', 'uses' => 'IndexController@Root']);
		Route::get('login', ['as' => 'login', 'uses' => 'IndexController@Error_login']);
		
	
		Route::get('auth/login', ['as' => 'auth/login', 'uses' => 'Auth\AuthController@authenticate']);
		Route::post('auth/login', ['as' => 'auth/login', 'uses' => 'Auth\AuthController@authenticate']);
		Route::get('logout', ['as' => 'logout', 'uses' => 'IndexController@Root']);
		Route::get('recordarpassword', ['as' => 'recordarpassword', 'uses' => 'RecordarPasswordController@RecordarPassword']);
		Route::post('enviarcorreo', ['as' => 'enviarcorreo', 'uses' => 'RecordarPasswordController@EnviarCorreo']);
	});

	Route::group(['middleware' => ['web','auth']], function () {
		Route::get('logs_applications', ['as' => 'main', 'uses' => '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index']);
		Route::get('main', ['as' => 'main', 'uses' => 'MainController@main']);
		Route::get('cambiopassword', ['as' => 'cambiopassword', 'uses' => 'CambioPasswordController@index']);
		Route::post('savepassword', ['as' => 'savepassword', 'uses' => 'CambioPasswordController@save']);
		Route::resource('regiones', 'RegionController');		
		Route::resource('roles', 'RolController');		
		Route::resource('perfiles', 'Modulo_AsignadoController');		
		Route::resource('aplicaciones', 'AplicacionController');
		Route::resource('asignaturas', 'AsignaturaController');
		Route::resource('modulos', 'ModuloController');
		Route::resource('comunas', 'ComunaController');
		Route::resource('colegios', 'ColegioController');
		Route::resource('niveles', 'NivelController');
		Route::resource('administradores', 'AdministradorController');
		Route::resource('alumnos', 'AlumnoController');
		Route::resource('apoderados', 'ApoderadoController');
		Route::resource('profesores', 'ProfesorController');
		Route::resource('cursos', 'CursoController');
		Route::resource('cargarnotas', 'CargarNotasController');
		Route::resource('malla_curricular', 'MallaCurricularController');
		Route::resource('asignaturas', 'AsignaturaController');
		Route::resource('periodos', 'PeriodoController');
		Route::resource('ver_notas', 'VerNotasController');
		Route::resource('anyos', 'AnyoController');
		
		
		
		Route::get('perfiles/modulo/{apl_codigo}/{rol_codigo}', ['as' => 'perfiles/modulo', 'uses' => 'Modulo_AsignadoController@getModulo']);
		Route::get('perfiles/{id}/modulo/{apl_codigo}/{rol_codigo}', ['as' => 'perfiles/modulo', 'uses' => 'Modulo_AsignadoController@getModulo']);
		Route::post('periodos/search', ['as' => 'periodos/search', 'uses' => 'PeriodoController@index']);
		Route::post('modulos/search', ['as' => 'modulos/search', 'uses' => 'ModuloController@index']);
		Route::post('regiones/search', ['as' => 'regiones/search', 'uses' => 'RegionController@index']);
		Route::post('roles/search', ['as' => 'roles/search', 'uses' => 'RolController@index']);
		Route::post('aplicaciones/search', ['as' => 'aplicaciones/search', 'uses' => 'AplicacionController@index']);
		Route::post('asignaturas/search', ['as' => 'asignaturas/search', 'uses' => 'AsignaturaController@index']);
		Route::post('comunas/search', ['as' => 'comunas/search', 'uses' => 'ComunaController@index']);
		Route::post('colegios/search', ['as' => 'comunas/search', 'uses' => 'ColegioController@index']);
		Route::post('niveles/search', ['as' => 'niveles/search', 'uses' => 'NivelController@index']);
		Route::post('perfiles/search', ['as' => 'perfiles/search', 'uses' => 'Modulo_AsignadoController@index']);
		Route::post('profesores/search', ['as' => 'profesores/search', 'uses' => 'ProfesorController@index']);
		Route::post('apoderados/search', ['as' => 'apoderados/search', 'uses' => 'ApoderadoController@index']);
		Route::post('cursos/search', ['as' => 'cursos/search', 'uses' => 'CursoController@index']);
		Route::post('alumnos/search', ['as' => 'alumnos/search', 'uses' => 'AlumnoController@index']);
		Route::post('malla_curricular/search_curso', ['as' => 'malla_curricular/search_curso', 'uses' => 'MallaCurricularController@index']);
		Route::post('vernotas/search_curso', ['as' => 'vernotas/search_curso', 'uses' => 'VerNotasController@index']);
		
		Route::get('cargarnotas/downloadscore/{curso}/{periodos}', ['as' => 'cargarnotas/downloadscore', 'uses' => 'CargarNotasController@exportar_calificaciones']);
		Route::post('cargarnotas/uploadscore', ['as' => 'cargarnotas/uploadscore', 'uses' => 'CargarNotasController@importar_calificaciones']);
		
		
		Route::post('cargarnotas/search_curso', ['as' => 'cargarnotas/search_curso', 'uses' => 'CargarNotasController@index']);
		Route::post('alumnos/search_curso', ['as' => 'alumnos/search_curso', 'uses' => 'AlumnoController@index']);
		Route::post('apoderados/search_curso', ['as' => 'apoderados/search_curso', 'uses' => 'ApoderadoController@index']);
		Route::get('alumnos/import/{id}', ['as' => 'alumnos/import', 'uses' => 'AlumnoController@importar_alumnos']);
		Route::post('alumnos/import/{id}', ['as' => 'alumnos/import', 'uses' => 'AlumnoController@importar_alumnos']);
		Route::post('alumnos/retirar/{id}', ['as' => 'alumnos/retirar', 'uses' => 'AlumnoController@retirar']);
		Route::get('alumnos/retirar/{id}', ['as' => 'alumnos/retirar', 'uses' => 'AlumnoController@retirar']);
		Route::get('apoderados/import/{id}', ['as' => 'apoderados/import', 'uses' => 'ApoderadoController@importar_apoderados']);
		Route::post('apoderados/import/{id}', ['as' => 'apoderados/import', 'uses' => 'ApoderadoController@importar_apoderados']);
		Route::get('alumnos/export/{id}', ['as' => 'alumnos/export', 'uses' => 'AlumnoController@exportar_alumnos']);
		Route::get('apoderados/export/{id}', ['as' => 'apoderados/export', 'uses' => 'ApoderadoController@exportar_apoderados']);
		Route::post('import_excel/alumno', ['as' => 'import_excel/alumno', 'uses' => 'AlumnoController@save_alumnos']);
		Route::post('import_excel/apoderado', ['as' => 'import_excel/apoderado', 'uses' => 'ApoderadoController@save_apoderados']);
		
		
		Route::get('malla_curricular/create/{id}', ['as' => 'malla_curricular/create', 'uses' => 'MallaCurricularController@create']);
		Route::get('alumnos/create/{id}', ['as' => 'alumnos/create', 'uses' => 'AlumnoController@create']);
		Route::get('colegios/comuna/{reg_codigo}', ['as' => 'colegios/comuna', 'uses' => 'ComunaController@getComuna']);
		Route::get('profesores/persona/{per_rut}', ['as' => 'profesores/persona', 'uses' => 'PersonaController@getRol']);
		Route::get('profesores_asignado', ['as' => 'profesores_asignado', 'uses' => 'CursoController@getProfesores']);
		Route::get('asignatura_asignado/{niv_codigo}/{cur_codigo}/{cur_numero}', ['as' => 'asignatura_asignado', 'uses' => 'AsignaturaController@getAsignaturas']);
		Route::get('asignatura_asignado_edit/{niv_codigo}/{cur_numero}', ['as' => 'asignatura_asignado_edit', 'uses' => 'AsignaturaController@getAsignaturasEdit']);
		
		
		
		Route::get('cursos_disponibles', ['as' => 'cursos_disponibles', 'uses' => 'CursoController@getCursoDisponible']);
		Route::get('cursos_disponibles_profesores/{per_rut}', ['as' => 'cursos_disponibles_profesores', 'uses' => 'CursoController@getCursoDisponibleProfesor']);
		Route::get('persona_rol/{per_rut}/{cur_codigo}', ['as' => 'persona_rol', 'uses' => 'PersonaController@getPersonaRol']);
		Route::get('cursos_mostrar/{per_rut}', ['as' => 'cursos_mostrar', 'uses' => 'VerNotasController@cursos_mostrar']);
		Route::get('alumnos_mostrar/{per_rut}/{cur_codigo}', ['as' => 'alumnos_mostrar', 'uses' => 'VerNotasController@alumnos_mostrar']);
		Route::get('notas_mostrar/{idusuario}/{per_rut}/{cur_codigo}', ['as' => 'notas_mostrar', 'uses' => 'VerNotasController@notas_mostrar']);
		
		Route::get('alumno_matriculado/{per_rut}', ['as' => 'alumno_matriculado', 'uses' => 'AlumnoController@getAlumno']);
		Route::get('alumno_retirado/{per_rut}', ['as' => 'alumno_retirado', 'uses' => 'AlumnoController@getAlumnoRetirado']);
		Route::get('alumno_administrador/{per_rut}', ['as' => 'alumno_administrador', 'uses' => 'AlumnoController@getAlumno']);
		Route::get('alumno_apoderado/{per_rut}', ['as' => 'alumno_apoderado', 'uses' => 'ApoderadoController@getApoderado_alumno']);
		Route::get('apoderado/{per_rut}', ['as' => 'alumno_apoderado', 'uses' => 'ApoderadoController@getApoderado']);
		Route::get('validar_curso/{cur_numero}/{cur_letra}/{niv_codigo}', ['as' => 'validar_curso', 'uses' => 'CursoController@getCurso']);
		Route::post('export_pdf/{per_rut}', ['as' => 'export_pdf', 'uses' => 'ExportPDFController@ExportPDF']);
		Route::get('validar_email/{e_mail}/{per_rut}', ['as' => 'validar_email', 'uses' => 'PersonaController@ValidarEmail']);
		Route::get('export_pdf/{per_rut}', ['as' => 'export_pdf', 'uses' => 'ExportPDFController@ExportPDF']);
		Route::get('anyos_encontrar/{any_codigo}', ['as' => 'anyos_encontrar', 'uses' => 'AnyoController@anyos_encontrar']);
		
		
		
	});




