<?php

namespace Routes\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Capsule\Manager as DB;

class MainModel extends Model {

	public function login($uname, $pass) {

		$sha1pass = sha1($pass);

	    $sqlUser = "SELECT usuario_id, firstname, lastname, username, nikname, email, image_avatar 
	        FROM g_usuario WHERE username = '{$uname}' and password = '{$sha1pass}'";

	    //$wsUrl = 'http://10.31.1.223:8051/ServiceAD.asmx?WSDL';
	    //$isValid = $this->loginWSAuthenticate($user, $pass, $wsUrl);
	    $isValid = 1;
	    if ($isValid) {

	        $exists_user = DB::select($sqlUser);

	        if ($exists_user) {

	            return $exists_user;

	        } else {
	            return  false;
	        }
	    }
	}

	public function listaCursos() {
		$sqlCursos = "SELECT id, description FROM g_curso WHERE visible = 1 ORDER BY description";

		return DB::select($sqlCursos);
	}

	public function listarUnidad($course_id) {
		$sqlUnidad = "SELECT id, description FROM g_unidad WHERE id_curso = '{$course_id}' ORDER BY description";

		return DB::select($sqlUnidad);
	}

	public function listaUsuarios($page, $recs, $uname, $keywr) {
		if (!isset($page) || $page == 0) {
	        $limit = 0;
	    } else {
	        $limit = ($page - 1) * $recs;
	    }

		$sqlUsuarios = "SELECT concat(firstname, ' ', lastname) as uname, nikname as usuario, username, image_avatar
			from g_usuario where username <> '{$uname}' and active = 1 and (lastname like '%{$keywr}%' or firstname 
			like '%{$keywr}%' or nikname like '%{$keywr}%') order by usuario_id LIMIT {$limit}, {$recs}";

		return DB::select($sqlUsuarios);
	}

