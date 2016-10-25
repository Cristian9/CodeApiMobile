<?php

date_default_timezone_set('America/Lima');
error_reporting(E_ERROR);

require 'vendor/autoload.php';
require 'core/accdb.php';

$app = new Slim\App();

$app->get("/getYearAndMonth/", function($req, $res, $args){
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
});

$app->get("/list-courses/", function($req, $res, $args) {
    getCursosAlumnos($req->getParam('page'), 30);
});

$app->get("/list-unidad/", function($req, $res, $args) {
    getUnidadesCourse($req->getParam('page'), 30, $req->getParam('courseId'));
});

$app->get("/list-categories/", function($req, $res, $args) {
    getCategories($req->getParam('page'), 30);
});

$app->get("/list-users/", function($req, $res, $args) {
    getUsers($req->getParam('page'), 30, $req->getParam('username'), $req->getParam('keywords'));
});

$app->get("/list-retos/", function($req, $res, $args) {
    getRetos($req->getParam('args'), $req->getParam('get'), $req->getParam('id'), $req->getParam('page'));
});

$app->get("/getQuestions/", function($req, $res, $args) {
    getQuestions($req->getParam('course'), $req->getParam('unidad'));
});

$app->post("/save_selected_rpta/", function($req, $res, $args) {
    setSelectedRespuesta(
            $idreto         = $req->getParam('reto_id'),
            $user           = $req->getParam('username'),
            $courseid       = $req->getParam('courseid'),
            $unidadid       = $req->getParam('unidadid'),
            $temageneralid  = $req->getParam('generalt'),
            $preguntaid     = $req->getParam('pregunta'),
            $respuestaid    = $req->getParam('respuest')
        );
});

$app->get("/get_resumen_juego/", function($req, $res, $args){
    getResumenJuego($req->getParam('id')/*, $req->getParam('uid')*/);
});

$app->get("/getRankingByCourse/", function($req, $res, $args){
    $course = $req->getParam('courseId');
    $year = $req->getParam('year');
    $month = $req->getParam('month');
    getRankingByCourse($course, $year, $month);
});

$app->get("/counter/", function($req, $res, $args){
    $uname = $req->getParam('uname');
    getRetosRecibidos($uname);
});

$app->post("/login/", function($req, $res, $args) {
    $user = $req->getParam('user');
    $pass = $req->getParam('pass');

    login($user, $pass);
});

$app->post("/save_retos/", function($req, $res, $args) {
    $id_reto = $req->getParam('id_reto');
    $uretador = $req->getParam('user_retador');
    $unidadId = $req->getParam('unidad_id');
    $courseId = $req->getParam('courseId');
    $uretado = $req->getParam('user_retado');
    $idTemageneral = $req->getParam('id_temageneral');
    $fecha_inicio = date('Y-m-d H:i:s');

    $id = saveRetos($id_reto, $uretador, $unidadId, $courseId, $uretado, $idTemageneral, $fecha_inicio);
});

$app->post("/update_retos/", function($req, $res, $args) {
    $ujugador = $req->getParam('username');
    $countCorrect = $req->getParam('countCorrect');
    $idQuestion = $req->getParam('idQuestion');
    $cancelled = $req->getParam('cancelled');
    $fecha_fin = date('Y-m-d H:i:s');

    updateRetos($cancelled, $ujugador, $countCorrect, $idQuestion, $fecha_fin);
});

$app->post("/updateDateReto/", function($req, $res, $args) {
    updateDate($req->getParam('idReto'), date('Y-m-d H:i:s'));
});

$app->get("/get_profile/", function($req, $res, $args){
    get_profile($req->getParam('username'));
});

$app->post("/change_nick/", function($req, $res, $args){
    changeNick($req->getParam('userid'), $req->getParam('niknam'), $req->getParam('image'));
});

$app->post("/registerDevice/", function($req, $res, $args){
    registerDevice($req->getParam('userid'), $req->getParam('identifier'));
});

