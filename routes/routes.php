<?php

$app->get("/getYearAndMonth/", 'MainController:getYearAndMonth');
$app->get("/list-courses/", 'MainController:listarCursos');
$app->get("/list-unidad/", 'MainController:listarUnidad');
$app->get("/list-users/", 'MainController:listaUsuarios');
$app->get("/list-retos/", 'MainController:listaRetos');
$app->get("/getQuestions/", 'MainController:cargarPreguntas');
$app->get("/get_resumen_juego/", 'MainController:resumenJuego');
$app->get("/getRankingByCourse/", 'MainController:rankingMensual');
$app->get("/counter", 'MainController:burbujaRetos');

$app->post("/login/", 'MainController:login');
$app->post("/save_selected_rpta/", 'MainController:insertarRespuestaUsuario');
$app->post("/save_retos/", 'MainController:insertarRetos');
$app->post("/update_retos/", 'MainController:actualizaRetos');