-- Sistema Telar - Agenda
-- Agrega AUSENTE al ENUM sin reemplazar estados adicionales que pudieran existir.
-- El script es idempotente: si AUSENTE ya existe, no modifica la tabla.

SET @telar_schema := DATABASE();
SET @telar_tipo_estado := NULL;
SET @telar_nullable_estado := NULL;
SET @telar_default_estado := NULL;
SET @telar_charset_estado := NULL;
SET @telar_collation_estado := NULL;
SET @telar_comment_estado := NULL;

SELECT COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, CHARACTER_SET_NAME, COLLATION_NAME, COLUMN_COMMENT
INTO @telar_tipo_estado, @telar_nullable_estado, @telar_default_estado,
     @telar_charset_estado, @telar_collation_estado, @telar_comment_estado
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @telar_schema
  AND TABLE_NAME = 'agenda'
  AND COLUMN_NAME = 'estado'
LIMIT 1;

SET @telar_nuevo_tipo_estado := IF(
    @telar_tipo_estado IS NOT NULL AND LOWER(LEFT(@telar_tipo_estado, 5)) = 'enum(',
    CONCAT(LEFT(@telar_tipo_estado, CHAR_LENGTH(@telar_tipo_estado) - 1), ',''AUSENTE'')'),
    NULL
);

SET @telar_sql := CASE
    WHEN @telar_tipo_estado IS NULL THEN
        'SELECT ''NO_APLICADO: no existe agenda.estado'' AS resultado'
    WHEN LOCATE('''AUSENTE''', UPPER(@telar_tipo_estado)) > 0 THEN
        'SELECT ''SIN_CAMBIOS: AUSENTE ya existe en agenda.estado'' AS resultado'
    WHEN @telar_nuevo_tipo_estado IS NULL THEN
        'SELECT ''NO_APLICADO: agenda.estado no es ENUM'' AS resultado'
    ELSE CONCAT(
        'ALTER TABLE `agenda` MODIFY COLUMN `estado` ',
        @telar_nuevo_tipo_estado,
        IF(@telar_charset_estado IS NULL, '', CONCAT(' CHARACTER SET ', @telar_charset_estado)),
        IF(@telar_collation_estado IS NULL, '', CONCAT(' COLLATE ', @telar_collation_estado)),
        IF(@telar_nullable_estado = 'NO', ' NOT NULL', ' NULL'),
        IF(
            @telar_default_estado IS NULL,
            IF(@telar_nullable_estado = 'YES', ' DEFAULT NULL', ''),
            CONCAT(
                ' DEFAULT ',
                IF(
                    LEFT(@telar_default_estado, 1) = '''' AND RIGHT(@telar_default_estado, 1) = '''',
                    @telar_default_estado,
                    QUOTE(@telar_default_estado)
                )
            )
        ),
        IF(@telar_comment_estado = '', '', CONCAT(' COMMENT ', QUOTE(@telar_comment_estado)))
    )
END;

PREPARE telar_stmt FROM @telar_sql;
EXECUTE telar_stmt;
DEALLOCATE PREPARE telar_stmt;

SELECT COLUMN_TYPE AS agenda_estado_tipo,
       LOCATE('''AUSENTE''', UPPER(COLUMN_TYPE)) > 0 AS ausente_disponible
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'agenda'
  AND COLUMN_NAME = 'estado';