$app->post("/sendNotification/", function($req, $res, $args){
    sendPushNotification($req->getParam('toUser'), $req->getParam('fromUser'));
});

$app->run();

function sendPushNotification($toUser, $fromUser) {
    $getDB = new accdb();

    define( 'API_ACCESS_KEY', 'AIzaSyCCa1aOXTCBK6an2exmaI6MEPjwqFRt-Hc');

    $sqlUser = "SELECT firstname, device_notification_id from g_usuario where username = '{$toUser}'";

    $keyDevice = $getDB->dataSet($sqlUser);

    $key = $keyDevice[0]['device_notification_id'];
    $username = $keyDevice[0]['firstname'];

    $to = $key;
    $title = "Tienes un nuevo reto de {$fromUser} !!!";
    $message = "{$username}, un nuevo retador te ha desafiado";

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
    echo $result;
}

function getRetosRecibidos($uname){
    $getDB = new accdb();
    $sql = "SELECT count(*) retos from g_reto where usuario_retado = '{$uname}' and jugado = 0";
    $json->retos = $getDB->dataSet($sql);
    
    echo json_encode($json);
}

function registerDevice($userid, $identifier) {
    $getDB = new accdb();

    $sql = "UPDATE g_usuario set device_notification_id = '{$identifier}' where usuario_id = '{$userid}'";

    echo $getDB->execQuery($sql);
}

function get_profile($username) {
    $getDB = new accdb();

    $sqlGanados = "SELECT count(id_reto) as ganado from g_reto where (usuario_retador = '{$username}' and puntaje_retador > 1) 
        or (usuario_retado = '{$username}' and  puntaje_retado > 1)";

    $sqlPerdidos = "SELECT count(id_reto) as perdido from g_reto where (usuario_retador = '{$username}' and puntaje_retador <= 1)
        or (usuario_retado = '{$username}' and  puntaje_retado <= 1)";

    $sqlPuntaje = "SELECT ifnull((select sum(puntaje_retador) from g_reto where usuario_retador = '{$username}'), 0) + ifnull((select sum(puntaje_retado) 
        from g_reto where usuario_retado = '{$username}'), 0) as total";

    $json->Ganados = $getDB->dataSet($sqlGanados);
    $json->Perdidos = $getDB->dataSet($sqlPerdidos);
    $json->Puntaje = $getDB->dataSet($sqlPuntaje);

    echo json_encode($json);
}

function changeNick($userid, $nik, $img) {
    $getDB = new accdb();
    $sql = "UPDATE g_usuario set nikname = '{$nik}', image_avatar = '{$img}' where usuario_id = '{$userid}'";
    echo $getDB->execQuery($sql);
}

function getRetos($user, $get, $id, $page) {
    $getDB = new accdb();

    switch ($get) {
        case 'all':
            /********* Verifica si algún reto, enviado o recibido esta fuera de fecha ****/
            verificarRetoFueraFecha($user);
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

            $json->Enviado = $getDB->dataSet($sqlRetosEnviados);
            $json->Recibido = $getDB->dataSet($sqlRetosRecibidos);
            $json->Historial = $getDB->dataSet($sqlRetosHistorial);
            break;

        case 'history':
            /********* Verifica si algún reto, enviado o recibido esta fuera de fecha ****/
            verificarRetoFueraFecha($user);
            /*****************************************/

            if($page == '0') {
                $limite = 0;
            } else {
                $limite = ($page - 1) * 10;
            }

            $sqlRetosHistorial = "SELECT r.id_reto, r.usuario_retado as usuario, u.nikname, u.image_avatar, 'Enviado' as origen, if(r.puntaje_retador > 
                r.puntaje_retado, 'Has ganado', 'Has perdido') as resultado from g_reto r, g_usuario u where r.usuario_retado = u.username 
                and r.usuario_retador = '{$user}' and r.jugado = 1 union select r.id_reto, r.usuario_retador as usuario, 
                u.nikname, u.image_avatar, 'Recibido' as origen, if(r.puntaje_retado > r.puntaje_retador, 'Has ganado', 'Has perdido') 
                as resultado from g_reto r, g_usuario u where r.usuario_retador = u.username and r.usuario_retado = '{$user}' and r.jugado = 1 
                order by id_reto desc limit {$limite}, 10";

            $json->Historial = $getDB->dataSet($sqlRetosHistorial);
            
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

            $json->Detalle = $getDB->dataSet($sqlDetalle);
            break;
    }

    echo json_encode($json);
}

