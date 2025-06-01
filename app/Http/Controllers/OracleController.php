<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OracleController extends Controller
{
    public function index()
{
    try {
        // Obtener datos ENAHO (mostrar solo los primeros 50)
        $tablaOrigen = DB::connection('oracle')->select('SELECT * FROM tabla_enaho WHERE ROWNUM <= 50 ORDER BY id');
        $tablaDestino = DB::connection('oracle')->select('SELECT * FROM tabla_destino ORDER BY id');
        
        return view('oracle.index', compact('tablaOrigen', 'tablaDestino'));
    } catch (\Exception $e) {
        return back()->with('error', 'Error al conectar con Oracle: ' . $e->getMessage());
    }
}

   public function actualizarTabla(Request $request)
{
    try {
        // Validar entrada
        $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:200'
        ]);

        // Insertar nuevo registro en tabla_enaho
        DB::connection('oracle')->insert(
            'INSERT INTO tabla_enaho (id, anio, mes, nombre, email, telefono, direccion, ubigeo) 
             VALUES (seq_tabla_enaho.NEXTVAL, 2022, 1, ?, ?, ?, ?, 150101)',
            [
                $request->nombre,
                $request->email,
                $request->telefono,
                $request->direccion
            ]
        );

        return redirect()->route('oracle.index')->with('success', 'Registro agregado exitosamente a tabla ENAHO');

    } catch (\Exception $e) {
        Log::error('Error al actualizar tabla: ' . $e->getMessage());
        return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
    }
}

    public function procesarMerge()
{
    try {
        DB::connection('oracle')->statement('BEGIN procesar_merge_enaho(); END;');
        
        return redirect()->route('oracle.index')
            ->with('success', 'Procedimiento MERGE ejecutado exitosamente - 10,000 registros ENAHO procesados');

    } catch (\Exception $e) {
        Log::error('Error en procedimiento MERGE: ' . $e->getMessage());
        return back()->with('error', 'Error al ejecutar MERGE: ' . $e->getMessage());
    }
}

    public function login()
    {
        return view('oracle.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'usuario' => 'required',
            'password' => 'required'
        ]);

        // Simulación de autenticación
        if ($request->usuario === 'admin' && $request->password === 'admin123') {
            session(['authenticated' => true]);
            return redirect()->route('oracle.index')->with('success', '¡Bienvenido! Login exitoso');
        }

        return back()->with('error', 'Credenciales incorrectas')->withInput();
    }

    public function logout()
    {
        session()->forget('authenticated');
        return redirect()->route('oracle.login')->with('success', 'Sesión cerrada exitosamente');
    }

    public function crearProcedimiento()
{
    try {
        $procedimiento = "
        CREATE OR REPLACE PROCEDURE procesar_merge_enaho AS
        BEGIN
            MERGE INTO tabla_destino dest
            USING tabla_enaho orig
            ON (dest.email = orig.email)
            WHEN MATCHED THEN
                UPDATE SET
                    dest.nombre = orig.nombre,
                    dest.telefono = orig.telefono,
                    dest.direccion = orig.direccion,
                    dest.fecha_creacion = orig.fecha_creacion,
                    dest.activo = 1
            WHEN NOT MATCHED THEN
                INSERT (id, nombre, email, telefono, direccion, fecha_creacion, activo)
                VALUES (seq_tabla_destino.NEXTVAL, orig.nombre, orig.email, orig.telefono, 
                       orig.direccion, orig.fecha_creacion, 1);
            
            COMMIT;
            
            DBMS_OUTPUT.PUT_LINE('MERGE completado: 10,000 registros ENAHO procesados');
        EXCEPTION
            WHEN OTHERS THEN
                ROLLBACK;
                RAISE_APPLICATION_ERROR(-20001, 'Error en MERGE: ' || SQLERRM);
        END procesar_merge_enaho;
        ";

        DB::connection('oracle')->statement($procedimiento);
        
        return redirect()->route('oracle.index')->with('success', 'Procedimiento almacenado creado exitosamente');

    } catch (\Exception $e) {
        Log::error('Error al crear procedimiento: ' . $e->getMessage());
        return back()->with('error', 'Error al crear procedimiento: ' . $e->getMessage());
    }
}
}