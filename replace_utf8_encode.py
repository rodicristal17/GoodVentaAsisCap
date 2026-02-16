import os
import re

# Configuración de la ruta
DIRECTORIO_OBJETIVO = r'c:\wamp64\www\GoodVentaAsisCap\php_system'

def procesar_archivos(directorio):
    # Lista de reemplazos con sus equivalencias correctas
    # utf8_encode: ISO-8859-1 -> UTF-8
    # utf8_decode: UTF-8 -> ISO-8859-1
    reemplazos = [
        (re.compile(r'utf8_encode\s*\((.*?)\)', re.IGNORECASE), r"mb_convert_encoding((string)(\1), 'UTF-8', 'ISO-8859-1')"),
        (re.compile(r'utf8_decode\s*\((.*?)\)', re.IGNORECASE), r"mb_convert_encoding((string)(\1), 'ISO-8859-1', 'UTF-8')")
    ]

    if not os.path.exists(directorio):
        print(f"El directorio no existe: {directorio}")
        return

    print(f"Iniciando proceso en: {directorio}")

    for root, dirs, files in os.walk(directorio):
        for filename in files:
            if filename.endswith(".php"):
                filepath = os.path.join(root, filename)
                
                try:
                    # Intentar leer con utf-8, si falla probar con latin-1 (común en sistemas legacy)
                    try:
                        with open(filepath, 'r', encoding='utf-8') as f:
                            contenido = f.read()
                        encoding_usado = 'utf-8'
                    except UnicodeDecodeError:
                        with open(filepath, 'r', encoding='latin-1') as f:
                            contenido = f.read()
                        encoding_usado = 'latin-1'
                    
                    # Realizar los reemplazos secuencialmente
                    nuevo_contenido = contenido
                    total_cambios = 0
                    
                    for patron, reemplazo in reemplazos:
                        nuevo_contenido, num_reemplazos = patron.subn(reemplazo, nuevo_contenido)
                        total_cambios += num_reemplazos
                    
                    if total_cambios > 0:
                        with open(filepath, 'w', encoding=encoding_usado) as f:
                            f.write(nuevo_contenido)
                        print(f"Modificado: {filename} ({total_cambios} cambios)")
                        
                except Exception as e:
                    print(f"Error al procesar {filename}: {e}")

if __name__ == "__main__":
    procesar_archivos(DIRECTORIO_OBJETIVO)
