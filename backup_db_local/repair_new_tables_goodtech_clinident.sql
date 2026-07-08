SET FOREIGN_KEY_CHECKS=0;
ALTER TABLE `syscvxco_ac`.`solicitud_eliminado`
  ADD PRIMARY KEY (`id_solicitud_eliminado`),
  ADD KEY `idx_solicitud_eliminado_estado_fecha` (`estado`,`fecha_solicitud`,`id_solicitud_eliminado`),
  ADD KEY `idx_solicitud_eliminado_registro` (`tabla_nombre`,`registro_pk_columna`,`registro_pk_valor`,`estado`),
  ADD KEY `idx_solicitud_eliminado_usuario` (`id_usuario_solicitud`);
ALTER TABLE `syscvxco_ac`.`solicitud_eliminado` MODIFY `id_solicitud_eliminado` int NOT NULL AUTO_INCREMENT;
ALTER TABLE `syscvxco_ac`.`solicitud_eliminado_detalle`
  ADD PRIMARY KEY (`id_solicitud_eliminado_detalle`),
  ADD KEY `idx_solicitud_eliminado_detalle_solicitud` (`id_solicitud_eliminado`),
  ADD KEY `idx_solicitud_eliminado_detalle_registro` (`id_solicitud_eliminado`,`tabla_nombre`,`registro_pk_columna`,`registro_pk_valor`),
  ADD KEY `idx_solicitud_eliminado_detalle_estado` (`estado_proceso`);
ALTER TABLE `syscvxco_ac`.`solicitud_eliminado_detalle` MODIFY `id_solicitud_eliminado_detalle` int NOT NULL AUTO_INCREMENT;
ALTER TABLE `syscvxco_ac`.`tarea_historial`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tarea_historial_tarea_fecha` (`tarea_id`,`created_at`,`id`),
  ADD KEY `idx_tarea_historial_usuario` (`usuario_id`),
  ADD KEY `idx_tarea_historial_accion` (`accion`);
ALTER TABLE `syscvxco_ac`.`tarea_historial` MODIFY `id` int NOT NULL AUTO_INCREMENT;
ALTER TABLE `syscvxco_ac`.`usuario_historial_cambios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_historial_usuario_fecha` (`cod_usuarioFK`,`fecha_hora`,`id`),
  ADD KEY `idx_usuario_historial_modifico` (`cod_usuario_modifico`),
  ADD KEY `idx_usuario_historial_estado` (`estado`);
ALTER TABLE `syscvxco_ac`.`usuario_historial_cambios` MODIFY `id` int NOT NULL AUTO_INCREMENT;
ALTER TABLE `syscvxco_ac`.`usuario_perfil_completitud`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuario_perfil_completitud_usuario` (`cod_usuarioFK`);
ALTER TABLE `syscvxco_ac`.`usuario_perfil_completitud` MODIFY `id` int NOT NULL AUTO_INCREMENT;
ALTER TABLE `syscvxco_ac`.`usuario_perfil_extendido`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuario_perfil_extendido_usuario` (`cod_usuarioFK`),
  ADD KEY `idx_usuario_perfil_responsable` (`responsable_directoFK`);
ALTER TABLE `syscvxco_ac`.`usuario_perfil_extendido` MODIFY `id` int NOT NULL AUTO_INCREMENT;
ALTER TABLE `syscvxco_ac`.`usuario_ubicacion_domicilio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuario_ubicacion_usuario` (`cod_usuarioFK`),
  ADD KEY `idx_usuario_ubicacion_estado` (`estado`);
ALTER TABLE `syscvxco_ac`.`usuario_ubicacion_domicilio` MODIFY `id` int NOT NULL AUTO_INCREMENT;
ALTER TABLE `syscvxco_ac`.`usuario_ubicacion_visualizaciones_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_ubicacion_log_consultado_fecha` (`cod_usuarioFK_consultado`,`fecha_hora`),
  ADD KEY `idx_usuario_ubicacion_log_visualizo_fecha` (`cod_usuarioFK_visualizo`,`fecha_hora`);
ALTER TABLE `syscvxco_ac`.`usuario_ubicacion_visualizaciones_log` MODIFY `id` int NOT NULL AUTO_INCREMENT;
SET FOREIGN_KEY_CHECKS=1;
