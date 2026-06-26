ALTER TABLE mensaje
  ADD INDEX idx_mensaje_interconsulta_estado_fecha (cod_interConsultaFK, estado, fecha_creacion),
  ADD INDEX idx_mensaje_interconsulta_fecha (cod_interConsultaFK, fecha_creacion);

ALTER TABLE menciones
  ADD INDEX idx_menciones_usuario_mensaje_leido (cod_usuarioFK, cod_mensajeFK, isLeido),
  ADD INDEX idx_menciones_mensaje_leido_usuario (cod_mensajeFK, isLeido, cod_usuarioFK);

ALTER TABLE interconsulta
  ADD INDEX idx_interconsulta_estado_codigo (estado, cod_interConsulta),
  ADD INDEX idx_interconsulta_usuario_estado_codigo (cod_usuarioFK_create, estado, cod_interConsulta);
