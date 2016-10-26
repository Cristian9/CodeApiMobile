<?php


namespace Routes\Controller;

use Routes\Models\MainModel;

class MainController extends Controller {

	public function login($request, $response) {

		$uname = $request->getParam('user');
		$passw = $request->getParam('pass');

		$user = MainModel::login($uname, $passw);

		echo json_encode($user);
	}

	public function listarCursos() {
		$courses = MainModel::listaCursos();

		echo json_encode($courses);
	}

	public function listarUnidad($request, $reqponse) {
		$course_id = $request->getParam('courseId');

		$unidad_course = MainModel::listarUnidad($course_id);

		echo json_encode($unidad_course);
	}

	public function listaUsuarios($request, $response) {
		$page = $request->getParam('page');
		$recs = 30;
		$uname = $request->getParam('username');
		$keywr = $request->getParam('keywords');

		$usuarios = MainModel::listaUsuarios($page, $recs, $uname, $keywr);

		echo json_encode($usuarios);
	}

	public function listaRetos($request, $response) {
		$args = $request->getParam('args');
		$get = $request->getParam('get');
		$id = $request->getParam('id');
		$page = $request->getParam('page');

		$retos = MainModel::listaRetos($args, $get, $id, $page);

		echo json_encode($retos);
	}

	public function cargarPreguntas($request, $response) {
		$course = $request->getParam('course');
		$unidad = $request->getParam('unidad');

		$preguntas = MainModel::cargarPreguntas($course, $unidad);

		echo json_encode($preguntas);
	}

	public function insertarRespuestaUsuario($request, $response) {
		$idreto         = $request->getParam('reto_id');
        $user           = $request->getParam('username');
        $courseid       = $request->getParam('courseid');
        $unidadid       = $request->getParam('unidadid');
        $temageneralid  = $request->getParam('generalt');
        $preguntaid     = $request->getParam('pregunta');
        $respuestaid    = $request->getParam('respuest');

        $result = MainModel::insertarRespuestaUsuario($idreto, $user, $courseid, $unidadid, $temageneralid, $preguntaid, $respuestaid);

        echo $result;
	}

	public function resumenJuego($request, $response) {
		
	}

	public function getYearAndMonth() {
		$currentYear = date('Y');
	    $pastYear = $currentYear - 3;
	    
	    for($pastYear; $pastYear <= $currentYear; $pastYear++) {
	        $year[$pastYear] = $pastYear;
	    }

	    $months = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

	    for($month = 1; $month < count($months); $month++) {
	        $meses[$month] = $months[$month];
	    }

	    $json->year = $year;
	    $json->year['selected'] = date('Y');
	    $json->mes = $meses;
	    $json->mes['selected'] = date('n');

	    echo json_encode($json);
	}

}