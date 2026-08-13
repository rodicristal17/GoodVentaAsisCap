-- Reversion al motor original. Ejecutar solo si es estrictamente necesario.
ALTER TABLE fotos_cliente ENGINE=MyISAM;
ALTER TABLE pedidos ENGINE=MyISAM;
ALTER TABLE solicitudcredito ENGINE=MyISAM;
ALTER TABLE listado ENGINE=MyISAM;
ALTER TABLE solicituddescuendo ENGINE=MyISAM;
ALTER TABLE visitas ENGINE=MyISAM;
ALTER TABLE tipopago ENGINE=MyISAM;
ALTER TABLE mensajesenviados ENGINE=MyISAM;
ALTER TABLE detallesolicitud ENGINE=MyISAM;
ALTER TABLE solicitud ENGINE=MyISAM;
