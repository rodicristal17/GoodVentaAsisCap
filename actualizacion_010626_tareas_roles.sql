ALTER TABLE tareas_programadas_asignadas
    ADD COLUMN tipo_asignacion ENUM('USUARIO','ROL') NOT NULL DEFAULT 'USUARIO' AFTER cod_usuarioFK,
    ADD COLUMN rol_operativoFK VARCHAR(45) NULL AFTER tipo_asignacion,
    ADD KEY idx_tarea_asignada_rol (rol_operativoFK),
    ADD KEY idx_tarea_asignada_tipo (tipo_asignacion);

ALTER TABLE tareas_programadas_diarias
    MODIFY cod_usuarioFK INT(11) NULL,
    ADD COLUMN tipo_destino ENUM('USUARIO','ROL') NOT NULL DEFAULT 'USUARIO' AFTER cod_usuarioFK,
    ADD COLUMN rol_operativoFK VARCHAR(45) NULL AFTER tipo_destino,
    ADD KEY idx_tarea_diaria_rol (rol_operativoFK),
    ADD KEY idx_tarea_diaria_destino (tipo_destino);