function verificarRetoFueraFecha($user) {
    $getDB = new accdb();
    $sqlVerifica = "SELECT id_reto, correctas_retador as correctas, if(unix_timestamp(fecha_inicio_reto + interval 1 day) - 
        unix_timestamp(now()) <= 0, 'yes', 'not') as actualizar from g_reto where usuario_retador = '{$user}' and jugado = 0
        union
        select id_reto, correctas_retador as correctas, if(unix_timestamp(fecha_inicio_reto + interval 1 day) - 
        unix_timestamp(now()) <= 0, 'yes', 'not') as actualizar from g_reto where usuario_retado = '{$user}' and jugado = 0";

    $queryVerifica = $getDB->dataSet($sqlVerifica);
    
    for ($i = 0; $i < count($queryVerifica); $i++) {
        if ($queryVerifica[$i]['actualizar'] == "yes") {

            $idreto = $queryVerifica[$i]['id_reto'];
            $punto_retador = ($queryVerifica[$i]['correctas'] >= 1) ? 5 : 0;

            $sqlUpdate = "UPDATE g_reto set puntaje_retador = '{$punto_retador}', fecha_inicio_juego = now(), fecha_fin_juego = 
                now(), correctas_retado = '0', puntaje_retado = '0', jugado = 1 where id_reto = '{$idreto}'";

            $getDB->execQuery($sqlUpdate);
        }
    }
}

function getResumenJuego($id/*, $uid*/) {
    $getDB = new accdb();

    $sql = "SELECT (select nikname from g_usuario where username = usuario_retador) as nikRetador, (select image_avatar from 
        g_usuario where username = usuario_retador) as myAvatar, (select nikname from g_usuario where username = usuario_retado) 
        as nikRetado, (select image_avatar from g_usuario where username = usuario_retado) as avatarRetado, correctas_retador, 
        if(correctas_retado = 0, '', correctas_retado) as correctas_retado, if(fecha_fin_reto = '0000-00-00 00:00:00', 'Cancelado', 
        time_format(timediff(fecha_fin_reto, fecha_inicio_reto), concat('%im ', '%ss'))) as tiempo_juego_retador, if(jugado <> 0, 
        time_format(timediff(fecha_fin_juego, fecha_inicio_juego), concat('%im ', '%ss')), 'Pendiente') as tiempo_juego_retado, 
        if(jugado = 0, time_format(timediff(fecha_inicio_reto + interval 1 day, now()), concat('Faltan %Hh ', '%im para ganar')), 
        'Juego Finalizado') as para_ganar from g_reto where id_reto = {$id}";

    $json->Resumen = $getDB->dataSet($sql);

    echo json_encode($json);
}

function getCursosAlumnos($page, $recs) {
    $getDB = new accdb();
    if (!isset($page) || $page == 0) {
        $limit = 0;
    } else {
        $limit = ($page - 1) * $recs;
    }

    $sql = "SELECT id, description FROM g_curso WHERE visible = 1 ORDER BY description LIMIT {$limit}, {$recs}";
    $query = $getDB->dataSet($sql);
    echo json_encode($query);
}

function getUnidadesCourse($page, $recs, $idCourse) {
    $getDB = new accdb();
    if (!isset($page) || $page == 0) {
        $limit = 0;
    } else {
        $limit = ($page - 1) * $recs;
    }

    $sql = "SELECT id, description FROM g_unidad WHERE id_curso = '{$idCourse}' ORDER BY description LIMIT {$limit}, {$recs}";
    $query = $getDB->dataSet($sql);
    echo json_encode($query);
}

