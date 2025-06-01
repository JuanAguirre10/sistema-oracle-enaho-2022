<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$archivo = 'C:\Users\Juan\Downloads\2022\784-Modulo02\Enaho01-2022-200.csv';

echo "🔥 Probando conexión y carga...\n";

try {
    // Probar conexión Oracle
    $test = DB::connection('oracle')->select('SELECT * FROM dual');
    echo "✅ Conexión Oracle OK\n";
    
    // Verificar tabla
    $tabla = DB::connection('oracle')->select("SELECT table_name FROM user_tables WHERE table_name = 'TABLA_ENAHO'");
    if (empty($tabla)) {
        echo "❌ Error: Tabla TABLA_ENAHO no existe. Créala primero en SQL*Plus\n";
        exit;
    }
    echo "✅ Tabla TABLA_ENAHO existe\n";
    
    // Leer solo 10 registros de prueba
    if (file_exists($archivo)) {
        echo "✅ Archivo encontrado\n";
        
        $handle = fopen($archivo, 'r');
        $headers = fgetcsv($handle, 0, ',');
        echo "📊 Columnas: " . implode(', ', array_slice($headers, 0, 5)) . "...\n";
        
        // Insertar solo 5 registros de prueba
        for ($i = 0; $i < 5; $i++) {
            $data = fgetcsv($handle, 0, ',');
            if ($data === FALSE) break;
            
            $nombre = "Persona ENAHO " . ($i + 1);
            $email = "enaho" . ($i + 1) . "@inei.gob.pe";
            $telefono = "9876543" . sprintf("%02d", $i + 1);
            $direccion = "Lima - UBIGEO: " . (isset($data[6]) ? $data[6] : '150101');
            
            DB::connection('oracle')->insert(
                'INSERT INTO tabla_enaho (id, anio, mes, nombre, email, telefono, direccion, ubigeo) 
                 VALUES (seq_tabla_enaho.NEXTVAL, ?, ?, ?, ?, ?, ?, ?)',
                [
                    isset($data[0]) ? $data[0] : 2022,
                    isset($data[1]) ? $data[1] : 1,
                    $nombre,
                    $email,
                    $telefono,
                    $direccion,
                    isset($data[6]) ? $data[6] : 150101
                ]
            );
            
            echo "✅ Registro " . ($i + 1) . " insertado\n";
        }
        
        fclose($handle);
        echo "🎯 Prueba completada exitosamente\n";
        
    } else {
        echo "❌ Archivo no encontrado: $archivo\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>