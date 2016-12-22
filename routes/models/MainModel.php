<?php

namespace Routes\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Capsule\Manager as DB;

class MainModel extends Model {

	public function login($uname) {

	    $sqlUser = "SELECT usuario_id, firstname, lastname, username, nikname, email, image_avatar
	        FROM g_usuario WHERE username = '{$uname}'";

	    $exists_user = DB::select($sqlUser);

	    return $exists_user;
	}

	public function listaCursos() {
		$sqlCursos = DB::table('g_curso')
					->select('id', 'description')
					->where('visible', '=', 1)
					->get();

		return $sqlCursos;
	}

	public function listarUnidad($course_id) {
		$sqlUnidad = DB::table('g_unidad')
					->select('id', 'description')
					->where('id_curso', '=', $course_id)
					->orderBy('description', 'asc')
					->get();

		return $sqlUnidad;
	}

	public function listaUsuarios($page, $recs, $uname, $keywr) {
		if (!isset($page) || $page == 0) {
	        $limit = 0;
	    } else {
	        $limit = ($page - 1) * $recs;
	    }

		$sqlUsuarios = "SELECT concat(firstname, ' ', lastname) as uname, nikname as usuario, username, image_avatar, 
			if(device_notification_id = '', 'nodevice', device_notification_id) install, email from g_usuario left join g_ranking r 
			on username = r.usuario_id where username <> '{$uname}' and active = 1 and (lastname like '%{$keywr}%' or firstname
			like '%{$keywr}%' or nikname like '%{$keywr}%' or username like '%{$keywr}%' or concat(firstname, ' ', lastname) 
			like '%{$keywr}%') group by username order by r.tiempo_jugado desc, r.puntaje desc LIMIT {$limit}, {$recs}";

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

	            $sqlUpdate = "UPDATE g_reto set fecha_inicio_juego = now(), fecha_fin_juego = now(), 
	            	correctas_retado = '0', puntaje_retado = '0', jugado = 1 where id_reto = '{$idreto}'";

	            DB::update($sqlUpdate);

	            $getResumenReto = DB::table('g_reto')
	    				->select(DB::raw('id_reto, usuario_retador, usuario_retado, fecha_inicio_reto, 
	    								unidad_id, puntaje_retador, puntaje_retado, timediff(fecha_fin_reto, 
	    								fecha_inicio_reto) as tiempo_retador, timediff(fecha_fin_juego, fecha_inicio_juego) 
	    								as tiempo_retado'))
	    				->where(
	    					[
	    						['id_reto', '=', $idreto],
	    						['jugado', '=', 1]
	    					]
	    				)->get();

			    $retado 		= 	$getResumenReto[$i]->usuario_retado;
			    $idreto 		= 	$getResumenReto[$i]->id_reto;
			    $unidadId 		= 	$getResumenReto[$i]->unidad_id;
			    $pRetado 		= 	$getResumenReto[$i]->puntaje_retado;

			    MainModel::actualizaRanking($retado, $user, $idreto, $unidadId, $pRetado);
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

		$sqlBuble = DB::table('g_reto')
					->select(DB::raw('count(*) retos'))
					->where(
						[
							['usuario_retado', '=', $uname],
							['jugado', '=', 0]
						]
					)->get();

		return $sqlBuble;

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
						->where('id_reto', '=', $idQuestion)->get();

	    $uRetado = $sqlGetRecord[0]->usuario_retado;
	    $unidadId = $sqlGetRecord[0]->unidad_id;

	    if($cancelled == "") {

	    	if($uRetado == $ujugador) {

	    		$sqlUpdate = DB::table('g_reto')
	    				->where('id_reto', $idQuestion)
	    				->update(
	    					[
	    						'correctas_retado' => $countCorrect,
	    						'puntaje_retado' => $countCorrect,
	    						'fecha_fin_juego' => $fecha_fin,
	    						'jugado' => 1
	    					]
	    				);

	    	} else {

	    		$sqlUpdate = DB::table('g_reto')
	    				->where('id_reto', $idQuestion)
	    				->update(
	    					[
	    						'correctas_retador' => $countCorrect,
	    						'puntaje_retador' => $countCorrect,
	    						'fecha_fin_reto' => $fecha_fin
	    					]
	    				);
	    	}

	    	// Funcion que actualiza el ranking mensual.
	    	
	    	if($sqlUpdate) {

	    		MainModel::actualizaRanking($uRetado, $ujugador, $idQuestion, $unidadId, $countCorrect);
	    	}

	    	return $sqlUpdate;

	    } else {

    		$course = $sqlGetRecord[0]->curso_id;

    		$sqlRanking = DB::table('g_ranking')
    						->where(
    							[
    								['usuario_id', '=', $ujugador],
    								['curso_id', '=', $course],
    								['year', '=', date('Y')],
    								['month', '=', date('m')]
    							]
    						)->get();

    		if(!empty($sqlRanking[0])) {

    			$id = $sqlRanking[0]->id_ranking;

    			$sqlRestaPuntajeRanking = "UPDATE g_ranking set puntaje = if((puntaje - 3) < 0, 0, (puntaje - 3)) where id_ranking = '{$id}'";

    			$sqlUpdateCancelled = DB::update($sqlRestaPuntajeRanking);

    			if($uRetado == $ujugador) {

    				$sqlSetReto = DB::table('g_reto')
    						->where('id_reto', $idQuestion)
    						->update(
    							[
    								'puntaje_retado' => 0,
									'fecha_fin_juego' => $fecha_fin,
									'jugado' => 1
								]
							);

    			} else {
    				DB::table('g_reto')->where('id_reto', '=', $idQuestion)->delete();
    			}

    			return $sqlUpdateCancelled;
    		}

    		return true;
	    }
	}

	public function actualizaRanking($retado, $username, $idreto, $unidadId, $puntaje) {

		$rawQuery = 'curso_id, timediff(fecha_fin_reto, fecha_inicio_reto) as tiempo';

		if($username == $retado) {
			$rawQuery = 'curso_id, timediff(fecha_fin_juego, fecha_inicio_juego) as tiempo';
		}

		$sqlVerificaFecha = DB::table('g_reto')
							->select(DB::raw($rawQuery))
							->where('id_reto', '=', $idreto)->get();

		$course = $sqlVerificaFecha[0]->curso_id;

		$tiempo = $sqlVerificaFecha[0]->tiempo;

		$sqlRanking = DB::table('g_ranking')
					->where(
						[
							['usuario_id', '=', $username],
							['curso_id', '=', $course],
							['year', '=', date('Y')],
							['month', '=', date('m')]
						]
					)->get();

		if(!empty($sqlRanking[0])) {
			$id = $sqlRanking[0]->id_ranking;

			$setRanking = DB::update("UPDATE g_ranking set puntaje = (puntaje + {$puntaje}), tiempo_jugado =
									addtime(tiempo_jugado, '{$tiempo}') where id_ranking = '{$id}'");

		} else {
			$setRanking = DB::insert("INSERT into g_ranking (usuario_id, curso_id, id_unidad, id_temageneral, puntaje,
									tiempo_jugado, year, month) values('{$username}', '{$course}', '{$unidadId}', '0', '{$puntaje}',
									'{$tiempo}', '{$anio}', '{$month}')");
		}

		return $setRanking;
	}

	public function UpdateFechaRetos($id, $fecha_inicio) {
		$UpdFechas = DB::table('g_reto')
					->where('id_reto', $id)
					->update(
						[
							'fecha_inicio_juego' => $fecha_inicio
						]
					);

		return $UpdFechas;
	}

	public function ObtenerPerfil($username) {
		$sqlGanados = DB::table('g_reto')
					->select(DB::raw('count(id_reto) as ganado'))
					->where(
						[
							['usuario_retador', '=', $username],
							['puntaje_retador', '>', 2]
						]
					)->orWhere(
						[
							['usuario_retado', '=', $username],
							['puntaje_retado', '>', 2]
						]
					)->get();

		$sqlPerdidos = DB::table('g_reto')
					->select(DB::raw('count(id_reto) as perdido'))
					->where(
						[
							['usuario_retador', '=', $username],
							['puntaje_retador', '<=', 2]
						]
					)->orWhere(
						[
							['usuario_retado', '=', $username],
							['puntaje_retado', '<=', 2]
						]
					)->get();

		$sqlPuntaje = DB::select("SELECT ifnull((select sum(puntaje_retador)
				from g_reto where usuario_retador = '{$username}'), 0) + ifnull((select sum(puntaje_retado)
				from g_reto where usuario_retado = '{$username}'), 0) as total");

		$json->Ganados = $sqlGanados;
		$json->Perdidos = $sqlPerdidos;
		$json->Puntaje = $sqlPuntaje;

		return $json;
	}

	public function ActualizaUsuario($userid, $nik, $img) {
		$UpdUser = DB::table('g_usuario')
				->where('usuario_id', $userid)
				->update(
					[
						'nikname' => $nik,
						'image_avatar' => $img
					]
				);

		return $UpdUser;
	}

	public function InsertaCodeDispositivo($userid, $identifier) {
		$UpdDevice = DB::table('g_usuario')
					->where('usuario_id', $userid)
					->update(
						[
							'device_notification_id' => $identifier
						]
					);

		return $UpdDevice;
	}

	public function delete($id_reto) {
		$delete = DB::table('g_reto')
				->where('id_reto', '=', $id_reto)
				->delete();
		return $delete;
	}

	public function Notificacion($toUser, $fromUser) {

		define( 'API_ACCESS_KEY', 'AIzaSyCCa1aOXTCBK6an2exmaI6MEPjwqFRt-Hc');

		$user = DB::table('g_usuario')
				->select('firstname', 'device_notification_id')
				->where('username', '=', $toUser)->get();


		$key = $user[0]->device_notification_id;
		$username = $user[0]->firstname;

		$to = $key;
	    $title = "{$fromUser} te ha retado!!!";
	    $message = "{$username}, acepta el desafío y vencelo!!!";

	    $registrationId = array($to);
	    $msg = array(
	        'message' => $message,
	        'title' => $title,
	        'vibrate' => 1,
	        'sound' => 1
	    );

	    $fields = array(
	        'registration_ids' => $registrationId,
	        'data' => $msg
	    );

	    $headers = array(
	        'Authorization: key=' . API_ACCESS_KEY,
	        'Content-Type: application/json'
	    );

	    $ch = curl_init();
	    curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
	    curl_setopt( $ch,CURLOPT_POST, true );
	    curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
	    curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
	    curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
	    curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
	    $result = curl_exec($ch );
	    curl_close( $ch );

	    return $result;
	}

	public function newUser($firstname, $lastname, $username, $nikname, $email){
		$pass = sha1($password);
		$hoy = date('Y-m-d H:i:s');

		$newUser = DB::insert("INSERT INTO g_usuario (firstname, lastname, username, nikname,
					email, fecha_registro, creator_id, image_avatar, active)
					values ('{$firstname}', '{$lastname}', '{$username}', '{$pass}',
					'{$nikname}', '{$email}', '{$hoy}', '1', 'default', '1')");
		return $newUser;
	}

}