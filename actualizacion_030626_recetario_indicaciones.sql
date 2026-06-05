CREATE TABLE IF NOT EXISTS recetarios_indicaciones (
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

CREATE TABLE IF NOT EXISTS recetario_medicamentos (
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

CREATE TABLE IF NOT EXISTS recetario_indicaciones_detalle (
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

CREATE TABLE IF NOT EXISTS recetario_plantillas (
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

CREATE TABLE IF NOT EXISTS recetario_auditoria (
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

INSERT INTO recetario_plantillas (nombre, categoria, tipo, contenido_json, activo)
SELECT 'Post extraccion simple', 'Indicaciones post extraccion', 'indicacion',
'{"medicamentos":[],"indicaciones":[{"categoria":"Indicaciones post extraccion","texto":"Morder la gasa durante 30 a 45 minutos. No escupir, no enjuagarse fuerte y no usar sorbete durante las primeras 24 horas. Aplicar frio local por periodos cortos. Evitar esfuerzos, alcohol y comidas calientes. Consultar si hay sangrado persistente, dolor intenso o fiebre."}]}',
1
WHERE NOT EXISTS (SELECT 1 FROM recetario_plantillas WHERE nombre = 'Post extraccion simple');

INSERT INTO recetario_plantillas (nombre, categoria, tipo, contenido_json, activo)
SELECT 'Post cirugia', 'Indicaciones post cirugia', 'indicacion',
'{"medicamentos":[],"indicaciones":[{"categoria":"Indicaciones post cirugia","texto":"Reposo relativo por 24 a 48 horas. No realizar esfuerzos fisicos. Mantener higiene suave de la zona indicada por el profesional. Usar frio local el primer dia y seguir estrictamente la medicacion indicada. Asistir a control o retiro de sutura segun indicacion."}]}',
1
WHERE NOT EXISTS (SELECT 1 FROM recetario_plantillas WHERE nombre = 'Post cirugia');

INSERT INTO recetario_plantillas (nombre, categoria, tipo, contenido_json, activo)
SELECT 'Dolor e inflamacion', 'Dolor e inflamacion', 'receta_indicacion',
'{"medicamentos":[{"medicamento":"Ibuprofeno","presentacion":"600 mg","dosis":"1 comprimido","frecuencia":"cada 8 horas","duracion":"3 dias","cantidad":"","via":"Oral","observaciones":"Tomar despues de las comidas, salvo contraindicacion medica."}],"indicaciones":[{"categoria":"Dolor e inflamacion","texto":"Tomar la medicacion segun indicacion profesional. No automedicarse ni duplicar dosis. Consultar si el dolor aumenta, aparece inflamacion progresiva o fiebre."}]}',
1
WHERE NOT EXISTS (SELECT 1 FROM recetario_plantillas WHERE nombre = 'Dolor e inflamacion');

INSERT INTO recetario_plantillas (nombre, categoria, tipo, contenido_json, activo)
SELECT 'Antibiotico + analgesico', 'Antibiotico + analgesico', 'receta_indicacion',
'{"medicamentos":[{"medicamento":"Amoxicilina","presentacion":"500 mg","dosis":"1 capsula","frecuencia":"cada 8 horas","duracion":"7 dias","cantidad":"","via":"Oral","observaciones":"Completar el tratamiento indicado. Verificar alergias antes de emitir."},{"medicamento":"Ibuprofeno","presentacion":"600 mg","dosis":"1 comprimido","frecuencia":"cada 8 horas","duracion":"3 dias","cantidad":"","via":"Oral","observaciones":"Tomar despues de las comidas."}],"indicaciones":[{"categoria":"Indicaciones generales","texto":"Completar el antibiotico aunque mejore el cuadro. Suspender y consultar inmediatamente ante reaccion alergica. No mezclar medicacion sin autorizacion profesional."}]}',
1
WHERE NOT EXISTS (SELECT 1 FROM recetario_plantillas WHERE nombre = 'Antibiotico + analgesico');

INSERT INTO recetario_plantillas (nombre, categoria, tipo, contenido_json, activo)
SELECT 'Protesis removible', 'Indicaciones para protesis removible', 'indicacion',
'{"medicamentos":[],"indicaciones":[{"categoria":"Indicaciones para protesis removible","texto":"Usar la protesis de forma progresiva. Retirarla para dormir salvo indicacion contraria. Higienizar despues de cada comida con cepillo suave. Puede existir molestia inicial; acudir a control para ajustes y no desgastar la protesis en casa."}]}',
1
WHERE NOT EXISTS (SELECT 1 FROM recetario_plantillas WHERE nombre = 'Protesis removible');

INSERT INTO recetario_plantillas (nombre, categoria, tipo, contenido_json, activo)
SELECT 'PPR acrilica', 'Indicaciones para PPR acrilica', 'indicacion',
'{"medicamentos":[],"indicaciones":[{"categoria":"Indicaciones para PPR acrilica","texto":"Colocar y retirar la PPR con ambas manos. No forzar retenedores. Higienizar protesis y dientes pilares despues de cada comida. Asistir a controles para ajustes por molestias, presion o heridas."}]}',
1
WHERE NOT EXISTS (SELECT 1 FROM recetario_plantillas WHERE nombre = 'PPR acrilica');

INSERT INTO recetario_plantillas (nombre, categoria, tipo, contenido_json, activo)
SELECT 'PPR flexible', 'Indicaciones para PPR flexible', 'indicacion',
'{"medicamentos":[],"indicaciones":[{"categoria":"Indicaciones para PPR flexible","texto":"Adaptarse al uso de forma progresiva. Limpiar con cepillo suave y productos no abrasivos. No exponer a calor. Consultar para ajustes si genera presion o heridas."}]}',
1
WHERE NOT EXISTS (SELECT 1 FROM recetario_plantillas WHERE nombre = 'PPR flexible');

INSERT INTO recetario_plantillas (nombre, categoria, tipo, contenido_json, activo)
SELECT 'Endodoncia', 'Indicaciones post endodoncia', 'indicacion',
'{"medicamentos":[],"indicaciones":[{"categoria":"Indicaciones post endodoncia","texto":"Puede existir sensibilidad al masticar durante algunos dias. Evitar masticar alimentos duros del lado tratado hasta la restauracion definitiva. Tomar medicacion solo si fue indicada. Consultar ante dolor intenso, inflamacion o fiebre."}]}',
1
WHERE NOT EXISTS (SELECT 1 FROM recetario_plantillas WHERE nombre = 'Endodoncia');

INSERT INTO recetario_plantillas (nombre, categoria, tipo, contenido_json, activo)
SELECT 'Profilaxis', 'Indicaciones post limpieza/profilaxis', 'indicacion',
'{"medicamentos":[],"indicaciones":[{"categoria":"Indicaciones post limpieza/profilaxis","texto":"Mantener cepillado despues de cada comida, usar hilo dental o cepillos interdentales segun indicacion. Evitar alimentos o bebidas con colorantes intensos durante las primeras horas si hubo pulido o tratamiento complementario."}]}',
1
WHERE NOT EXISTS (SELECT 1 FROM recetario_plantillas WHERE nombre = 'Profilaxis');

INSERT INTO recetario_plantillas (nombre, categoria, tipo, contenido_json, activo)
SELECT 'Control de ortodoncia', 'Indicaciones de ortodoncia', 'indicacion',
'{"medicamentos":[],"indicaciones":[{"categoria":"Indicaciones de ortodoncia","texto":"Evitar alimentos duros o pegajosos. Mantener higiene cuidadosa alrededor de brackets, tubos o alineadores. Consultar si se despega algun aditamento, aparece herida persistente o dolor no habitual."}]}',
1
WHERE NOT EXISTS (SELECT 1 FROM recetario_plantillas WHERE nombre = 'Control de ortodoncia');

INSERT INTO listadodeacceso (nro, formulario, codigo, nombre, accion, tipo)
SELECT 73, 'FORMULARIO RECETARIO E INDICACIONES', 'VERRECETARIOINDICACIONES', 'VER MODULO', 'NO', 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM listadodeacceso WHERE codigo = 'VERRECETARIOINDICACIONES');

INSERT INTO listadodeacceso (nro, formulario, codigo, nombre, accion, tipo)
SELECT 73, 'FORMULARIO RECETARIO E INDICACIONES', 'BUSCARRECETARIOINDICACIONES', 'BUSCAR DOCUMENTOS', 'NO', 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM listadodeacceso WHERE codigo = 'BUSCARRECETARIOINDICACIONES');

INSERT INTO listadodeacceso (nro, formulario, codigo, nombre, accion, tipo)
SELECT 73, 'FORMULARIO RECETARIO E INDICACIONES', 'EMITIRRECETARIOINDICACIONES', 'EMITIR RECETARIO', 'NO', 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM listadodeacceso WHERE codigo = 'EMITIRRECETARIOINDICACIONES');

INSERT INTO listadodeacceso (nro, formulario, codigo, nombre, accion, tipo)
SELECT 73, 'FORMULARIO RECETARIO E INDICACIONES', 'IMPRIMIRRECETARIOINDICACIONES', 'IMPRIMIR DOCUMENTO', 'NO', 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM listadodeacceso WHERE codigo = 'IMPRIMIRRECETARIOINDICACIONES');

INSERT INTO listadodeacceso (nro, formulario, codigo, nombre, accion, tipo)
SELECT 73, 'FORMULARIO RECETARIO E INDICACIONES', 'ANULARRECETARIOINDICACIONES', 'ANULAR DOCUMENTO', 'NO', 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM listadodeacceso WHERE codigo = 'ANULARRECETARIOINDICACIONES');

