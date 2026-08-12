<?php

// La clave nunca debe exponerse en JavaScript ni registrarse en logs.
// Para cambiarla, generar SHA-256 de: HPS_CODIGO_SALT + '|' + nueva_clave.
define('HPS_CODIGO_SALT', 'hps-20260810');
define('HPS_CODIGO_HASH', '8bc4dce06e0287bb7fc412ca244a070dd5f4c81b06f12519574c3c5fc792527f');
define('HPS_MAX_INTENTOS', 5);
define('HPS_MINUTOS_BLOQUEO', 10);
