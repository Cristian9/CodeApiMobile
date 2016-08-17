select * from g_respuestas;


select * from g_preguntas where course_id = 'A48Z';

select * from g_usuario;

truncate table g_reto;

select fecha_inicio_reto, fecha_inicio_reto + interval 1 day as a, now(), timediff(fecha_inicio_reto + interval 1 day, now()) as b from g_reto where id_reto = 2;

select timediff(fecha_fin_reto, fecha_inicio_reto) as tiempo_retador, correctas_retador, puntaje_retador, timediff(fecha_fin_juego, fecha_inicio_juego) as tiempo_retado, correctas_retado, puntaje_retado from g_reto where id_reto = '1';


select  r.id_reto, r.usuario_retador, u.nikname, r.unidad_id, r.curso_id, r.id_temageneral, r.fecha_inicio_reto, r.jugado, if(puntaje_retador > puntaje_retado, 'Has ganado', 'Has perdido') resultado from g_reto r, g_usuario u where r.usuario_retador = u.username and (r.usuario_retador = '1622571' or r.usuario_retado = '1622571') and r.jugado = 1;


select * from g_reto where id_reto in(25,24,21,26);

select time_to_sec('2016-08-09 09:09:31');
select if((time_to_sec('2016-08-09 09:09:31') - time_to_sec('2016-08-09 09:10:31')) < 0, 'fuck', 'ok');

/******** Retos enviados y recibidos Terminados **************/

select r.id_reto, r.usuario_retador, r.unidad_id, r.curso_id, r.id_temageneral, r.fecha_inicio_reto, r.usuario_retado, u.nikname, r.jugado, time_format(timediff(r.fecha_inicio_reto + interval 1 day, now()), concat('%H', 'h', ':', '%i', 'm')) as para_ganar from g_reto r, g_usuario u where r.usuario_retado = u.username and r.usuario_retador = 'ctapia' and r.jugado = 0;
-- -------------------------------------------------------
select r.id_reto, r.usuario_retador, u.nikname, r.unidad_id, r.curso_id, r.id_temageneral, r.fecha_inicio_reto, r.usuario_retado, r.jugado, time_format(timediff(r.fecha_inicio_reto + interval 1 day, now()), concat('%H', 'h', ':', '%i', 'm')) as para_perder from g_reto r, g_usuario u where r.usuario_retador = u.username and r.usuario_retado = '1622571' and r.jugado = 0;

-- --------------------------------------------------------

select r.id_reto, r.usuario_retado as usuario, u.nikname, 'Enviado' as origen, if(r.puntaje_retador > r.puntaje_retado, 'Has ganado', 'Has perdido') as resultado from g_reto r, g_usuario u where r.usuario_retado = u.username and r.usuario_retador = '1622571' and r.jugado = 1 
union 
select r.id_reto, r.usuario_retador as usuario, u.nikname, 'Recibido' as origen, if(r.puntaje_retado > r.puntaje_retador, 'Has ganado', 'Has perdido') as resultado from g_reto r, g_usuario u where r.usuario_retador = u.username and r.usuario_retado = '1622571' and r.jugado = 1 order by id_reto;

/**********************************************/

/******* tiempo vencido ********/

select *, id_reto, correctas_retador as correctas, if(unix_timestamp(fecha_inicio_reto + interval 1 day) - unix_timestamp(now()) <= 0, 'yes', 'not') as actualizar from g_reto where usuario_retador = 'dsakiyama' and jugado = 0
union
select *, id_reto, correctas_retador as correctas, if(unix_timestamp(fecha_inicio_reto + interval 1 day) - unix_timestamp(now()) <= 0, 'yes', 'not') as actualizar from g_reto where usuario_retado = 'dsakiyama' and jugado = 0;

select (unix_timestamp(fecha_inicio_reto + interval 1 day) - unix_timestamp(now())) from g_reto where id_reto = 34;
/******************************/

/******** detalle ***************/

select r.id_reto, (select nikname from g_usuario where username = '1622571') as myNik, r.usuario_retado as rival, u.nikname, 'Enviado' as origen, if(r.puntaje_retador > r.puntaje_retado, 'Has ganado', 'Has perdido') as resultado, r.correctas_retador as mis_correctas, r.puntaje_retador as mi_punto, r.correctas_retado as correctas_rival, r.puntaje_retado as punto_rival, time_format(timediff(r.fecha_fin_reto, r.fecha_inicio_reto), concat('%im ', '%ss')) as miTiempo, time_format(timediff(r.fecha_fin_juego, r.fecha_inicio_juego), concat('%im ', '%sm')) as tiempoRival from g_reto r, g_usuario u where r.usuario_retado = u.username and r.usuario_retador = '1622571' and r.jugado = 1 
union
select r.id_reto, (select nikname from g_usuario where username = '1622571') as myNik, r.usuario_retador as rival, u.nikname, 'Recibido' as origen, if(r.puntaje_retado > r.puntaje_retador, 'Has ganado', 'Has perdido') as resultado, r.correctas_retado as mis_correctas, r.puntaje_retado as mi_punto, r.correctas_retador as correctas_retado, r.puntaje_retador as punto_rival, time_format(timediff(r.fecha_fin_juego, r.fecha_inicio_juego), concat('%im ', '%sm')) as miTiempo, time_format(timediff(r.fecha_fin_reto, r.fecha_inicio_reto), concat('%im ', '%sm')) as tiempoRival from g_reto r, g_usuario u where r.usuario_retador = u.username and r.usuario_retado = '1622571' and r.jugado = 1 order by id_reto;

/*************************************/

update g_usuario set password = sha1('dsakiyama') where usuario_id = 33199;

/************ Resumen **********/

select (select nikname from g_usuario where username = '1622571') as myNik, u.nikname as nikRival, r.correctas_retador, if(r.correctas_retado = 0, '', r.correctas_retado) as correctas_retado, time_format(timediff(r.fecha_fin_reto, r.fecha_inicio_reto), concat('%im ', '%ss')) as tiempo_juego_retador, if(r.jugado <> 0, time_format(timediff(r.fecha_fin_juego, r.fecha_inicio_juego), concat('%im ', '%ss')), 'Pendiente') as tiempo_juego_retado, if(r.jugado = 0, time_format(timediff(r.fecha_inicio_reto + interval 1 day, now()), concat('Faltan %Hh ', '%im para ganar')), 'Juego Finalizado') as para_ganar from g_reto r, g_usuario u where r.usuario_retado = u.username and r.id_reto = 11;

/****************************/

/********** Cantidad de juegos (ganados, perdidos y puntaje) **********/
select * from g_reto;

select count(id_reto) as ganado from g_reto where (usuario_retador = 'ctapia' and puntaje_retador > 1) or (usuario_retado = 'ctapia' and  puntaje_retado > 1);
 
select count(id_reto) as perdido from g_reto where (usuario_retador = 'ctapia' and puntaje_retador <= 1) or (usuario_retado = 'ctapia' and  puntaje_retado <= 1);

select (select sum(puntaje_retador) from g_reto where usuario_retador = 'ctapia') + (select sum(puntaje_retado) from g_reto where usuario_retado = 'ctapia') as total;

/**********************************************************/

update g_usuario set nikname = lcase(concat(substring_index(lastname, ' ', -1), username)) where usuario_id = usuario_id;

select * from g_reto;

select * from g_respuesta_usuario;

truncate table g_respuesta_usuario;

alter table g_respuesta_usuario add column id_reto int not null after respuesta_id;

select * from g_respuestas;

select * from g_usuario;

select md5('ctapia'), sha1('ctapia');