	public function listaRetos($user, $get, $id, $page) {

		switch ($get) {
			case 'all':
				
				/********* Verifica si algún reto, enviado o recibido esta fuera de fecha ****/
	            MainModel::verificarRetoFueraFecha($user);
	            /*****************************************/

	            $sqlRetosEnviados = "SELECT r.id_reto, r.usuario_retador, r.unidad_id, r.curso_id, r.id_temageneral, r.fecha_inicio_reto, 
	                r.usuario_retado, u.nikname, r.jugado, (select image_avatar from g_usuario where username = r.usuario_retado) as avatar, 
	                time_format(timediff(r.fecha_inicio_reto + interval 1 day, now()), concat('%H', 'h', ':', '%i', 'm')) as para_ganar 
	                from g_reto r, g_usuario u where r.usuario_retado = u.username and r.usuario_retador = '{$user}' and r.jugado = 0";

	            $sqlRetosRecibidos = "SELECT  r.id_reto, r.usuario_retador, u.nikname, r.unidad_id, r.curso_id, r.id_temageneral,
	                    r.fecha_inicio_reto, r.usuario_retado, r.jugado, (select image_avatar from g_usuario where username = r.usuario_retador) 
	                    as avatar, time_format(timediff(r.fecha_inicio_reto + interval 1 day, now()), concat('%H', 'h', ':', '%i', 'm')) as 
	                    para_perder from g_reto r, g_usuario u where r.usuario_retador = u.username and r.usuario_retado = '{$user}' and r.jugado = 0";

	            $sqlRetosHistorial = "SELECT r.id_reto, r.usuario_retado as usuario, u.nikname, u.image_avatar, 'Enviado' as origen, if(r.puntaje_retador > 
	                r.puntaje_retado, 'Has ganado', 'Has perdido') as resultado from g_reto r, g_usuario u where r.usuario_retado = u.username 
	                and r.usuario_retador = '{$user}' and r.jugado = 1 union select r.id_reto, r.usuario_retador as usuario, 
	                u.nikname, u.image_avatar, 'Recibido' as origen, if(r.puntaje_retado > r.puntaje_retador, 'Has ganado', 'Has perdido') as resultado 
	                from g_reto r, g_usuario u where r.usuario_retador = u.username and r.usuario_retado = '{$user}' and r.jugado = 1 
	                order by id_reto desc limit 0, 10";

	            $json->Enviado = DB::select($sqlRetosEnviados);
	            $json->Recibido = DB::select($sqlRetosRecibidos);
	            $json->Historial = DB::select($sqlRetosHistorial);
				break;

			case 'history':

				/********* Verifica si algún reto, enviado o recibido esta fuera de fecha ****/
	            MainModel::verificarRetoFueraFecha($user);
	            /*****************************************/

	            if(!(isset($page)) || $page == '0') {
	                $limite = 0;
	            } else {
	                $limite = ($page - 1) * 10;
	            }

	            $sqlRetosHistorial = "SELECT r.id_reto, r.usuario_retado as usuario, u.nikname, u.image_avatar, 'Enviado' as origen, 
	            	if(r.puntaje_retador > r.puntaje_retado, 'Has ganado', 'Has perdido') as resultado from g_reto r, g_usuario u where 
	            	r.usuario_retado = u.username and r.usuario_retador = '{$user}' and r.jugado = 1 union select r.id_reto, r.usuario_retador 
	            	as usuario, u.nikname, u.image_avatar, 'Recibido' as origen, if(r.puntaje_retado > r.puntaje_retador, 'Has ganado', 'Has perdido') 
	                as resultado from g_reto r, g_usuario u where r.usuario_retador = u.username and r.usuario_retado = '{$user}' and r.jugado = 1 
	                order by id_reto desc limit {$limite}, 10";

	            $json->Historial = DB::select($sqlRetosHistorial);
	            break;
			
			default:
				$sqlDetalle = "SELECT r.id_reto, (select nikname from g_usuario where username = '{$user}') as myNik, (select image_avatar 
		            from g_usuario where username = '{$user}') as myAvatar, r.usuario_retado as rival, u.nikname, u.image_avatar, 'Enviado' 
		            as origen, if(r.puntaje_retador > r.puntaje_retado, 'Has ganado', 'Has perdido') as resultado, r.correctas_retador as 
		            mis_correctas, r.puntaje_retador as mi_punto, r.correctas_retado as correctas_rival, r.puntaje_retado as punto_rival, 
		            time_format(timediff(r.fecha_fin_reto, r.fecha_inicio_reto), concat('%i', 'm ', '%s', 's')) as miTiempo, time_format(timediff
		            (r.fecha_fin_juego, r.fecha_inicio_juego), concat('%i', 'm ', '%s', 's')) as tiempoRival from g_reto r, g_usuario u
		             where r.usuario_retado = u.username and r.usuario_retador = '{$user}' and r.jugado = 1 and r.id_reto = {$id}
		            union
		            select r.id_reto, (select nikname from g_usuario where username = '{$user}') as myNik, (select image_avatar from g_usuario 
		            where username = '{$user}') as myAvatar, r.usuario_retador as rival, u.nikname, u.image_avatar, 'Recibido' as origen, 
		            if(r.puntaje_retado > r.puntaje_retador, 'Has ganado', 'Has perdido') as resultado, r.correctas_retado as mis_correctas, 
		            r.puntaje_retado as mi_punto, r.correctas_retador as correctas_retado, r.puntaje_retador as punto_rival, time_format(timediff
		            (r.fecha_fin_juego, r.fecha_inicio_juego), concat('%i', 'm ', '%s', 's')) as miTiempo, time_format(timediff(r.fecha_fin_reto, 
		            r.fecha_inicio_reto), concat('%i', 'm ', '%s', 's')) as tiempoRival from g_reto r, g_usuario u where r.usuario_retador = 
		            u.username and r.usuario_retado = '{$user}' and r.jugado = 1 and r.id_reto = {$id} order by id_reto";

