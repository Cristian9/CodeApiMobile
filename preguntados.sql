select * from g_respuestas;

select * from g_curso where visible = 1;

select * from g_preguntas where course_id = 'A48Z';

select * from g_unidad;

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

select r.id_reto, r.usuario_retado as usuario, u.nikname, 'Enviado' as origen, if(r.puntaje_retador > r.puntaje_retado, 'Has ganado', 'Has perdido') as resultado from g_reto r, g_usuario u where r.usuario_retado = u.username and r.usuario_retador = 'ctapia' and r.jugado = 1 
union 
select r.id_reto, r.usuario_retador as usuario, u.nikname, 'Recibido' as origen, if(r.puntaje_retado > r.puntaje_retador, 'Has ganado', 'Has perdido') as resultado from g_reto r, g_usuario u where r.usuario_retador = u.username and r.usuario_retado = 'ctapia' and r.jugado = 1 order by id_reto desc;

/**********************************************/

/******* tiempo vencido ********/

select *, id_reto, correctas_retador as correctas, if(unix_timestamp(fecha_inicio_reto + interval 1 day) - unix_timestamp(now()) <= 0, 'yes', 'not') as actualizar from g_reto where usuario_retador = 'dsakiyama' and jugado = 0
union
select *, id_reto, correctas_retador as correctas, if(unix_timestamp(fecha_inicio_reto + interval 1 day) - unix_timestamp(now()) <= 0, 'yes', 'not') as actualizar from g_reto where usuario_retado = 'dsakiyama' and jugado = 0;

select (unix_timestamp(fecha_inicio_reto + interval 1 day) - unix_timestamp(now())) from g_reto where id_reto = 34;
/******************************/

/******** detalle ***************/

select r.id_reto, (select nikname from g_usuario where username = 'ctapia') as myNik, r.usuario_retado as rival, u.nikname, 'Enviado' as origen, if(r.puntaje_retador > r.puntaje_retado, 'Has ganado', 'Has perdido') as resultado, r.correctas_retador as mis_correctas, r.puntaje_retador as mi_punto, r.correctas_retado as correctas_rival, r.puntaje_retado as punto_rival, time_format(timediff(r.fecha_fin_reto, r.fecha_inicio_reto), concat('%im ', '%ss')) as miTiempo, time_format(timediff(r.fecha_fin_juego, r.fecha_inicio_juego), concat('%im ', '%sm')) as tiempoRival from g_reto r, g_usuario u where r.usuario_retado = u.username and r.usuario_retador = 'ctapia' and r.jugado = 1 
union
select r.id_reto, (select nikname from g_usuario where username = 'ctapia') as myNik, r.usuario_retador as rival, u.nikname, 'Recibido' as origen, if(r.puntaje_retado > r.puntaje_retador, 'Has ganado', 'Has perdido') as resultado, r.correctas_retado as mis_correctas, r.puntaje_retado as mi_punto, r.correctas_retador as correctas_retado, r.puntaje_retador as punto_rival, time_format(timediff(r.fecha_fin_juego, r.fecha_inicio_juego), concat('%im ', '%sm')) as miTiempo, time_format(timediff(r.fecha_fin_reto, r.fecha_inicio_reto), concat('%im ', '%sm')) as tiempoRival from g_reto r, g_usuario u where r.usuario_retador = u.username and r.usuario_retado = 'ctapia' and r.jugado = 1 order by id_reto;

/*************************************/

-- update g_usuario set password = sha1('dsakiyama') where usuario_id = 33199;

/************ Resumen **********/

select (select nikname from g_usuario where username = usuario_retador) as nikRetador, (select nikname from g_usuario where username = usuario_retado) as nikRival, correctas_retador, if(correctas_retado = 0, '', correctas_retado) as correctas_retado, if(fecha_fin_reto = '0000-00-00 00:00:00', 'Cancelado', time_format(timediff(fecha_fin_reto, fecha_inicio_reto), concat('%im ', '%ss'))) as tiempo_juego_retador, if(jugado <> 0, time_format(timediff(fecha_fin_juego, fecha_inicio_juego), concat('%im ', '%ss')), 'Pendiente') as tiempo_juego_retado, if(jugado = 0, time_format(timediff(fecha_inicio_reto + interval 1 day, now()), concat('Faltan %Hh ', '%im para ganar')), 'Juego Finalizado') as para_ganar from g_reto where id_reto = 1;

/****************************/

/********** Cantidad de juegos (ganados, perdidos y puntaje) **********/
select * from g_reto;

select count(id_reto) as ganado from g_reto where (usuario_retador = 'ctapia' and puntaje_retador > 1) or (usuario_retado = 'ctapia' and  puntaje_retado > 1);
 