INSERT INTO accesosuser (formulario, anhadir, modificar, buscar, informes, frmname, orden, usuarios_idusario, accion, agrupacion, idlistadodeaccesoFK, tipo)
SELECT lta.formulario, NULL, NULL, NULL, NULL, '', 0, u.cod_usuario,
       CASE
           WHEN lta.codigo IN ('EMITIRRECETARIOINDICACIONES','ANULARRECETARIOINDICACIONES') AND u.tipo = 'DOCTOR' THEN 'SI'
           WHEN lta.codigo IN ('VERRECETARIOINDICACIONES','BUSCARRECETARIOINDICACIONES','IMPRIMIRRECETARIOINDICACIONES') THEN 'SI'
           ELSE 'NO'
       END,
       NULL, lta.idlistadodeacceso, 'Administrativo'
FROM usuario u
INNER JOIN listadodeacceso lta ON lta.codigo IN (
    'VERRECETARIOINDICACIONES',
    'BUSCARRECETARIOINDICACIONES',
    'EMITIRRECETARIOINDICACIONES',
    'IMPRIMIRRECETARIOINDICACIONES',
    'ANULARRECETARIOINDICACIONES'
)
WHERE u.estado = 'Activo'
AND NOT EXISTS (
    SELECT 1
    FROM accesosuser au
    WHERE au.usuarios_idusario = u.cod_usuario
    AND au.idlistadodeaccesoFK = lta.idlistadodeacceso
);