	            $json->Detalle = DB::select($sqlDetalle);
	            break;
		}

		return $json;
	}

	public function verificarRetoFueraFecha($user){
		$sqlVerifica = "SELECT id_reto, correctas_retador as correctas, if(unix_timestamp(fecha_inicio_reto + interval 1 day) - 
        unix_timestamp(now()) <= 0, 'yes', 'not') as actualizar from g_reto where usuario_retador = '{$user}' and jugado = 0
        union
        select id_reto, correctas_retador as correctas, if(unix_timestamp(fecha_inicio_reto + interval 1 day) - 
        unix_timestamp(now()) <= 0, 'yes', 'not') as actualizar from g_reto where usuario_retado = '{$user}' and jugado = 0";

	    $queryVerifica = DB::select($sqlVerifica);
	    
	    for ($i = 0; $i < count($queryVerifica); $i++) {
	        if ($queryVerifica[$i]->actualizar == "yes") {

	            $idreto = $queryVerifica[$i]->id_reto;
	            $punto_retador = ($queryVerifica[$i]->correctas >= 1) ? 5 : 0;

	            $sqlUpdate = "UPDATE g_reto set puntaje_retador = '{$punto_retador}', fecha_inicio_juego = now(), fecha_fin_juego = 
	                now(), correctas_retado = '0', puntaje_retado = '0', jugado = 1 where id_reto = '{$idreto}'";

	            DB::update($sqlUpdate);
	        }
	    }
	}

	public function cargarPreguntas($course, $unidad) {
		$sql = "SELECT id_preguntas, preguntas FROM g_preguntas WHERE 
            course_id = '{$course}' and id_unidad = '{$unidad}' order by rand() limit 5";

	    $query = DB::select($sql);

	    for ($i = 0; $i < count($query); $i++) {

	        $question_id = $query[$i]->id_preguntas;

	        $sql_answer = "SELECT * FROM g_respuestas WHERE id_pregunta = '{$question_id}'";

	        $queryAnswer = DB::select($sql_answer);

	        $query[$i]->Indice = $i;
	        $query[$i]->Respuesta = $queryAnswer;
	    }

	    return $query;
	}

	public function insertarRespuestaUsuario($idreto, $username, $courseid, $unidadid, $generalt, $pregunta, $respuest) {
		$sqlInsert = "INSERT INTO g_respuesta_usuario (username, course_id, unidad_id, id_temageneral, pregunta_id, respuesta_id, id_reto)
        values ('{$username}', '{$courseid}', '{$unidadid}', '{$generalt}', '{$pregunta}', '{$respuest}', '{$idreto}')";

        return DB::insert($sqlInsert);
	}

	public function resumenJuego($id){
		$sqlResumen = "SELECT (select nikname from g_usuario where username = usuario_retador) as nikRetador, (select 
			image_avatar from g_usuario where username = usuario_retador) as myAvatar, (select nikname from g_usuario where 
			username = usuario_retado) as nikRetado, (select image_avatar from g_usuario where username = usuario_retado) 
			as avatarRetado, correctas_retador, if(correctas_retado = 0, '', correctas_retado) as correctas_retado, 
			if(fecha_fin_reto = '0000-00-00 00:00:00', 'Cancelado', time_format(timediff(fecha_fin_reto, fecha_inicio_reto), 
			concat('%im ', '%ss'))) as tiempo_juego_retador, if(jugado <> 0, time_format(timediff(fecha_fin_juego, 
			fecha_inicio_juego), concat('%im ', '%ss')), 'Pendiente') as tiempo_juego_retado, if(jugado = 0, 
			time_format(timediff(fecha_inicio_reto + interval 1 day, now()), concat('Faltan %Hh ', '%im para ganar')), 
	        'Juego Finalizado') as para_ganar from g_reto where id_reto = {$id}";

	    $json->Resumen = DB::select($sqlResumen);

	    return $json;
	}

	public function rankingMensual($course, $year, $month) {
		 $sqlRanking = "SELECT u.nikname, u.image_avatar, r.* from g_ranking r, g_usuario u where u.username = r.usuario_id 
		 	and r.curso_id = '{$course}' and r.year = '{$year}' and r.month = '{$month}' order by r.puntaje desc, 
		 	r.tiempo_jugado desc";

		return DB::select($sqlRanking);
	}

	public function burbujaRetos($uname) {
		$sqlBuble = "SELECT count(*) retos from g_reto where usuario_retado = '{$uname}' and jugado = 0";

		return DB::select($sqlBuble);
	}

	public function insertarRetos($id_reto, $uretador, $unidadId, $courseId, $uretado, $idTemageneral, $fecha_inicio) {

		if($id_reto == "") {

			$sqlInsert = DB::table('g_reto')->insertGetId(
				[
					'usuario_retador' => $uretador,
					'unidad_id' => $unidadId,
					'curso_id' => $courseId,
					'id_temageneral' => $idTemageneral,
					'fecha_inicio_reto' => $fecha_inicio,
					'usuario_retado' => $uretado
				]
			);

	        return $sqlInsert;
	    }
	}

	public function actualizaRetos($cancelled, $ujugador, $countCorrect, $idQuestion, $fecha_fin) {
		// Obteniendo datos de los usuarios que jugaron por cada reto
		
		$sqlGetRecord = DB::table('g_reto')
						->select(DB::raw('*, year(fecha_inicio_reto) as anio, month(fecha_inicio_reto) as month'))
						->where('id_reto', '=', $idQuestion)->get();

		$anio = $sqlGetRecord[0]->anio;
	    $month = $sqlGetRecord[0]->month;
	    $uRetador = $sqlGetRecord[0]->usuario_retador;
	    $uRetado = $sqlGetRecord[0]->usuario_retado;
	    $uFechaIn = $sqlGetRecord[0]->fecha_inicio_reto;
	    $unidadId = $sqlGetRecord[0]->unidad_id;


	    if($cancelled == "") {

	    	$sqlUpdate = DB::table('g_reto')
	    				->where('id_reto', $idQuestion)
	    				->update(
	    					[
	    						'correctas_retador' => $countCorrect,
	    						'fecha_fin_reto' => $fecha_fin
	    					]
	    				);

	    	if($uRetado == $ujugador) {
	    		$sqlUpdate = DB::table('g_reto')
	    				->where('id_reto', $idQuestion)
	    				->update(
	    					[
	    						'correctas_retado' => $countCorrect,
	    						'fecha_fin_juego' => $fecha_fin,
	    						'jugado' => 1
	    					]
	    				);

	    	}

	    	if($sqlUpdate && $uRetado == $ujugador) {

	    		$queryRecordRate = DB::table('g_reto')
	    							->select(DB::raw('timediff(fecha_fin_reto, fecha_inicio_reto) as tiempo_retador, 
	    									correctas_retador, timediff(fecha_fin_juego, fecha_inicio_juego) 
	    									as tiempo_retado, correctas_retado'))
	    							->where('id_reto', '=', $idQuestion)->get();

	    		//Retador
	            $puntajeRetador = $queryRecordRate[0]->correctas_retador;
	            $tiempoRetador = $queryRecordRate[0]->tiempo_retador;

	            // Retado
	            $puntajeRetado = $queryRecordRate[0]->correctas_retado;
	            $tiempoRetado = $queryRecordRate[0]->tiempo_retado;

	            // Consultamos la cantidad de respuestas correctas y el tiempo jugado
	            // para asignarle los puntajes a cada uno.

	            $pRetador = 5;
	            $pRetado = 1;

	            if($puntajeRetador == $puntajeRetado) {

	            	if ($tiempoRetador == $tiempoRetado) {

	            		$sqlUpdatePuntos = DB::table('g_reto')
	            							->where('id_reto', $idQuestion)
	            							->update(

	            								[
	            									'puntaje_retador' => 5,
	            									'puntaje_retado' => 2
	            								]
	            							);
	            	} else {

	            		$pRetador = 1;
                    	$pRetado = 5;

	            		$sqlUpdatePuntos = DB::table('g_reto')
	            							->where('id_reto', $idQuestion)
	            							->update(

	            								[
	            									'puntaje_retador' => 2,
	            									'puntaje_retado' => 5
	            								]
	            							);

	            	}

	            } elseif($puntajeRetador > $puntajeRetado) {

	            	$sqlUpdatePuntos = DB::table('g_reto')
	            						->where('id_reto', $idQuestion)
	            						->update(

	            								[
	            									'puntaje_retador' => 5,
	            									'puntaje_retado' => 2
	            								]
	            							);
	            } else {

	            	$pRetador = 1;
                	$pRetado = 5;

                	$sqlUpdatePuntos = DB::table('g_reto')
	            						->where('id_reto', $idQuestion)
	            						->update(

	            								[
	            									'puntaje_retador' => 2,
	            									'puntaje_retado' => 5
	            								]
	            							);
	            }

	            // Funcion que actualiza el ranking mensual.
	            
	            MainModel::actualizaRanking($uRetador, $uRetado, $uFechaIn, $idQuestion, $unidadId, $pRetador, $pRetado, $tiempoRetador, $tiempoRetado);

	    	} else {

	    		$course = $sqlGetRecord[0]->curso_id;

	    		$sqlRangking = DB::table('g_reto')
	    						->where()
	    						->get();
	    	}

	    }
		return $cancelled;
	}

}