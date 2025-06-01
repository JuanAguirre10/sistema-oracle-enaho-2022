<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$archivo = 'C:\Users\Juan\Downloads\2022\784-Modulo02\Enaho01-2022-200.csv';

echo "🔥 Iniciando carga masiva de datos ENAHO...\n";

try {
    // Limpiar tabla anterior
    DB::connection('oracle')->statement('DELETE FROM tabla_enaho');
    echo "🗑️ Tabla limpiada\n";
    
    $handle = fopen($archivo, 'r');
    $headers = fgetcsv($handle, 0, ','); // Saltar encabezados
    
    $contador = 0;
    $target = 10000; // Cargar 10,000 registros
    
    echo "📊 Cargando $target registros...\n";
    
    // Nombres y apellidos peruanos
    $nombres = ['Juan', 'María', 'Carlos', 'Ana', 'Luis', 'Rosa', 'Pedro', 'Carmen', 'José', 'Elena', 
               'Miguel', 'Patricia', 'Roberto', 'Laura', 'Fernando', 'Claudia', 'Ricardo', 'Isabel'];
    $apellidos = ['García', 'López', 'Martínez', 'González', 'Pérez', 'Rodríguez', 'Sánchez', 'Torres',
                 'Ramírez', 'Flores', 'Rivera', 'Gómez', 'Díaz', 'Morales', 'Jiménez', 'Herrera'];
    
    // Departamentos del Perú
    $departamentos = [
        '01' => 'Amazonas', '02' => 'Áncash', '03' => 'Apurímac', '04' => 'Arequipa',
        '05' => 'Ayacucho', '06' => 'Cajamarca', '07' => 'Callao', '08' => 'Cusco',
        '09' => 'Huancavelica', '10' => 'Huánuco', '11' => 'Ica', '12' => 'Junín',
        '13' => 'La Libertad', '14' => 'Lambayeque', '15' => 'Lima', '16' => 'Loreto',
        '17' => 'Madre de Dios', '18' => 'Moquegua', '19' => 'Pasco', '20' => 'Piura',
        '21' => 'Puno', '22' => 'San Martín', '23' => 'Tacna', '24' => 'Tumbes', '25' => 'Ucayali'
    ];
    
    while (($data = fgetcsv($handle, 0, ',')) !== FALSE && $contador < $target) {
        
        // Generar datos realistas
        $nombre_completo = $nombres[array_rand($nombres)] . ' ' . $apellidos[array_rand($apellidos)];
        $email = strtolower(str_replace(' ', '.', $nombre_completo)) . ($contador + 1) . '@inei.gob.pe';
        $telefono = '9' . str_pad(rand(10000000, 99999999), 8, '0');
        
        // Procesar UBIGEO
        $ubigeo = isset($data[6]) ? $data[6] : '150101';
        $codigo_depto = substr(str_pad($ubigeo, 6, '0', STR_PAD_LEFT), 0, 2);
        $departamento = $departamentos[$codigo_depto] ?? 'Lima';
        $direccion = $departamento . ' - UBIGEO: ' . $ubigeo;
        
        // Insertar registro
        DB::connection('oracle')->insert(
            'INSERT INTO tabla_enaho (id, anio, mes, conglome, vivienda, hogar, codperso, ubigeo, dominio, estrato, p201p, p203, nombre, email, telefono, direccion, facpob07) 
             VALUES (seq_tabla_enaho.NEXTVAL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                isset($data[0]) ? $data[0] : 2022,           // AÑO
                isset($data[1]) ? $data[1] : 1,              // MES
                isset($data[2]) ? $data[2] : 0,              // CONGLOME
                isset($data[3]) ? $data[3] : 0,              // VIVIENDA
                isset($data[4]) ? $data[4] : 0,              // HOGAR
                isset($data[5]) ? $data[5] : 0,              // CODPERSO
                $ubigeo,                                      // UBIGEO
                isset($data[7]) ? $data[7] : 0,              // DOMINIO
                isset($data[8]) ? $data[8] : 0,              // ESTRATO
                isset($data[9]) ? $data[9] : 0,              // P201P
                isset($data[10]) ? $data[10] : 0,            // P203
                $nombre_completo,                             // NOMBRE
                $email,                                       // EMAIL
                $telefono,                                    // TELEFONO
                $direccion,                                   // DIRECCION
                isset($data[37]) ? floatval($data[37]) : 1.0 // FACPOB07
            ]
        );
        
        $contador++;
        
        // Mostrar progreso cada 500 registros
        if ($contador % 500 == 0) {
            echo "📈 Procesados: $contador/$target registros (" . round(($contador/$target)*100, 1) . "%)\n";
        }
    }
    
    fclose($handle);
    
    echo "✅ ¡CARGA COMPLETADA!\n";
    echo "📊 Total de registros cargados: $contador\n";
    echo "🎯 Datos reales ENAHO listos para procedimiento MERGE\n";
    
    // Mostrar estadísticas
    $total = DB::connection('oracle')->select('SELECT COUNT(*) as total FROM tabla_enaho')[0]->total;
    echo "📋 Total en base de datos: $total registros\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>