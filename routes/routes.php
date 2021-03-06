<?php

$app->get("/getToken/", 'MainController:getTokenCsrf');
$app->get("/getDateRanking/", 'MainController:getDateRanking');
$app->get("/list-courses/", 'MainController:listarCursos');
$app->get("/list-unidad/", 'MainController:listarUnidad');
$app->get("/list-retos/", 'MainController:listaRetos');
$app->get("/list-users/", 'MainController:listaUsuarios');
$app->get("/getQuestions/", 'MainController:cargarPreguntas');
$app->get("/get_resumen_juego/", 'MainController:resumenJuego');
$app->get("/getRankingByCourse/", 'MainController:rankingMensual');
$app->get("/counter/", 'MainController:burbujaRetos');
$app->get("/get_profile/", 'MainController:ObtenerPerfil');

$app->post("/sendNotification/", 'MainController:Notificacion');
$app->post("/login/", 'MainController:login');
$app->post("/save_selected_rpta/", 'MainController:insertarRespuestaUsuario');
$app->post("/save_retos/", 'MainController:insertarRetos');
$app->post("/update_retos/", 'MainController:actualizaRetos');
$app->post("/updateDateReto/", 'MainController:UpdateFechaRetos');
$app->post("/change_nick/", 'MainController:ActualizaUsuario');
$app->post("/registerDevice/", 'MainController:InsertaCodeDispositivo');
$app->post("/delete/", "MainController:delete");
$app->post("/sendMail/", "MainController:sendMail");
$app->post("/new-user/", 'MainController:newUser');