USE `syscvxco_ac`;

UPDATE `centro_legajo_pagare_solicitud`
SET `solicitud_abierta`=CASE
  WHEN `estado` IN ('solicitada','aprobada','esperando_recepcion','preparada') THEN 1
  ELSE NULL
END;

DROP TRIGGER IF EXISTS `trg_clps_solicitud_abierta_bi`;
DROP TRIGGER IF EXISTS `trg_clps_solicitud_abierta_bu`;

DELIMITER $$

CREATE TRIGGER `trg_clps_solicitud_abierta_bi`
BEFORE INSERT ON `centro_legajo_pagare_solicitud`
FOR EACH ROW
SET NEW.`solicitud_abierta`=CASE
  WHEN NEW.`estado` IN ('solicitada','aprobada','esperando_recepcion','preparada') THEN 1
  ELSE NULL
END$$

CREATE TRIGGER `trg_clps_solicitud_abierta_bu`
BEFORE UPDATE ON `centro_legajo_pagare_solicitud`
FOR EACH ROW
SET NEW.`solicitud_abierta`=CASE
  WHEN NEW.`estado` IN ('solicitada','aprobada','esperando_recepcion','preparada') THEN 1
  ELSE NULL
END$$

DELIMITER ;

SELECT COUNT(*) AS solicitudes_abiertas
FROM `centro_legajo_pagare_solicitud`
WHERE `solicitud_abierta`=1;
