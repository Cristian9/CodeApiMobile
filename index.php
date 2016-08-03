<?php

date_default_timezone_set('America/Lima');
error_reporting(E_ERROR);

require 'vendor/autoload.php';
require 'core/accdb.php';

$app = new Slim\App();

$app->get("/list-courses/", function($req, $res, $args) {
    getCursosAlumnos($req->getParam('page'), 30);
});

$app->get("/list-unidad/", function($req, $res, $args) {
    getUnidadesCourse($req->getParam('page'), 30, $req->getParam('args'));
});

$app->get("/list-categories/", function($req, $res, $args) {
    getCategories($req->getParam('page'), 30);
});

$app->get("/list-users/", function($req, $res, $args) {
    getUsers($req->getParam('page'), 30, $req->getParam('username'), $req->getParam('keywords'));
});

$app->get("/list-retos/", function($req, $res, $args) {
    getRetos($req->getParam('args'));
});

$app->get("/getQuestions/", function($req, $res, $args) {
    getQuestions($req->getParam('course'), $req->getParam('unidad'));
});

$app->get("/getResultQuestion/", function($req, $res, $args) {
    getResultQuestion($req->getParam('question_id'));
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
    $fecha_fin = date('Y-m-d H:i:s');

    updateRetos($ujugador, $countCorrect, $idQuestion, $fecha_fin);
});

$app->post("/updateDateReto/", function($req, $res, $args) {
    updateDate($req->getParam('idReto'), date('Y-m-d H:i:s'));
});

$app->run();

function getRetos($user) {
    $getDB = new accdb();

    $sqlRetosEnviados = "SELECT r.id_reto, r.usuario_retador, r.unidad_id, r.curso_id, r.id_temageneral, r.fecha_inicio_reto, 
            r.usuario_retado, u.nikname, r.jugado, time_format(timediff(r.fecha_inicio_reto + interval 1 day, now()), 
            concat('%H', 'h', ':', '%i', 'm')) as para_ganar from g_reto r, g_usuario u where r.usuario_retado = u.username 
            and r.usuario_retador = '{$user}' and r.jugado = 0";

    $sqlRetosRecibidos = "SELECT  r.id_reto, r.usuario_retador, u.nikname, r.unidad_id, r.curso_id, r.id_temageneral,
            r.fecha_inicio_reto, r.usuario_retado, r.jugado, time_format(timediff(r.fecha_inicio_reto + 
            interval 1 day, now()), concat('%H', 'h', ':', '%i', 'm')) as para_perder from g_reto r, 
            g_usuario u where r.usuario_retador = u.username and r.usuario_retado = '{$user}' and r.jugado = 0";

    $sqlRetosHistorial = "SELECT r.id_reto, r.usuario_retado as usuario, u.nikname, 'reto enviado', 
        if(r.puntaje_retador > r.puntaje_retado, 'Has ganado', 'Has perdido') as resultado from 
        g_reto r, g_usuario u where r.usuario_retado = u.username and r.usuario_retador = '{$user}' 
        and r.jugado = 1 union select r.id_reto, r.usuario_retador as usuario, u.nikname, 'reto recibido', 
        if(r.puntaje_retado > r.puntaje_retador, 'Has ganado', 'Has perdido') as resultado from g_reto r, g_usuario 
        u where r.usuario_retador = u.username and r.usuario_retado = '{$user}' and r.jugado = 1 order by id_reto";


    $json->Enviado = $getDB->dataSet($sqlRetosEnviados);
    $json->Recibido = $getDB->dataSet($sqlRetosRecibidos);
    $json->Historial = $getDB->dataSet($sqlRetosHistorial);

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

    $sql = "SELECT nikname as usuario, username 
        from g_usuario where username <> '{$username}' and (lastname like '%{$keywords}%' 
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

function updateRetos($ujugador, $countCorrect, $idQuestion, $fecha_fin) {

    $getDB = new accdb();

    $sqlGetRecord = "SELECT * FROM g_reto where id_reto = '{$idQuestion}'";

    $queryRecord = $getDB->dataSet($sqlGetRecord);

    $uRetador = $queryRecord[0]['usuario_retador'];
    $uRetado = $queryRecord[0]['usuario_retado'];


    $sqlUpdate = "UPDATE g_reto SET correctas_retador = '{$countCorrect}', 
        fecha_fin_reto = '{$fecha_fin}' where id_reto = '{$idQuestion}'";

    if ($uRetado == $ujugador) {
        $sqlUpdate = "UPDATE g_reto SET correctas_retado = '{$countCorrect}', 
            fecha_fin_juego = '{$fecha_fin}', jugado = 1 where id_reto = '{$idQuestion}'";
    }

    $updReto = $getDB->execQuery($sqlUpdate);

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

        if ($puntajeRetador == $puntajeRetado) {
            if ($tiempoRetador < $tiempoRetado) {
                $sqlUpdatePuntos = "UPDATE g_reto SET puntaje_retador = '5', puntaje_retado = '1' where id_reto = '{$idQuestion}'";
            } else {
                $sqlUpdatePuntos = "UPDATE g_reto SET puntaje_retador = '1', puntaje_retado = '5' where id_reto = '{$idQuestion}'";
            }
        } else if($puntajeRetador > $puntajeRetado) {
            $sqlUpdatePuntos = "UPDATE g_reto SET puntaje_retador = '5', puntaje_retado = '1' where id_reto = '{$idQuestion}'";
        } else {
            $sqlUpdatePuntos = "UPDATE g_reto SET puntaje_retador = '1', puntaje_retado = '5' where id_reto = '{$idQuestion}'";
        }

        $updReto = $getDB->execQuery($sqlUpdatePuntos);
    }
}

function login($uname, $pass) {
    $getDB = new accdb();
    $sha1pass = sha1($pass);
    $sqlUser = "SELECT usuario_id, firstname, lastname, username, email 
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
