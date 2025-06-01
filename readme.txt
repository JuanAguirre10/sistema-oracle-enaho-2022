================================================================================
                    SISTEMA ORACLE CON PROCEDIMIENTO MERGE
                         DATOS ENAHO 2022 - INEI PERU
================================================================================

DESCRIPCION:
Sistema web desarrollado en Laravel que permite la gestion de datos de la 
Encuesta Nacional de Hogares (ENAHO) 2022 del INEI Peru, implementando 
procedimientos almacenados Oracle con sentencia MERGE para transferir 
10,000 registros entre tablas.

================================================================================
REQUISITOS DEL SISTEMA
================================================================================

SOFTWARE NECESARIO:
- PHP 8.2 o superior
- Oracle Database 19c
- XAMPP (Apache + MySQL + PHP)
- Composer
- Laravel 10

EXTENSIONES PHP REQUERIDAS:
- oci8 (Oracle Client)
- pdo_oci (PDO Oracle Driver)

================================================================================
ESTRUCTURA DEL PROYECTO
================================================================================

oracle-app/
├── app/
│   └── Http/
│       └── Controllers/
│           └── OracleController.php     # Controlador principal
├── config/
│   └── database.php                     # Configuracion Oracle
├── resources/
│   └── views/
│       └── oracle/
│           ├── login.blade.php          # Vista login
│           └── index.blade.php          # Vista principal
├── routes/
│   └── web.php                          # Rutas del sistema
├── cargar_enaho_completo.php           # Script carga ENAHO
├── test_enaho.php                      # Script prueba
└── .env                                # Variables entorno

================================================================================
CONFIGURACION ORACLE
================================================================================

1. CREAR USUARIO EN ORACLE:
   SQL> CREATE USER JUAN IDENTIFIED BY Juan1234;
   SQL> GRANT CONNECT, RESOURCE TO JUAN;
   SQL> GRANT CREATE SESSION TO JUAN;

2. CREAR TABLAS:
   
   -- Tabla origen (ENAHO)
   CREATE TABLE tabla_enaho (
       id NUMBER PRIMARY KEY,
       anio NUMBER(4),
       mes NUMBER(2),
       conglome NUMBER,
       vivienda NUMBER,
       hogar NUMBER,
       codperso NUMBER,
       ubigeo NUMBER(6),
       dominio NUMBER,
       estrato NUMBER,
       p201p NUMBER,
       p203 NUMBER,
       nombre VARCHAR2(100),
       email VARCHAR2(100),
       telefono VARCHAR2(20),
       direccion VARCHAR2(200),
       fecha_creacion DATE DEFAULT SYSDATE,
       facpob07 NUMBER(10,2)
   );

   -- Tabla destino
   CREATE TABLE tabla_destino (
       id NUMBER PRIMARY KEY,
       nombre VARCHAR2(100) NOT NULL,
       email VARCHAR2(100) UNIQUE,
       telefono VARCHAR2(20),
       direccion VARCHAR2(200),
       fecha_creacion DATE DEFAULT SYSDATE,
       activo NUMBER(1) DEFAULT 1
   );

   -- Secuencias
   CREATE SEQUENCE seq_tabla_enaho START WITH 1 INCREMENT BY 1;
   CREATE SEQUENCE seq_tabla_destino START WITH 1 INCREMENT BY 1;

3. PROCEDIMIENTO ALMACENADO MERGE:
   Ver archivo: procedimiento_merge.sql

================================================================================
INSTALACION
================================================================================

1. CLONAR O DESCARGAR EL PROYECTO:
   Extraer el archivo ZIP en C:\xampp\htdocs\

2. INSTALAR DEPENDENCIAS:
   cd C:\xampp\htdocs\oracle-app
   composer install

3. CONFIGURAR ORACLE DRIVER:
   composer require yajra/laravel-oci8

4. CONFIGURAR VARIABLES DE ENTORNO:
   Editar archivo .env:
   
   DB_CONNECTION=oracle
   DB_HOST=localhost
   DB_PORT=1521
   DB_DATABASE=orcl
   DB_SERVICE_NAME=orcl
   DB_USERNAME=JUAN
   DB_PASSWORD=Juan1234

5. CONFIGURAR DATABASE.PHP:
   Verificar configuracion Oracle en config/database.php

6. CARGAR DATOS ENAHO:
   php cargar_enaho_completo.php
   
   Esto cargara 10,000 registros del archivo ENAHO CSV

================================================================================
USO DEL SISTEMA
================================================================================

1. INICIAR SERVIDOR:
   php artisan serve
   
   El sistema estara disponible en: http://localhost:8000

2. CREDENCIALES DE ACCESO:
   Usuario: admin
   Password: admin123

3. FUNCIONALIDADES:
   - Login de usuario
   - Visualizar tablas origen y destino
   - Agregar nuevos registros via formulario
   - Crear procedimiento almacenado MERGE
   - Ejecutar transferencia masiva de datos
   - Ver estadisticas en tiempo real

