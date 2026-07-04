SET NAMES utf8mb4;

-- Agrega Actividades del dia como acceso rapido sin tocar tareas ni registros de asistencia.
SET @agenda_dia_catalog_existe := (
  SELECT COUNT(*)
  FROM dashboard_access_catalog
  WHERE access_key = 'agenda_dia'
);

UPDATE dashboard_access_catalog
SET default_quick_order = default_quick_order + 1
WHERE @agenda_dia_catalog_existe = 0
  AND is_default_quick_access = 1
  AND default_quick_order >= 16;

INSERT INTO dashboard_access_catalog
  (access_key, label, module_key, module_label, icon_key, route_path, permission_key, is_active, is_default_quick_access, default_quick_order)
VALUES
  ('agenda_dia', 'Actividades del dia', 'agendamientos', 'Agendamientos', 'agenda-dia', NULL, NULL, 1, 1, 16)
ON DUPLICATE KEY UPDATE
  label = VALUES(label),
  module_key = VALUES(module_key),
  module_label = VALUES(module_label),
  icon_key = VALUES(icon_key),
  route_path = VALUES(route_path),
  permission_key = VALUES(permission_key),
  is_active = VALUES(is_active),
  is_default_quick_access = VALUES(is_default_quick_access),
  default_quick_order = VALUES(default_quick_order),
  updated_at = CURRENT_TIMESTAMP;

-- Los usuarios sin configuracion propia lo reciben por ser predeterminado.
-- Los usuarios que ya personalizaron accesos necesitan una fila propia para verlo.
INSERT INTO dashboard_user_shortcuts
  (user_id, access_id, shortcut_order, is_visible)
SELECT
  usuarios_config.user_id,
  catalogo.id,
  COALESCE(MAX(accesos_usuario.shortcut_order), 0) + 1,
  1
FROM (
  SELECT DISTINCT user_id
  FROM dashboard_user_shortcuts
) usuarios_config
INNER JOIN dashboard_access_catalog catalogo
  ON catalogo.access_key = 'agenda_dia'
LEFT JOIN dashboard_user_shortcuts accesos_usuario
  ON accesos_usuario.user_id = usuarios_config.user_id
 AND accesos_usuario.is_visible = 1
GROUP BY usuarios_config.user_id, catalogo.id
ON DUPLICATE KEY UPDATE
  is_visible = 1,
  updated_at = CURRENT_TIMESTAMP;
