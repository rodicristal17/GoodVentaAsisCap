-- Migracion reversible de tablas legacy MyISAM a InnoDB.
-- Ejecutar durante mantenimiento y con respaldo verificado.
ALTER TABLE fotos_cliente ENGINE=InnoDB;
ALTER TABLE pedidos ENGINE=InnoDB;
ALTER TABLE solicitudcredito ENGINE=InnoDB;
ALTER TABLE listado ENGINE=InnoDB;
ALTER TABLE solicituddescuendo ENGINE=InnoDB;
ALTER TABLE visitas ENGINE=InnoDB;
ALTER TABLE tipopago ENGINE=InnoDB;
ALTER TABLE mensajesenviados ENGINE=InnoDB;
ALTER TABLE detallesolicitud ENGINE=InnoDB;
ALTER TABLE solicitud ENGINE=InnoDB;
