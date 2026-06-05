ALTER TABLE recetarios_indicaciones
    MODIFY paciente_id INT(11) DEFAULT NULL,
    MODIFY titular_id INT(11) DEFAULT NULL,
    MODIFY cedula_titular VARCHAR(45) DEFAULT NULL,
    MODIFY venta_id INT(11) DEFAULT NULL,
    MODIFY numero_venta VARCHAR(20) DEFAULT NULL,
    MODIFY doctor_id INT(11) DEFAULT NULL,
    MODIFY sucursal_id INT(11) DEFAULT NULL;