function getQuestions($course, $unidad) {
    $getDB = new accdb();

    $sql = "SELECT id_preguntas, preguntas FROM g_preguntas WHERE 
            course_id = '{$course}' and id_unidad = '{$unidad}' order by rand() limit 5";
    $query = $getDB->dataSet($sql);

    for ($i = 0; $i < count($query); $i++) {
        $question_id = $query[$i]['id_preguntas'];

        $sql_answer = "SELECT * FROM g_respuestas WHERE id_pregunta = '{$question_id}'";

        $queryAnswer = $getDB->dataSet($sql_answer);

        $query[$i]['Indice'] = $i;
        $query[$i]['Respuesta'] = $queryAnswer;
    }

    echo json_encode($query);
}

function getTemasGenerales($page, $recs) {
    $getDB = new accdb();

    if (!isset($page) || $page == 0) {
        $limit = 0;
    } else {
        $limit = ($page - 1) * $recs;
    }

    $sql = "SELECT id, description FROM g_temageneral ORDER BY description LIMIT {$limit}, {$recs}";
    $query = $getDB->dataSet($sql);
    echo json_encode($query);
}

function getUsers($page, $recs, $username, $keywords) {
    $getDB = new accdb();

    if (!isset($page) || $page == 0) {
        $limit = 0;
    } else {
        $limit = ($page - 1) * $recs;
    }

    $sql = "SELECT concat(firstname, ' ', lastname) as uname, nikname as usuario, username, image_avatar 
        from g_usuario where username <> '{$username}' and active = 1 and (lastname like '%{$keywords}%' 
        or firstname like '%{$keywords}%' or nikname like '%{$keywords}%') order by 
        usuario_id LIMIT {$limit}, {$recs}";

    $query = $getDB->dataSet($sql);

    echo json_encode($query);
}

function saveRetos($id_reto, $uretador, $unidadId, $courseId, $uretado, $idTemageneral, $fecha_inicio) {
    $getDB = new accdb();

    if($id_reto == "") {
        $sqlInsert = "INSERT INTO  g_reto (usuario_retador, unidad_id, curso_id, id_temageneral, fecha_inicio_reto, usuario_retado) 
                VALUES ('{$uretador}', '{$unidadId}', '{$courseId}', '{$idTemageneral}', '{$fecha_inicio}', '{$uretado}')";

        $insertId = $getDB->InsertAndGetLastId($sqlInsert);

        echo $insertId;
    }
}

function updateDate($id, $fecha_inicio) {
    $getDB = new accdb();
    $sqlUpdate = "UPDATE g_reto SET fecha_inicio_juego = '{$fecha_inicio}' where id_reto = '{$id}'";
    $updReto = $getDB->execQuery($sqlUpdate);
}

