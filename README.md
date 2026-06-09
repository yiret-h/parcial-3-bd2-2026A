# Proyecto N°12 — Veterinaria

## Descripción
Este aplicativo web gestiona las operaciones de una clínica veterinaria. Permite registrar dueños, mascotas, citas, consultas, historial de vacunación, tratamientos y más.

## Requisitos Previos
- Servidor web (Apache) con PHP .
- Base de datos MySQL o PostgreSQL (probado en MySQL).
- XAMPP, WAMP o equivalente.

## Instalación y Ejecución
1. Copiar la carpeta del proyecto dentro del directorio de despliegue del servidor web (por ejemplo, `htdocs` en XAMPP).
2. Abrir phpMyAdmin o su gestor de base de datos preferido.
3. Crear una base de datos llamada `clinica_veterinaria`.
4. Importar el script `ddl/Base de datos.sql` en la base de datos recién creada.
5. **¡IMPORTANTE!** Configurar las credenciales de la base de datos en el archivo `app/config/conexion.php`. Actualmente usa el usuario `admin_vet`, deberás cambiarlo a `root` (o el usuario de tu servidor local) para poder probar la aplicación.
6. Acceder a la aplicación desde el navegador web mediante `http://localhost/parcial-3-bd2-2026A/entregas/yiret-arenas-proyecto12/app/`.

## Datos de Acceso
**Usuario**: admin
**Contraseña**: admin (la contraseña está cifrada en la base de datos mediante BCRYPT, verificar el código si se configuró distinto o usar las funciones de registro si existen).

## Requisitos Cumplidos
- RF1: Registro de dueños y mascotas.
- RF2: Catálogo de especies y razas.
- RF3: Registro de consultas médicas.
- RF4: Registro de vacunas aplicadas en carnet.
- RF5: Tratamientos y medicamentos.
- RF6: Catálogo de medicamentos.
- RF7: Historial de mascotas.
