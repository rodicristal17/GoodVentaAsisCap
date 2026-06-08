CREATE TABLE tarea_historial (
    id INT(11) NOT NULL AUTO_INCREMENT,
    tarea_id INT(11) NOT NULL,
    usuario_id INT(11) NULL,
    accion VARCHAR(80) NOT NULL,
    campo VARCHAR(80) NULL,
    valor_anterior TEXT NULL,
    valor_nuevo TEXT NULL,
    motivo VARCHAR(255) NULL,
    origen VARCHAR(80) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    metadata_json LONGTEXT NULL,
    PRIMARY KEY (id),
    KEY idx_tarea_historial_tarea (tarea_id),
    KEY idx_tarea_historial_usuario (usuario_id),
    KEY idx_tarea_historial_fecha (created_at),
    KEY idx_tarea_historial_accion (accion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE tareas
    ADD COLUMN observacion TEXT NULL AFTER responsable,
    ADD COLUMN prioridad VARCHAR(30) NOT NULL DEFAULT 'Normal' AFTER observacion,
    ADD COLUMN culminada_por INT(11) NULL AFTER prioridad,
    ADD COLUMN culminada_en DATETIME NULL AFTER culminada_por,
    ADD COLUMN anulada_por INT(11) NULL AFTER culminada_en,
    ADD COLUMN anulada_en DATETIME NULL AFTER anulada_por,
    ADD COLUMN motivo_anulacion VARCHAR(255) NULL AFTER anulada_en,
    ADD COLUMN deleted_at DATETIME NULL AFTER motivo_anulacion,
    ADD COLUMN created_by INT(11) NULL AFTER deleted_at,
    ADD COLUMN updated_by INT(11) NULL AFTER created_by,
    ADD COLUMN created_at DATETIME NULL AFTER updated_by,
    ADD COLUMN updated_at DATETIME NULL AFTER created_at,
    ADD KEY idx_tareas_estado (estado),
    ADD KEY idx_tareas_deleted_at (deleted_at),
    ADD KEY idx_tareas_fecha_inicio_fin (fecha_inicio, fecha_fin);

CREATE TABLE recetarios_indicaciones (
    id INT(11) NOT NULL AUTO_INCREMENT,
    codigo_documento VARCHAR(35) DEFAULT NULL,
    paciente_id INT(11) DEFAULT NULL,
    beneficiario_id INT(11) DEFAULT NULL,
    titular_id INT(11) DEFAULT NULL,
    cedula_titular VARCHAR(45) DEFAULT NULL,
    nombre_paciente VARCHAR(180) DEFAULT NULL,
    nombre_titular VARCHAR(180) DEFAULT NULL,
    venta_id INT(11) DEFAULT NULL,
    numero_venta VARCHAR(20) DEFAULT NULL,
    apodo_venta VARCHAR(50) DEFAULT NULL,
    consulta_id INT(11) DEFAULT NULL,
    hilo_id INT(11) DEFAULT NULL,
    doctor_id INT(11) DEFAULT NULL,
    usuario_emisor_id INT(11) NOT NULL,
    sucursal_id INT(11) DEFAULT NULL,
    nombre_doctor VARCHAR(180) DEFAULT NULL,
    nombre_sucursal VARCHAR(180) DEFAULT NULL,
    tipo_documento ENUM('receta','indicacion','receta_indicacion') NOT NULL DEFAULT 'receta_indicacion',
    estado ENUM('borrador','emitida','anulada','reemplazada','complementaria') NOT NULL DEFAULT 'borrador',
    fecha_emision DATETIME DEFAULT NULL,
    motivo_anulacion VARCHAR(750) DEFAULT NULL,
    documento_reemplazado_id INT(11) DEFAULT NULL,
    observaciones_generales VARCHAR(1000) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    cod_usuario_createFK INT(11) DEFAULT NULL,
    cod_usuario_editFK INT(11) DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_recetarios_codigo_documento (codigo_documento),
    KEY idx_recetario_paciente (paciente_id),
    KEY idx_recetario_titular (titular_id),
    KEY idx_recetario_venta (venta_id),
    KEY idx_recetario_consulta (consulta_id),
    KEY idx_recetario_hilo (hilo_id),
    KEY idx_recetario_doctor (doctor_id),
    KEY idx_recetario_estado_fecha (estado, fecha_emision),
    KEY idx_recetario_reemplazado (documento_reemplazado_id),
    CONSTRAINT fk_recetario_paciente FOREIGN KEY (paciente_id) REFERENCES cliente(cod_cliente),
    CONSTRAINT fk_recetario_beneficiario FOREIGN KEY (beneficiario_id) REFERENCES cliente(cod_cliente),
    CONSTRAINT fk_recetario_titular FOREIGN KEY (titular_id) REFERENCES cliente(cod_cliente),
    CONSTRAINT fk_recetario_venta FOREIGN KEY (venta_id) REFERENCES venta(cod_venta),
    CONSTRAINT fk_recetario_consulta FOREIGN KEY (consulta_id) REFERENCES consulta(cod_consulta),
    CONSTRAINT fk_recetario_hilo FOREIGN KEY (hilo_id) REFERENCES interconsulta(cod_interConsulta),
    CONSTRAINT fk_recetario_doctor FOREIGN KEY (doctor_id) REFERENCES usuario(cod_usuario),
    CONSTRAINT fk_recetario_usuario_emisor FOREIGN KEY (usuario_emisor_id) REFERENCES usuario(cod_usuario),
    CONSTRAINT fk_recetario_sucursal FOREIGN KEY (sucursal_id) REFERENCES local(cod_local),
    CONSTRAINT fk_recetario_reemplazado FOREIGN KEY (documento_reemplazado_id) REFERENCES recetarios_indicaciones(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE recetario_medicamentos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    recetario_id INT(11) NOT NULL,
    medicamento VARCHAR(180) NOT NULL,
    presentacion VARCHAR(120) DEFAULT NULL,
    dosis VARCHAR(180) DEFAULT NULL,
    frecuencia VARCHAR(180) DEFAULT NULL,
    duracion VARCHAR(120) DEFAULT NULL,
    cantidad VARCHAR(80) DEFAULT NULL,
    via VARCHAR(80) DEFAULT NULL,
    observaciones VARCHAR(500) DEFAULT NULL,
    orden INT(11) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_recetario_medicamentos_recetario (recetario_id),
    CONSTRAINT fk_recetario_medicamentos_recetario FOREIGN KEY (recetario_id) REFERENCES recetarios_indicaciones(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE recetario_indicaciones_detalle (
    id INT(11) NOT NULL AUTO_INCREMENT,
    recetario_id INT(11) NOT NULL,
    categoria VARCHAR(120) DEFAULT NULL,
    texto TEXT NOT NULL,
    orden INT(11) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_recetario_indicaciones_recetario (recetario_id),
    CONSTRAINT fk_recetario_indicaciones_recetario FOREIGN KEY (recetario_id) REFERENCES recetarios_indicaciones(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE recetario_plantillas (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(120) NOT NULL,
    categoria VARCHAR(120) DEFAULT NULL,
    tipo ENUM('receta','indicacion','receta_indicacion') NOT NULL DEFAULT 'indicacion',
    contenido_json LONGTEXT NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_por INT(11) DEFAULT NULL,
    aprobado_por INT(11) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_recetario_plantillas_activo (activo, tipo, categoria),
    CONSTRAINT fk_recetario_plantillas_creado FOREIGN KEY (creado_por) REFERENCES usuario(cod_usuario),
    CONSTRAINT fk_recetario_plantillas_aprobado FOREIGN KEY (aprobado_por) REFERENCES usuario(cod_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE recetario_auditoria (
    id INT(11) NOT NULL AUTO_INCREMENT,
    recetario_id INT(11) NOT NULL,
    usuario_id INT(11) NOT NULL,
    accion VARCHAR(60) NOT NULL,
    descripcion VARCHAR(500) DEFAULT NULL,
    motivo VARCHAR(750) DEFAULT NULL,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    datos_anteriores LONGTEXT DEFAULT NULL,
    datos_nuevos LONGTEXT DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_recetario_auditoria_recetario (recetario_id),
    KEY idx_recetario_auditoria_usuario_fecha (usuario_id, fecha_hora),
    CONSTRAINT fk_recetario_auditoria_recetario FOREIGN KEY (recetario_id) REFERENCES recetarios_indicaciones(id) ON DELETE CASCADE,
    CONSTRAINT fk_recetario_auditoria_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(cod_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE recetario_firmas (
    id INT(11) NOT NULL AUTO_INCREMENT,
    recetario_id INT(11) NOT NULL,
    usuario_firmante_id INT(11) NOT NULL,
    nombre_firmante_snapshot VARCHAR(180) NOT NULL,
    usuario_emisor_id INT(11) NOT NULL,
    nombre_emisor_snapshot VARCHAR(180) NOT NULL,
    firma_imagen_path VARCHAR(255) NOT NULL,
    firma_base64 LONGTEXT DEFAULT NULL,
    fecha_hora_firma DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    hash_documento CHAR(64) DEFAULT NULL,
    hash_firma CHAR(64) DEFAULT NULL,
    ip VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    dispositivo VARCHAR(80) DEFAULT NULL,
    estado ENUM('vigente','invalida','anulada') NOT NULL DEFAULT 'vigente',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_recetario_firmas_recetario_estado (recetario_id, estado),
    KEY idx_recetario_firmas_usuario_fecha (usuario_firmante_id, fecha_hora_firma),
    CONSTRAINT fk_recetario_firmas_recetario FOREIGN KEY (recetario_id) REFERENCES recetarios_indicaciones(id) ON DELETE CASCADE,
    CONSTRAINT fk_recetario_firmas_firmante FOREIGN KEY (usuario_firmante_id) REFERENCES usuario(cod_usuario),
    CONSTRAINT fk_recetario_firmas_emisor FOREIGN KEY (usuario_emisor_id) REFERENCES usuario(cod_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO recetario_plantillas (nombre, categoria, tipo, contenido_json, activo) VALUES
('Post extraccion simple', 'Indicaciones post extraccion', 'indicacion', '{"medicamentos":[],"indicaciones":[{"categoria":"Indicaciones post extraccion","texto":"Morder la gasa durante 30 a 45 minutos. No escupir, no enjuagarse fuerte y no usar sorbete durante las primeras 24 horas. Aplicar frio local por periodos cortos. Evitar esfuerzos, alcohol y comidas calientes. Consultar si hay sangrado persistente, dolor intenso o fiebre."}]}', 1),
('Post cirugia', 'Indicaciones post cirugia', 'indicacion', '{"medicamentos":[],"indicaciones":[{"categoria":"Indicaciones post cirugia","texto":"Reposo relativo por 24 a 48 horas. No realizar esfuerzos fisicos. Mantener higiene suave de la zona indicada por el profesional. Usar frio local el primer dia y seguir estrictamente la medicacion indicada. Asistir a control o retiro de sutura segun indicacion."}]}', 1),
('Dolor e inflamacion', 'Dolor e inflamacion', 'receta_indicacion', '{"medicamentos":[{"medicamento":"Ibuprofeno","presentacion":"600 mg","dosis":"1 comprimido","frecuencia":"cada 8 horas","duracion":"3 dias","cantidad":"","via":"Oral","observaciones":"Tomar despues de las comidas, salvo contraindicacion medica."}],"indicaciones":[{"categoria":"Dolor e inflamacion","texto":"Tomar la medicacion segun indicacion profesional. No automedicarse ni duplicar dosis. Consultar si el dolor aumenta, aparece inflamacion progresiva o fiebre."}]}', 1),
('Antibiotico + analgesico', 'Antibiotico + analgesico', 'receta_indicacion', '{"medicamentos":[{"medicamento":"Amoxicilina","presentacion":"500 mg","dosis":"1 capsula","frecuencia":"cada 8 horas","duracion":"7 dias","cantidad":"","via":"Oral","observaciones":"Completar el tratamiento indicado. Verificar alergias antes de emitir."},{"medicamento":"Ibuprofeno","presentacion":"600 mg","dosis":"1 comprimido","frecuencia":"cada 8 horas","duracion":"3 dias","cantidad":"","via":"Oral","observaciones":"Tomar despues de las comidas."}],"indicaciones":[{"categoria":"Indicaciones generales","texto":"Completar el antibiotico aunque mejore el cuadro. Suspender y consultar inmediatamente ante reaccion alergica. No mezclar medicacion sin autorizacion profesional."}]}', 1),
('Protesis removible', 'Indicaciones para protesis removible', 'indicacion', '{"medicamentos":[],"indicaciones":[{"categoria":"Indicaciones para protesis removible","texto":"Usar la protesis de forma progresiva. Retirarla para dormir salvo indicacion contraria. Higienizar despues de cada comida con cepillo suave. Puede existir molestia inicial; acudir a control para ajustes y no desgastar la protesis en casa."}]}', 1),
('PPR acrilica', 'Indicaciones para PPR acrilica', 'indicacion', '{"medicamentos":[],"indicaciones":[{"categoria":"Indicaciones para PPR acrilica","texto":"Colocar y retirar la PPR con ambas manos. No forzar retenedores. Higienizar protesis y dientes pilares despues de cada comida. Asistir a controles para ajustes por molestias, presion o heridas."}]}', 1),
('PPR flexible', 'Indicaciones para PPR flexible', 'indicacion', '{"medicamentos":[],"indicaciones":[{"categoria":"Indicaciones para PPR flexible","texto":"Adaptarse al uso de forma progresiva. Limpiar con cepillo suave y productos no abrasivos. No exponer a calor. Consultar para ajustes si genera presion o heridas."}]}', 1),
('Endodoncia', 'Indicaciones post endodoncia', 'indicacion', '{"medicamentos":[],"indicaciones":[{"categoria":"Indicaciones post endodoncia","texto":"Puede existir sensibilidad al masticar durante algunos dias. Evitar masticar alimentos duros del lado tratado hasta la restauracion definitiva. Tomar medicacion solo si fue indicada. Consultar ante dolor intenso, inflamacion o fiebre."}]}', 1),
('Profilaxis', 'Indicaciones post limpieza/profilaxis', 'indicacion', '{"medicamentos":[],"indicaciones":[{"categoria":"Indicaciones post limpieza/profilaxis","texto":"Mantener cepillado despues de cada comida, usar hilo dental o cepillos interdentales segun indicacion. Evitar alimentos o bebidas con colorantes intensos durante las primeras horas si hubo pulido o tratamiento complementario."}]}', 1),
('Control de ortodoncia', 'Indicaciones de ortodoncia', 'indicacion', '{"medicamentos":[],"indicaciones":[{"categoria":"Indicaciones de ortodoncia","texto":"Evitar alimentos duros o pegajosos. Mantener higiene cuidadosa alrededor de brackets, tubos o alineadores. Consultar si se despega algun aditamento, aparece herida persistente o dolor no habitual."}]}', 1);

CREATE TABLE agenda_tratamientos (
    id INT NOT NULL AUTO_INCREMENT,
    id_agenda INT NOT NULL,
    cod_ventaFK INT NOT NULL,
    cod_detalle_ventaFK INT NOT NULL,
    estado ENUM('previsto','realizado','pendiente','cancelado') NOT NULL DEFAULT 'previsto',
    creado_por INT NULL,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    realizado_por INT NULL,
    realizado_en DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_agenda_detalle (id_agenda, cod_detalle_ventaFK),
    KEY idx_agenda_trat_agenda (id_agenda),
    KEY idx_agenda_trat_venta (cod_ventaFK),
    KEY idx_agenda_trat_detalle (cod_detalle_ventaFK)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE agenda_insumo_base (
    id INT NOT NULL AUTO_INCREMENT,
    id_insumo INT NOT NULL,
    cantidad DECIMAL(12,3) NOT NULL DEFAULT 1,
    estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    creado_por INT NULL,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_agenda_insumo_base (id_insumo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE agenda_consumo_insumos (
    id INT NOT NULL AUTO_INCREMENT,
    id_agenda INT NOT NULL,
    id_insumo INT NOT NULL,
    cantidad_prevista DECIMAL(12,3) NOT NULL DEFAULT 0,
    cantidad_confirmada DECIMAL(12,3) NULL,
    unidad_medida VARCHAR(40) NULL,
    estado ENUM('previsto','confirmado','ajustado') NOT NULL DEFAULT 'previsto',
    usuario_confirmo INT NULL,
    fecha_confirmo DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_agenda_consumo (id_agenda, id_insumo),
    KEY idx_agenda_consumo_agenda (id_agenda),
    KEY idx_agenda_consumo_insumo (id_insumo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE agenda_consumo_ajustes (
    id INT NOT NULL AUTO_INCREMENT,
    id_agenda INT NOT NULL,
    id_insumo INT NOT NULL,
    usuario_id INT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    paciente VARCHAR(160) NULL,
    venta_apodo VARCHAR(160) NULL,
    id_consultorio INT NULL,
    cantidad_anterior DECIMAL(12,3) NOT NULL DEFAULT 0,
    cantidad_nueva DECIMAL(12,3) NOT NULL DEFAULT 0,
    diferencia_stock DECIMAL(12,3) NOT NULL DEFAULT 0,
    motivo VARCHAR(255) NOT NULL DEFAULT 'Correccion de consumo confirmado',
    PRIMARY KEY (id),
    KEY idx_agenda_ajuste_agenda (id_agenda),
    KEY idx_agenda_ajuste_insumo (id_insumo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE insumosconsl (
    id_insumo INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(150) NOT NULL,
    descripcion VARCHAR(255) DEFAULT NULL,
    cant_stock NOT NULL INT,
    unidad_medida varchar(50) NOT NULL DEFAULT 0,
    estado  BOOLEAN DEFAULT 1,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_edit DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    stock_minimo INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id_insumo),
    KEY idx_insumosconsl_nombre (nombre)
);

CREATE TABLE insumo_producto(
    id_insumo_producto INT NOT NULL AUTO_INCREMENT,
    id_insumo INT NOT NULL,
    cod_producto VARCHAR(45) NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL DEFAULT 1,
    PRIMARY KEY (id_insumo_producto),
    FOREIGN KEY (id_insumo) REFERENCES insumosconsl(id_insumo),
    FOREIGN KEY (cod_producto) REFERENCES producto(cod_producto)
);

CREATE TABLE insumo_stock_consultorio (
    id_stock INT NOT NULL AUTO_INCREMENT,
    id_insumo INT NOT NULL,
    cod_local INT NOT NULL,
    id_consultorio INT NOT NULL,
    cantidad DECIMAL(12,3) NOT NULL DEFAULT 0,
    fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_stock),
    UNIQUE KEY uq_insumo_local_consultorio (id_insumo, cod_local, id_consultorio),
    KEY idx_insumo_stock_local (cod_local),
    KEY idx_insumo_stock_consultorio (id_consultorio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE movimientos_insumos (
    id INT NOT NULL AUTO_INCREMENT,
    grupo_movimiento VARCHAR(40) NULL,
    tipo ENUM('entrada','salida','ajuste') NOT NULL,
    insumo_id INT NOT NULL,
    sucursal_id INT NOT NULL,
    consultorio_id INT NOT NULL,
    cantidad DECIMAL(10,3) NOT NULL,
    motivo VARCHAR(255) NOT NULL,
    usuario_id INT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_mov_insumo (insumo_id),
    KEY idx_mov_fecha (fecha),
    KEY idx_mov_sucursal (sucursal_id),
    KEY idx_mov_consultorio (consultorio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE usuario_perfil_extendido (
    id INT(11) NOT NULL AUTO_INCREMENT,
    cod_usuarioFK INT(11) NOT NULL,
    barrio VARCHAR(120) DEFAULT NULL,
    ciudad VARCHAR(120) DEFAULT NULL,
    referencia_domicilio VARCHAR(255) DEFAULT NULL,
    contacto_emergencia_nombre VARCHAR(160) DEFAULT NULL,
    contacto_emergencia_telefono VARCHAR(45) DEFAULT NULL,
    segundo_telefono VARCHAR(45) DEFAULT NULL,
    correo VARCHAR(160) DEFAULT NULL,
    responsable_directoFK INT(11) DEFAULT NULL,
    area VARCHAR(120) DEFAULT NULL,
    cargo_funcion VARCHAR(160) DEFAULT NULL,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    cod_usuarioFK_update INT(11) DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuario_perfil_extendido_usuario (cod_usuarioFK),
    KEY idx_usuario_perfil_extendido_responsable (responsable_directoFK)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE usuario_historial_cambios (
    id INT(11) NOT NULL AUTO_INCREMENT,
    cod_usuarioFK INT(11) NOT NULL,
    campo VARCHAR(120) NOT NULL,
    valor_anterior TEXT DEFAULT NULL,
    valor_nuevo TEXT DEFAULT NULL,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    cod_usuario_modifico INT(11) DEFAULT NULL,
    origen VARCHAR(80) DEFAULT NULL,
    estado VARCHAR(45) DEFAULT 'Registrado',
    responsable_revisionFK INT(11) DEFAULT NULL,
    fecha_revision DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_usuario_historial_usuario (cod_usuarioFK),
    KEY idx_usuario_historial_fecha (fecha_hora),
    KEY idx_usuario_historial_modifico (cod_usuario_modifico)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE usuario_ubicacion_domicilio (
    id INT(11) NOT NULL AUTO_INCREMENT,
    cod_usuarioFK INT(11) NOT NULL,
    direccion VARCHAR(255) DEFAULT NULL,
    barrio VARCHAR(120) DEFAULT NULL,
    ciudad VARCHAR(120) DEFAULT NULL,
    referencia VARCHAR(255) DEFAULT NULL,
    latitud DECIMAL(11,8) DEFAULT NULL,
    longitud DECIMAL(11,8) DEFAULT NULL,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    cod_usuarioFK_update INT(11) DEFAULT NULL,
    valor_anterior_json TEXT DEFAULT NULL,
    estado VARCHAR(45) DEFAULT 'Activo',
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuario_ubicacion_usuario (cod_usuarioFK),
    KEY idx_usuario_ubicacion_update (cod_usuarioFK_update)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE usuario_ubicacion_visualizaciones_log (
    id INT(11) NOT NULL AUTO_INCREMENT,
    cod_usuarioFK_consultado INT(11) NOT NULL,
    cod_usuarioFK_visualizo INT(11) NOT NULL,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    origen VARCHAR(80) DEFAULT NULL,
    motivo VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_usuario_ubicacion_log_consultado (cod_usuarioFK_consultado),
    KEY idx_usuario_ubicacion_log_visualizo (cod_usuarioFK_visualizo),
    KEY idx_usuario_ubicacion_log_fecha (fecha_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE usuario_documentos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    cod_usuarioFK INT(11) NOT NULL,
    tipo_documento VARCHAR(80) NOT NULL,
    nombre_archivo VARCHAR(180) NOT NULL,
    url_archivo VARCHAR(255) NOT NULL,
    estado VARCHAR(45) DEFAULT 'Activo',
    fecha_carga DATETIME DEFAULT CURRENT_TIMESTAMP,
    cod_usuarioFK_carga INT(11) DEFAULT NULL,
    observacion VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_usuario_documentos_usuario (cod_usuarioFK),
    KEY idx_usuario_documentos_tipo (tipo_documento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE usuario_perfil_completitud (
    id INT(11) NOT NULL AUTO_INCREMENT,
    cod_usuarioFK INT(11) NOT NULL,
    porcentaje INT(3) NOT NULL DEFAULT 0,
    obligatorios_pendientes TEXT DEFAULT NULL,
    recomendados_pendientes TEXT DEFAULT NULL,
    fecha_calculo DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuario_perfil_completitud_usuario (cod_usuarioFK)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

ALTER TABLE usuario
    DROP COLUMN hora_entrada_lunes,
    DROP COLUMN hora_entrada_martes,
    DROP COLUMN hora_entrada_miercoles,
    DROP COLUMN hora_entrada_jueves,
    DROP COLUMN hora_entrada_viernes,
    DROP COLUMN hora_entrada_sabado;

-- Permisos agregados
-- VERRECETARIOINDICACIONES, BUSCARRECETARIOINDICACIONES, EMITIRRECETARIOINDICACIONES, IMPRIMIRRECETARIOINDICACIONES, ANULARRECETARIOINDICACIONES