function updateRetos($cancelled, $ujugador, $countCorrect, $idQuestion, $fecha_fin) {

    $getDB = new accdb();

    // Obteniendo datos de los usuarios que jugaron por cada reto
    $sqlGetRecord = "SELECT *, year(fecha_inicio_reto) as anio, month(fecha_inicio_reto) as month FROM g_reto where id_reto = '{$idQuestion}'";

    $queryRecord = $getDB->dataSet($sqlGetRecord);
    $anio = $queryRecord[0]['anio'];
    $month = $queryRecord[0]['month'];
    $uRetador = $queryRecord[0]['usuario_retador'];
    $uRetado = $queryRecord[0]['usuario_retado'];
    $uFechaIn = $queryRecord[0]['fecha_inicio_reto'];
    $unidadId = $queryRecord[0]['unidad_id'];

    if($cancelled == "") {

        // Actualizando la fecha de termino de cada reto tanto del usuario retador como del retado
        $sqlUpdate = "UPDATE g_reto SET correctas_retador = '{$countCorrect}', 
            fecha_fin_reto = '{$fecha_fin}' where id_reto = '{$idQuestion}'";

        if ($uRetado == $ujugador) {
            $sqlUpdate = "UPDATE g_reto SET correctas_retado = '{$countCorrect}', 
                fecha_fin_juego = '{$fecha_fin}', jugado = 1 where id_reto = '{$idQuestion}'";
        }

        $updReto = $getDB->execQuery($sqlUpdate);

        // validamos que se haya actualizado correctamente y si el que esta jugando es
        // el usuario retado.
        
        if ($updReto && $uRetado == $ujugador) {

            $queryRecordRate = "SELECT timediff(fecha_fin_reto, fecha_inicio_reto)
                as tiempo_retador, correctas_retador, timediff(fecha_fin_juego, fecha_inicio_juego) 
                as tiempo_retado, correctas_retado from g_reto where id_reto = '{$idQuestion}'";

            $queryRate = $getDB->dataSet($queryRecordRate);


            //Retador
            $puntajeRetador = $queryRate[0]['correctas_retador'];
            $tiempoRetador = $queryRate[0]['tiempo_retador'];

            // Retado
            $puntajeRetado = $queryRate[0]['correctas_retado'];
            $tiempoRetado = $queryRate[0]['tiempo_retado'];

            // Consultamos la cantidad de respuestas correctas y el tiempo jugado
            // para asignarle los puntajes a cada uno.

            $pRetador = 5;
            $pRetado = 1;
            
            if ($puntajeRetador == $puntajeRetado) {
                if ($tiempoRetador < $tiempoRetado) {
                    $sqlUpdatePuntos = "UPDATE g_reto SET puntaje_retador = '5', puntaje_retado = '1' where id_reto = '{$idQuestion}'";
                } else {

                    $pRetador = 1;
                    $pRetado = 5;

                    $sqlUpdatePuntos = "UPDATE g_reto SET puntaje_retador = '1', puntaje_retado = '5' where id_reto = '{$idQuestion}'";
                }
            } else if($puntajeRetador > $puntajeRetado) {
                $sqlUpdatePuntos = "UPDATE g_reto SET puntaje_retador = '5', puntaje_retado = '1' where id_reto = '{$idQuestion}'";
            } else {

                $pRetador = 1;
                $pRetado = 5;
                
                $sqlUpdatePuntos = "UPDATE g_reto SET puntaje_retador = '1', puntaje_retado = '5' where id_reto = '{$idQuestion}'";
            }

            // Funcion que actualiza el ranking mensual.
            updateRanking($uRetador, $uRetado, $uFechaIn, $idQuestion, $unidadId, $pRetador, $pRetado, $tiempoRetador, $tiempoRetado);

            // Guardamos los puntajes en la tabla retos
            $updReto = $getDB->execQuery($sqlUpdatePuntos);
        }

    } else {

        $course = $queryRecord[0]['curso_id'];

        $sqlRangking = "SELECT * from g_ranking where usuario_id = '{$ujugador}' and curso_id = '{$course}'";

        $data = $getDB->dataSet($sqlRangking);

        if (!empty($data)) {
            $id = $data[0]['id_ranking'];

            if($uRetado == $ujugador) {

                $queryRecordRate = "SELECT timediff(fecha_fin_reto, fecha_inicio_reto) as tiempo_retador from g_reto where id_reto = '{$idQuestion}'";

                $queryRate = $getDB->dataSet($queryRecordRate);

                $sqlUpdateCancelled = "UPDATE g_reto set puntaje_retador = '5', puntaje_retado = '1', fecha_fin_juego = '{$fecha_fin}', 
                    jugado = '1' where id_reto = '{$idQuestion}'";

                updateRanking($uRetador, $uRetado, $uFechaIn, $idQuestion, $unidadId, 5, 1, $queryRate[0]['tiempo_retador'], '00:00:00');

            } else {
                if ($data[0]['year'] == $anio && $data[0]['month'] == $month) {
                    $sqlUpdateCancelled = "UPDATE g_ranking set puntaje = if((puntaje - 3) < 0, 0, (puntaje - 3)) where id_ranking = '{$id}'";
                }

                $getDB->execQuery("DELETE from g_reto where id_reto = '{$idQuestion}'");
            }
        }

        $getDB->execQuery($sqlUpdateCancelled);
    }
}