================================================================================
ARCHIVO ENAHO REQUERIDO
================================================================================

ARCHIVO: Enaho01-2022-200.csv
UBICACION: C:\Users\Juan\Downloads\2022\784-Modulo02\

FUENTE: Instituto Nacional de Estadistica e Informatica (INEI)
DATASET: Encuesta Nacional de Hogares 2022
REGISTROS: 121,253 (se procesan 10,000)

CAMPOS PRINCIPALES:
- AÑO, MES, CONGLOME, VIVIENDA, HOGAR
- CODPERSO, UBIGEO, DOMINIO, ESTRATO
- P201P, P203 (variables socioeconomicas)
- FACPOB07 (factor de expansion poblacional)

================================================================================
PROCEDIMIENTO MERGE
================================================================================

El procedimiento almacenado implementa la logica MERGE de Oracle para:

1. COMPARAR registros entre tabla_enaho y tabla_destino usando email
2. ACTUALIZAR registros existentes con nueva informacion
3. INSERTAR registros nuevos que no existen en destino
4. MANEJAR transacciones con COMMIT/ROLLBACK
5. GENERAR logs de ejecucion

SINTAXIS MERGE:
MERGE INTO tabla_destino dest
USING tabla_enaho orig
ON (dest.email = orig.email)
WHEN MATCHED THEN UPDATE SET...
WHEN NOT MATCHED THEN INSERT...

================================================================================
ESTRUCTURA DE DIRECTORIOS
================================================================================

ARCHIVOS CLAVE:

app/Http/Controllers/OracleController.php
├── index()                 # Dashboard principal
├── login()                 # Pantalla login
├── authenticate()          # Validar credenciales
├── actualizarTabla()       # Insertar nuevos registros
├── crearProcedimiento()    # Crear procedimiento MERGE
├── procesarMerge()         # Ejecutar MERGE
└── logout()                # Cerrar sesion

resources/views/oracle/
├── login.blade.php         # Interfaz login
└── index.blade.php         # Dashboard principal

routes/web.php              # Definicion de rutas
config/database.php         # Configuracion Oracle

SCRIPTS AUXILIARES:
├── cargar_enaho_completo.php    # Carga masiva ENAHO
├── test_enaho.php               # Prueba conexion
└── procedimiento_merge.sql      # Codigo procedimiento

================================================================================
VERIFICACION DE FUNCIONAMIENTO
================================================================================

1. PROBAR CONEXION ORACLE:
   php test_enaho.php

2. VERIFICAR DATOS CARGADOS:
   SQL> SELECT COUNT(*) FROM tabla_enaho;
   Resultado esperado: 10000

3. VERIFICAR PROCEDIMIENTO:
   SQL> SELECT object_name FROM user_objects WHERE object_name = 'PROCESAR_MERGE_ENAHO';

4. PROBAR MERGE MANUAL:
   SQL> EXEC procesar_merge_enaho;

================================================================================
SOLUCION DE PROBLEMAS
================================================================================

ERROR: ORA-12514 TNS listener
SOLUCION: Verificar service_name en .env (debe ser 'orcl')

ERROR: Class 'Yajra\Pdo\Oci8' not found
SOLUCION: composer require yajra/laravel-oci8

ERROR: Archivo ENAHO no encontrado
SOLUCION: Verificar ruta exacta del CSV en script de carga

ERROR: Tabla no existe
SOLUCION: Crear tablas en Oracle segun scripts SQL

ERROR: Procedimiento no ejecuta
SOLUCION: Verificar que el procedimiento este compilado correctamente

================================================================================
LOGS Y DEPURACION
================================================================================

LOGS LARAVEL: storage/logs/laravel.log
LOGS ORACLE: Verificar en SQL*Plus con DBMS_OUTPUT.PUT_LINE

COMANDOS UTILES:
SQL> SET SERVEROUTPUT ON;
SQL> EXEC procesar_merge_enaho;

VERIFICAR TABLAS:
SQL> SELECT table_name FROM user_tables;
SQL> SELECT object_name, status FROM user_objects WHERE object_type = 'PROCEDURE';

================================================================================
DATOS TECNICOS
================================================================================

DESARROLLADO CON:
- Laravel Framework 10.x
- PHP 8.2.12
- Oracle Database 19c Enterprise Edition
- Bootstrap 5.3.0 (Frontend)
- Font Awesome 6.0.0 (Iconos)

PATRONES IMPLEMENTADOS:
- MVC (Model-View-Controller)
- Repository Pattern
- Service Layer
- Exception Handling

CARACTERISTICAS:
- Interfaz responsive
- Validacion de formularios
- Manejo de errores
- Logs de auditoria
- Transacciones seguras

================================================================================
CREDITOS
================================================================================

DATOS: Instituto Nacional de Estadistica e Informatica (INEI) - Peru
DATASET: Encuesta Nacional de Hogares (ENAHO) 2022
TECNOLOGIA: Laravel + Oracle Database
PROPOSITO: Sistema academico de gestion de datos masivos
