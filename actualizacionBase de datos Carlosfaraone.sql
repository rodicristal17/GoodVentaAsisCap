SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS insumo_producto (
    id INT NOT NULL AUTO_INCREMENT,
    id_insumo INT NOT NULL,
    cod_producto VARCHAR(45) NOT NULL,
    cantidad DECIMAL(12,3) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_insumo_producto (id_insumo, cod_producto),
    KEY idx_insumo_producto_insumo (id_insumo),
    KEY idx_insumo_producto_producto (cod_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

SET @tabla_insumos_existe := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'insumosconsl'
);
SET @col_stock_minimo_existe := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'insumosconsl'
      AND COLUMN_NAME = 'stock_minimo'
);
SET @sql_stock_minimo := IF(
    @tabla_insumos_existe > 0 AND @col_stock_minimo_existe = 0,
    'ALTER TABLE insumosconsl ADD COLUMN stock_minimo INT NOT NULL DEFAULT 0',
    'SELECT 1'
);
PREPARE stmt_stock_minimo FROM @sql_stock_minimo;
EXECUTE stmt_stock_minimo;
DEALLOCATE PREPARE stmt_stock_minimo;

CREATE TABLE IF NOT EXISTS dashboard_access_catalog (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    access_key VARCHAR(120) NOT NULL,
    label VARCHAR(150) NOT NULL,
    module_key VARCHAR(80) NOT NULL,
    module_label VARCHAR(120) NOT NULL,
    icon_key VARCHAR(120) NULL,
    route_path VARCHAR(255) NULL,
    permission_key VARCHAR(120) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_default_quick_access TINYINT(1) NOT NULL DEFAULT 0,
    default_quick_order INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_dashboard_access_key (access_key),
    KEY idx_dashboard_access_module (module_key, is_active),
    KEY idx_dashboard_access_default_quick (is_default_quick_access, default_quick_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS dashboard_user_shortcuts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    access_id BIGINT UNSIGNED NOT NULL,
    shortcut_order INT UNSIGNED NOT NULL DEFAULT 0,
    custom_label VARCHAR(150) NULL,
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_dashboard_user_access (user_id, access_id),
    KEY idx_dashboard_user_shortcuts_order (user_id, is_visible, shortcut_order),
    KEY idx_dashboard_user_shortcuts_access (access_id),

    CONSTRAINT fk_dashboard_user_shortcuts_access
        FOREIGN KEY (access_id)
        REFERENCES dashboard_access_catalog(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;









INSERT INTO dashboard_access_catalog
(access_key, label, module_key, module_label, icon_key, route_path, permission_key, is_active, is_default_quick_access, default_quick_order)
VALUES
-- Accesos frecuentes / administrativos usados como rápidos
('cargar_compras', 'Cargar Compras', 'administrativo', 'Administrativo', 'cart', NULL, NULL, 1, 1, 1),
('cuentas_a_cobrar', 'Cuentas a Cobrar', 'administrativo', 'Administrativo', 'cash-register', NULL, NULL, 1, 1, 2),
('cobros_realizados', 'Cobros Realizados', 'administrativo', 'Administrativo', 'money-bag', NULL, NULL, 1, 1, 3),
('expediente_cliente', 'Exped. del Cliente', 'administrativo', 'Administrativo', 'client-file', NULL, NULL, 1, 1, 4),
('historial_venta', 'Historial de Venta', 'administrativo', 'Administrativo', 'sales-history', NULL, NULL, 1, 1, 5),
('productos', 'Productos', 'administrativo', 'Administrativo', 'products', NULL, NULL, 1, 1, 6),
('nueva_venta', 'Nueva Venta', 'administrativo', 'Administrativo', 'new-sale', NULL, NULL, 1, 1, 7),
('flujo_egreso_ingreso', 'Flujo de Egreso / Ingreso', 'administrativo', 'Administrativo', 'cash-flow', NULL, NULL, 1, 1, 8),
('cerrar_caja', 'Cerrar caja', 'administrativo', 'Administrativo', 'close-cashbox', NULL, NULL, 1, 1, 9),
('hilos_interconsultas', 'Hilos - InterConsultas', 'administrativo', 'Administrativo', 'interconsultas', NULL, NULL, 1, 1, 10),
('historial_presupuestos', 'Historial de presupuestos', 'administrativo', 'Administrativo', 'budget-history', NULL, NULL, 1, 1, 11),
('insumos', 'Insumos', 'administrativo', 'Administrativo', 'supplies', NULL, NULL, 1, 1, 12),
('migrar_caja', 'Migrar Caja', 'administrativo', 'Administrativo', 'cashbox-transfer', NULL, NULL, 1, 1, 13),
('recibir_caja', 'Recibir Caja', 'administrativo', 'Administrativo', 'receive-cashbox', NULL, NULL, 1, 1, 14),

-- Agendamientos
('historial_clinico_evolucion', 'Historial Clínico y Evolución', 'agendamientos', 'Agendamientos', 'clinical-history', NULL, NULL, 1, 0, NULL),
('sugerencias_calificaciones', 'Sugerencias y Calificaciones', 'agendamientos', 'Agendamientos', 'ratings', NULL, NULL, 1, 0, NULL),
('pagos_programados', 'Pagos Programados', 'agendamientos', 'Agendamientos', 'scheduled-payments', NULL, NULL, 1, 0, NULL),
('cargar_tratamientos', 'Cargar tratamientos', 'agendamientos', 'Agendamientos', 'treatments', NULL, NULL, 1, 0, NULL),
('historial_consulta', 'Historial Consulta', 'agendamientos', 'Agendamientos', 'consultation-history', NULL, NULL, 1, 0, NULL),
('calendario', 'Calendario', 'agendamientos', 'Agendamientos', 'calendar', NULL, NULL, 1, 0, NULL),
('asignar_tareas', 'Asignar Tareas', 'agendamientos', 'Agendamientos', 'assign-tasks', NULL, NULL, 1, 0, NULL),

-- Administrativo
('cargar_sueldo', 'Cargar Sueldo', 'administrativo', 'Administrativo', 'salary', NULL, NULL, 1, 0, NULL),
('cuentas_a_pagar', 'Cuentas a pagar', 'administrativo', 'Administrativo', 'accounts-payable', NULL, NULL, 1, 0, NULL),
('consulta_cajas', 'Consulta de cajas', 'administrativo', 'Administrativo', 'cashbox-search', NULL, NULL, 1, 0, NULL),
('historial_compras', 'Historial de Compras', 'administrativo', 'Administrativo', 'purchase-history', NULL, NULL, 1, 0, NULL),
('productos_garantia', 'Productos en Garantía', 'administrativo', 'Administrativo', 'warranty-products', NULL, NULL, 1, 0, NULL),
('productos_baja', 'Productos de baja', 'administrativo', 'Administrativo', 'inactive-products', NULL, NULL, 1, 0, NULL),
('despachar_productor', 'Despachar Productor', 'administrativo', 'Administrativo', 'dispatch', NULL, NULL, 1, 0, NULL),
('control_deposito', 'Control de Deposito', 'administrativo', 'Administrativo', 'warehouse-control', NULL, NULL, 1, 0, NULL),
('agenda_cliente', 'Agenda Cliente', 'administrativo', 'Administrativo', 'client-agenda', NULL, NULL, 1, 0, NULL),
('cheque', 'Cheque', 'administrativo', 'Administrativo', 'check', NULL, NULL, 1, 0, NULL),
('cargar_imagenes', 'Cargar Imagenes', 'administrativo', 'Administrativo', 'upload-images', NULL, NULL, 1, 0, NULL),
('activos_fijos', 'Activos fijos', 'administrativo', 'Administrativo', 'fixed-assets', NULL, NULL, 1, 0, NULL),

-- Listados
('listado_tareas_usuario', 'Listado de Tareas Usuario', 'listados', 'Listados', 'task-list', NULL, NULL, 1, 0, NULL),
('listado_consultorios', 'Listado de Consultorios', 'listados', 'Listados', 'clinic-rooms', NULL, NULL, 1, 0, NULL),
('listado_locales', 'Listado de Locales', 'listados', 'Listados', 'locations', NULL, NULL, 1, 0, NULL),
('listado_zonas', 'Listado de Zonas', 'listados', 'Listados', 'zones', NULL, NULL, 1, 0, NULL),
('listado_cobradores', 'Listado de Cobradores', 'listados', 'Listados', 'collectors', NULL, NULL, 1, 0, NULL),
('listado_clientes', 'Listado Clientes', 'listados', 'Listados', 'clients', NULL, NULL, 1, 0, NULL),
('listado_productos', 'Listado de Productos', 'listados', 'Listados', 'product-list', NULL, NULL, 1, 0, NULL),
('listado_proveedor', 'Listado de Proveedor', 'listados', 'Listados', 'supplier-list', NULL, NULL, 1, 0, NULL),
('listado_vendedores', 'Listado de Vendedores', 'listados', 'Listados', 'seller-list', NULL, NULL, 1, 0, NULL),
('listado_caja', 'Listado de Caja', 'listados', 'Listados', 'cashbox-list', NULL, NULL, 1, 0, NULL),
('lista_factura_habilitadas', 'Lista de Factura Habilitadas', 'listados', 'Listados', 'invoice-list', NULL, NULL, 1, 0, NULL),
('lista_tipos_pago', 'Lista de Tipos de pago', 'listados', 'Listados', 'payment-types', NULL, NULL, 1, 0, NULL),
('lista_bancos', 'Lista de bancos', 'listados', 'Listados', 'banks', NULL, NULL, 1, 0, NULL),
('trabajos_mecanicos_dentales', 'Trabajos de Mecanicos Dentales', 'listados', 'Listados', 'dental-mechanic-work', NULL, NULL, 1, 0, NULL),
('listado_mecanicos_dentales', 'Listado de Mecanicos Dentales', 'listados', 'Listados', 'dental-mechanics', NULL, NULL, 1, 0, NULL),

-- Informes
('imprimir_precio', 'Imprimir Precio', 'informes', 'Informes', 'report', NULL, NULL, 1, 0, NULL),
('informe_general_cuentas', 'Informe General de Cuentas', 'informes', 'Informes', 'report', NULL, NULL, 1, 0, NULL),
('informe_evaluacion', 'Informe Evaluacion', 'informes', 'Informes', 'report', NULL, NULL, 1, 0, NULL),
('informe_inventario', 'Informe de Inventario', 'informes', 'Informes', 'report', NULL, NULL, 1, 0, NULL),
('informe_ganancia_venta', 'Informe de Gan. por venta', 'informes', 'Informes', 'report', NULL, NULL, 1, 0, NULL),
('informe_prod_comprados', 'Informe de Prod. Comprados', 'informes', 'Informes', 'report', NULL, NULL, 1, 0, NULL),
('informe_prod_vendidos', 'informe de Prod. Vendidos', 'informes', 'Informes', 'report', NULL, NULL, 1, 0, NULL),
('informe_ventas_canceladas', 'Informe de Ventas Canceladas', 'informes', 'Informes', 'report', NULL, NULL, 1, 0, NULL),
('informe_comision_cobrador', 'Informe de Comisión Cobrador', 'informes', 'Informes', 'report', NULL, NULL, 1, 0, NULL),
('informe_vendedores', 'Informe de Vendedores', 'informes', 'Informes', 'report', NULL, NULL, 1, 0, NULL),
('informe_pagos_eliminados', 'Informe de Pagos Eliminados', 'informes', 'Informes', 'report', NULL, NULL, 1, 0, NULL),
('catalogo', 'Catalogo', 'informes', 'Informes', 'catalog', NULL, NULL, 1, 0, NULL),
('clientes_inactivos', 'Clientes inactivos', 'informes', 'Informes', 'inactive-clients', NULL, NULL, 1, 0, NULL),
('productos_despachados', 'Productos Despachados', 'informes', 'Informes', 'dispatched-products', NULL, NULL, 1, 0, NULL),
('informe_compras_eliminados', 'Informe de Compras Eliminados', 'informes', 'Informes', 'report', NULL, NULL, 1, 0, NULL),
('informe_metas_vendedores', 'Informe Metas Vendedores', 'informes', 'Informes', 'seller-goals', NULL, NULL, 1, 0, NULL),
('clientes_morosos', 'Clientes Morosos', 'informes', 'Informes', 'late-clients', NULL, NULL, 1, 0, NULL),
('auditoria_producto', 'Auditoria Producto', 'informes', 'Informes', 'product-audit', NULL, NULL, 1, 0, NULL),
('cumpleanos_clientes', 'Cumpleaños Clientes', 'informes', 'Informes', 'birthday', NULL, NULL, 1, 0, NULL),
('informe_agenda_clientes', 'Agenda Clientes', 'informes', 'Informes', 'client-agenda-report', NULL, NULL, 1, 0, NULL),
('clientes_fieles', 'Clientes Fieles', 'informes', 'Informes', 'loyal-clients', NULL, NULL, 1, 0, NULL),
('solicitud_descuento', 'Solicitud de Descuento', 'informes', 'Informes', 'discount-request', NULL, NULL, 1, 0, NULL),
('contabilidad_venta', 'Contabilidad - Venta', 'informes', 'Informes', 'accounting-sale', NULL, NULL, 1, 0, NULL),
('contabilidad_compra', 'Contabilidad - Compra', 'informes', 'Informes', 'accounting-purchase', NULL, NULL, 1, 0, NULL),
('informe_asistencia', 'Informe de asistencia', 'informes', 'Informes', 'attendance-report', NULL, NULL, 1, 0, NULL),
('informe_dictamenes', 'Informe de dictamenes', 'informes', 'Informes', 'dictamen-report', NULL, NULL, 1, 0, NULL),

-- Sistema
('usuarios', 'Usuarios', 'sistema', 'Sistema', 'users', NULL, NULL, 1, 0, NULL),
('mis_datos', 'Mis Datos', 'sistema', 'Sistema', 'my-data', NULL, NULL, 1, 0, NULL),
('listado_acceso', 'Listado de Acceso', 'sistema', 'Sistema', 'access-list', NULL, NULL, 1, 0, NULL),
('listado_niveles', 'Listado de Niveles', 'sistema', 'Sistema', 'levels', NULL, NULL, 1, 0, NULL)

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