function updateRanking($retador, $retado, $fecha, $idreto, $unidadId, $pRetador, $pRetado, $timeRetador, $timeRetado) {
    $getDB = new accdb();
    $sqlVerficarFecha = "SELECT year(fecha_inicio_reto) as anio, month(fecha_inicio_reto) as mes, curso_id 
        from g_reto where id_reto = '{$idreto}'";
    $dataReto = $getDB->dataSet($sqlVerficarFecha);

    $anio = $dataReto[0]['anio'];
    $month = $dataReto[0]['mes'];
    $course = $dataReto[0]['curso_id'];

    $usuarios = array($retador, $retado);
    $puntaje = array($pRetador, $pRetado);
    $tiempos = array($timeRetador, $timeRetado);

    for($i = 0; $i < count($usuarios); $i++) {
        $username = $usuarios[$i];
        $puntosac = $puntaje[$i];
        $tiempoac = $tiempos[$i];

        $sqlRangking = "SELECT * from g_ranking where usuario_id = '{$username}' and curso_id = '{$course}'";
        $data = $getDB->dataSet($sqlRangking);

        $setRanking = "INSERT into g_ranking (usuario_id, curso_id, id_unidad, id_temageneral, puntaje, tiempo_jugado, year, month)
            values('{$username}', '{$course}', '{$unidadId}', '0', '{$puntosac}', '{$tiempoac}', '{$anio}', '{$month}')";

        if (!empty($data)) {
            $id = $data[0]['id_ranking'];
            if ($data[0]['year'] == $anio && $data[0]['month'] == $month) {

                $setRanking = "UPDATE g_ranking set puntaje = (puntaje + {$puntosac}), tiempo_jugado = addtime(tiempo_jugado, '{$tiempoac}')
                    where id_ranking = '{$id}'";
            }
        }

        $getDB->execQuery($setRanking);
    }
}

function setSelectedRespuesta($idreto, $username, $courseid, $unidadid, $generalt, $pregunta, $respuest) {
    $getDB = new accdb();
    $sql = "INSERT INTO g_respuesta_usuario (username, course_id, unidad_id, id_temageneral, pregunta_id, respuesta_id, id_reto)
        values ('{$username}', '{$courseid}', '{$unidadid}', '{$generalt}', '{$pregunta}', '{$respuest}', '{$idreto}')";

    $getDB->execQuery($sql);
}

function getRankingByCourse($course, $year, $month){
    $getDB = new accdb();
    $sql = "SELECT u.nikname, u.image_avatar, r.* from g_ranking r, g_usuario u where u.username = r.usuario_id and 
        r.curso_id = '{$course}' and r.year = '{$year}' and r.month = '{$month}' order by r.puntaje desc, r.tiempo_jugado desc";
    
    $ranking = $getDB->dataSet($sql);

    echo json_encode($ranking);
}

function login($uname, $pass) {
    $getDB = new accdb();
    $sha1pass = sha1($pass);
    $sqlUser = "SELECT usuario_id, firstname, lastname, username, password, nikname, email, image_avatar 
        FROM g_usuario WHERE username = '{$uname}' and password = '{$sha1pass}'";

    //$wsUrl = 'http://10.31.1.223:8051/ServiceAD.asmx?WSDL';
    //$isValid = $this->loginWSAuthenticate($user, $pass, $wsUrl);
    $isValid = 1;
    if ($isValid) {
        $exists_user = $getDB->numRows($sqlUser);
        if ($exists_user) {
            $dataUser = $getDB->dataSet($sqlUser);
            echo json_encode($dataUser);
        } else {
            echo $exists_user;
        }
    }
}