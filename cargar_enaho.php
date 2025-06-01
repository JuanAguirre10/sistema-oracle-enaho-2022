<?php
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Configurar Laravel para script independiente
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Ruta del archivo ENAHO
$archivo_enaho = 'C:\Users\Juan\Downloads\2022\784-Modulo02\Enaho012022200.csv';

echo "🔥 Iniciando carga de datos ENAHO...\n";

try {
    // Verificar si existe el archivo
    if (!file_exists($archivo_enaho)) {
        die("❌ Error: No se encuentra el archivo $archivo_enaho\n");
    }

    // Abrir archivo CSV
    $handle = fopen($archivo_enaho, 'r');
    if (!$handle) {
        die("❌ Error: No se puede abrir el archivo\n");
    }

    // Leer encabezados
    $headers = fgetcsv($handle, 0, ',');
    echo "✅ Archivo abierto correctamente\n";
    echo "📊 Columnas encontradas: " . count($headers) . "\n";

    $contador = 0;
    $lote = 0;
    $registros_por_lote = 1000;

    // Leer datos línea por línea
    while (($data = fgetcsv($handle, 0, ',')) !== FALSE && $contador < 10000) {
        
        // Generar datos simulados para nombre, email, etc.
        $nombres = ['Juan', 'María', 'Carlos', 'Ana', 'Luis', 'Rosa', 'Pedro', 'Carmen', 'José', 'Elena'];
        $apellidos = ['García', 'López', 'Martínez', 'González', 'Pérez', 'Rodríguez', 'Sánchez', 'Torres'];
        
        $nombre_completo = $nombres[array_rand($nombres)] . ' ' . $apellidos[array_rand($apellidos)];
        $email = strtolower(str_replace(' ', '.', $nombre_completo)) . $contador . '@inei.gob.pe';
        $telefono = '9' . str_pad(rand(10000000, 99999999), 8, '0');
        
        // Obtener departamento según UBIGEO
        $ubigeo = isset($data[6]) ? $data[6] : '150101';
        $departamentos = [
            '01' => 'Amazonas', '02' => 'Áncash', '03' => 'Apurímac', '04' => 'Arequipa',
            '05' => 'Ayacucho', '06' => 'Cajamarca', '07' => 'Callao', '08' => 'Cusco',
            '09' => 'Huancavelica', '10' => 'Huánuco', '11' => 'Ica', '12' => 'Junín',
            '13' => 'La Libertad', '14' => 'Lambayeque', '15' => 'Lima', '16' => 'Loreto',
            '17' => 'Madre de Dios', '18' => 'Moquegua', '19' => 'Pasco', '20' => 'Piura',
            '21' => 'Puno', '22' => 'San Martín', '23' => 'Tacna', '24' => 'Tumbes', '25' => 'Ucayali'
        ];
        
        $codigo_depto = substr($ubigeo, 0, 2);
        $direccion = ($departamentos[$codigo_depto] ?? 'Lima') . ' - UBIGEO: ' . $ubigeo;

        // Insertar en Oracle
        DB::connection('oracle')->insert(
            'INSERT INTO tabla_enaho (id, anio, mes, conglome, vivienda, hogar, codperso, ubigeo, dominio, estrato, p201p, p203, nombre, email, telefono, direccion, fecha_creacion, facpob07) 
             VALUES (seq_tabla_enaho.NEXTVAL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, SYSDATE, ?)',
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
                isset($data[37]) ? $data[37] : 1.0           // FACPOB07
            ]
        );

        $contador++;
        
        // Mostrar progreso cada 1000 registros
        if ($contador % $registros_por_lote == 0) {
            $lote++;
            echo "📈 Procesados: $contador registros (Lote $lote)\n";
        }
    }

    fclose($handle);
    
    echo "✅ ¡PROCESO COMPLETADO!\n";
    echo "📊 Total de registros cargados: $contador\n";
    echo "🎯 Datos listos para procedimiento MERGE\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>