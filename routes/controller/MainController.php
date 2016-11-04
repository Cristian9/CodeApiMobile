<?php


namespace Routes\Controller;

use Routes\Models\MainModel;

class MainController extends Controller {

	public function getToken($request, $response) {
		
		$jsonToken->$_SESSION['name'] = $_SESSION['value'];
		
		$jsonToken->$_SESSION['csrf_name'] = $_SESSION['csrf_value'];

		echo json_encode($jsonToken);
	}

	public function login($request, $response) {

		/*$uname = $request->getParam('user');
		$passw = $request->getParam('pass');

		$user = MainModel::login($uname, $passw);

		echo json_encode($user);*/
		die('ok');
	}

	public function listarCursos($request, $response) {
		
		$courses = MainModel::listaCursos();

		echo json_encode($courses);
	}

	public function listarUnidad($request, $response) {
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
		$id = $request->getParam('id');

		$resumen = MainModel::resumenJuego($id);

		echo json_encode($resumen);
	}

	public function rankingMensual($request, $response) {
		$course = $request->getParam('courseId');
	    $year = $request->getParam('year');
	    $month = $request->getParam('month');

	    $rankign = MainModel::rankingMensual($course, $year, $month);

	    echo json_encode($rankign);
	}

	public function burbujaRetos($request, $response) {
		$uname = $request->getParam('uname');

		$buble = MainModel::burbujaRetos($uname);

		echo json_encode($buble);
	}

	public function insertarRetos($request, $response) {
		$id_reto = $request->getParam('id_reto');
	    $uretador = $request->getParam('user_retador');
	    $unidadId = $request->getParam('unidad_id');
	    $courseId = $request->getParam('courseId');
	    $uretado = $request->getParam('user_retado');
	    $idTemageneral = $request->getParam('id_temageneral');

	    $fecha_inicio = date('Y-m-d H:i:s');

	    $result = MainModel::insertarRetos($id_reto, $uretador, $unidadId, $courseId, $uretado, $idTemageneral, $fecha_inicio);

	    echo $result;
	}

	public function actualizaRetos($request, $response) {
		$ujugador = $request->getParam('username');
	    $countCorrect = $request->getParam('countCorrect');
	    $idQuestion = $request->getParam('idQuestion');
	    $cancelled = $request->getParam('cancelled');
	    $fecha_fin = date('Y-m-d H:i:s');

	    $result = MainModel::actualizaRetos($cancelled, $ujugador, $countCorrect, $idQuestion, $fecha_fin);

	    print_r($result);
	}

	public function UpdateFechaRetos($request, $response) {
		$id = $request->getParam('idReto');
		$fecha_inicio = date('Y-m-d H:i:s');

		$result = MainModel::UpdateFechaRetos($id, $fecha_inicio);

		echo $result;
	}

	public function ObtenerPerfil($request, $response) {
		$username = $request->getParam('username');

		$profile = MainModel::ObtenerPerfil($username);

		echo json_encode($profile);
	}

	public function ActualizaUsuario($request, $response) {
		$userid = $request->getParam('userid');
		$nik = $request->getParam('niknam');
		$img = $request->getParam('image');

		$result = MainModel::ActualizaUsuario($userid, $nik, $img);

		echo $result;
	}

	public function InsertaCodeDispositivo($request, $response) {
		$userid = $request->getParam('userid');
		$identifier = $request->getParam('identifier');

		$result = MainModel::InsertaCodeDispositivo($userid, $identifier);

		echo $result;
	}

	public function DeleteRetoFallado($request, $response) {
		$lastID = $request->getParam('lastID');
		$uname = $request->getParam('uname');

		$result = MainModel::DeleteRetoFallado($lastID, $uname);

		echo $result;
	}

	public function Notificacion($request, $response) {
		$toUser = $request->getParam('toUser');
		$fromUser = $request->getParam('fromUser');

		$result = MainModel::Notificacion($toUser, $fromUser);

		print_r($result);
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