select count(id_reto) as perdido from g_reto where (usuario_retador = 'ctapia' and puntaje_retador <= 1) or (usuario_retado = 'ctapia' and  puntaje_retado <= 1);

select ifnull((select sum(puntaje_retador) from g_reto where usuario_retador = 'ctapia') + (select sum(puntaje_retado) from g_reto where usuario_retado = 'ctapia'), 0) as total;

/**********************************************************/

-- update g_usuario set nikname = lcase(concat(substring_index(lastname, ' ', -1), username)) where usuario_id = usuario_id;

truncate table g_ranking;
truncate table g_reto;
truncate table g_respuesta_usuario;

select * from g_ranking;
select * from g_reto;
select * from g_respuesta_usuario;

update g_ranking set puntaje = if((puntaje - 3) < 0, 0, (puntaje - 3)) where id_ranking = 1;

select * from g_usuario;

select concat(firstname, ' ', lastname) as uname, nikname as usuario, username from g_usuario where username <> '1622571' and (lastname like '%cristian%' or firstname like '%cristian%' or nikname like '%cristian%') order by usuario_id;

select * from g_usuario where username in ('1622571', 'ctapia');

select concat(substr(substring_index(lastname, ' ', 1),1,1), lcase(substr(substring_index(lastname, ' ', 1),2))) name from g_usuario;

update g_usuario set firstname = concat(substr(firstname, 1, 1), lcase(substr(firstname, 2))) where usuario_id = usuario_id;

-- alter table g_usuario add column device_notification_id varchar(500) default "" after creator_id, add column image_avatar varchar(50) default "" after device_notification_id;

insert into g_usuario (firstname, lastname, username, password, email, fecha_registro, creator_id, active) values 
('Ines Susana', 'Evaristo Chiyong', 'ievaristo', sha1('ievaristo'), 'ievaristo@utp.edu.pe', now(), 1, 1),
('Jenny', 'Rios Poma', 'jriosp', sha1('jriosp'), 'jriosp@utp.edu.pe', now(), 1, 1),
('Gabriela Maria', 'Arizola Velasquez', 'garizola', sha1('garizola'), 'garizola@utp.edu.pe', now(), 1, 1),
('Victoria', 'Flores Laurente', 'vflores', sha1('vflores'), 'vflores@utp.edu.pe', now(), 1, 1),
('Renan', 'Flores Taype', 'rflorest', sha1('rflorest'), 'rflorest@utp.edu.pe', now(), 1, 1),
('Eduardo Antonio', 'Zapata Isasi', 'ezapata', sha1('ezapata'), 'ezapata@utp.edu.pe', now(), 1, 1),
('Lesly Giuliana', 'Loza Arista', 'lloza', sha1('lloza'), 'lloza@utp.edu.pe', now(), 1, 1),
('Giann Carlo', 'Carrasco Taco', 'gcarrasco', sha1('gcarrasco'), 'gcarrasco@utp.edu.pe', now(), 1, 1),
('Carlos Alexander', 'Valverde Castillo', 'cvalverde', sha1('cvalverde'), 'cvalverde@utp.edu.pe', now(), 1, 1),
('Jose Carlos', 'Ardiles Alarcon', 'jardiles', sha1('jardiles'), 'jardiles@utp.edu.pe', now(), 1, 1),
('Jhonatan Erick', 'Montes Machicado', 'jmontes', sha1('jmontes'), 'jmontes@utp.edu.pe', now(), 1, 1),
('Samuel Tony', 'Huarcaya Jara', 'shuarcaya', sha1('shuarcaya'), 'shuarcaya@utp.edu.pe', now(), 1, 1),
('Katty Janett', 'Huaringa Angeles', 'khuaringa', sha1('khuaringa'), 'khuaringa@utp.edu.pe', now(), 1, 1),
('Paul Fernando', 'Iparraguirre Velarde', 'piparraguir', sha1('piparraguir'), 'piparraguir@utp.edu.pe', now(), 1, 1),
('Miluska Adriana', 'Bellido Acevedo', 'mbellido', sha1('mbellido'), 'mbellido@utp.edu.pe', now(), 1, 1),
('Lia Consuelo', 'Flores Gallegos', 'lfloresg', sha1('lfloresg'), 'lfloresg@utp.edu.pe', now(), 1, 1),
('Rosly Andrea', 'Mejia León', 'rmejia', sha1('rmejia'), 'rmejia@utp.edu.pe', now(), 1, 1);


select * from g_usuario where active = 1 order by usuario_id asc;

update g_usuario set nikname = username where usuario_id = usuario_id and active = 1;
