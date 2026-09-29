# Sistema Osman

Sistema de gestion de inventario y produccion para la **U.P.Q.L - Unidad de Produccion de Quimica**.

Permite controlar materias primas, proveedores, entradas y salidas de almacen,
recetas de produccion y usuarios, ademas de generar reportes en PDF.

> [!CAUTION]
> **No expongas esta aplicacion en internet tal como esta.** Conserva varias
> debilidades de seguridad conocidas que no se han corregido: inyeccion SQL en
> algunos archivos, contrasenas con `md5()`, respuestas de seguridad en texto
> plano y endpoints sin verificacion de sesion en el servidor. Esta pensado
> para una red interna controlada.
> El detalle esta en [Notas de seguridad](#notas-de-seguridad).
>
> Es material de uso **no comercial** (CC BY-NC-SA 4.0). La redistribucion
> debe mantener esa licencia y dar credito. Ver
> [NOTICIA-LICENCIAS.md](NOTICIA-LICENCIAS.md).

---

## Stack

| Componente  | Version / Detalle                          |
|-------------|--------------------------------------------|
| PHP         | 8.x (probado en 8.4)                        |
| Base datos  | MySQL 8+ / MariaDB 10.6+                    |
| Servidor    | Apache o Nginx con PHP-FPM                 |
| PDF         | TCPDF 6.x y FPDF 1.8x (incluidos)          |
| Frontend    | jQuery, Bootstrap 4.6, DataTables, SB Admin |

No requiere Composer: **tcpdf** y **fpdf** vienen incluidos en el repositorio.

---

## Requisitos

- PHP 8.0 o superior con las extensiones `mysqli`, `pdo_mysql`, `mbstring`, `gd`
- MySQL 5.7+ o MariaDB 10.4+
- Un servidor web (Nginx o Apache)

---

## Instalacion

### 1. Copiar los archivos

```bash
git clone <url-del-repositorio> sistema-osman
cd sistema-osman
```

### 2. Configurar las credenciales

Las credenciales **no** estan en el codigo. Se leen del archivo `.env`:

```bash
cp .env.example .env
```

Luego edita `.env` con los datos de tu servidor:

```ini
DB_HOST=localhost
DB_NAME=osman_db
DB_USER=osman_user
DB_PASS=tu_clave_aqui
```

> El archivo `.env` esta en `.gitignore` y jamas debe subirse al repositorio.

### 3. Crear la base de datos

```bash
mysql -u root -p < database/schema.sql
```

Esto crea la base `osman_db` con sus 10 tablas. No inserta ningun dato.

### 4. (Opcional) Cargar datos de prueba

```bash
mysql -u root -p osman_db < database/seed_demo.sql
```

Carga datos **ficticios** y un usuario de prueba:

| Campo    | Valor       |
|----------|-------------|
| Usuario  | `admin`     |
| Clave    | `osman2024` |

> **Cambia esa clave antes de cualquier uso real.**
> El archivo tambien crea un usuario `operador` con la misma clave.

### 5. Apuntar el servidor web

Configura el document root al directorio del proyecto y entra por `index.php`.

---

## Modulos

| Modulo          | Archivos principales                                                    |
|-----------------|-------------------------------------------------------------------------|
| Inicio          | `inicio.php`                                                            |
| Almacen         | `almacen.php`, `inventario.php`                                         |
| Entregas        | `entregas.php`, `registrar_entrega.php`, `añadir_entrega.php`, `editar_entrega.php` |
| Salidas         | `salidas.php`, `registro_salidas.php`, `registrar_salida.php`, `eliminar_salida.php` |
| Materia prima   | `materias_primas.php`, `añadir_materia_prima.php`, `editar_materia_prima.php` |
| Proveedores     | `proveedores.php`, `añadir_proveedor.php`, `editar_proveedores.php`     |
| Productos       | `productos.php`, `registro_producto.php`, `editar_receta.php`          |
| Usuarios        | `usuarios.php`, `añadir_usuario.php`, `editar_usuario.php`             |
| Autenticacion   | `index.php`, `login.php`, `salir.php`, `logout.php`, `recuperar.php`    |
| Reportes PDF    | `generar_pdf_inventario.php`, `generar_pdf_simple.php`                  |

---

## Estructura

```
sistema-osman/
├── config.php            # carga el .env y define DB_HOST, DB_NAME, DB_USER, DB_PASS
├── conexion.php          # conexion mysqli
├── db.php                # conexion PDO
├── database/
│   ├── schema.sql        # estructura de las 10 tablas (sin datos)
│   └── seed_demo.sql     # datos ficticios de demostracion
├── assets/               # imagenes y demos de graficos
├── css/                  # estilos SB Admin y DataTables
├── image/                # logos
├── js/                   # jQuery, Bootstrap, DataTables, Font Awesome
├── fpdf/                 # libreria PDF (incluida)
├── tcpdf/                # libreria PDF (incluida)
├── .env.example          # plantilla de configuracion
└── [paginas .php]        # modulos del sistema
```

---

## Base de datos

| Tabla             | Descripcion                              |
|-------------------|------------------------------------------|
| `usuarios`        | Usuarios del sistema y tipo de acceso    |
| `proveedores`     | Proveedores registrados                  |
| `materias_primas` | Materia prima disponible                 |
| `productos`       | Productos elaborados                     |
| `recetas`         | Receta: producto + materia prima         |
| `entregas`        | Entradas de material al almacen         |
| `detalle_entregas`| Detalle de cada entrega                  |
| `salidas`         | Salidas de material del almacen          |
| `detalle_salidas` | Detalle de cada salida                   |
| `inventario`      | Existencias actuales por materia prima   |

---

## Notas de seguridad

El sistema funciona tal cual, pero conviene conocer estos puntos antes de
usarlo en un entorno expuesto. **No se corrigieron** para preservar el
codigo original:

1. **Contrasenas con `md5()`.** `login.php`, `guardar_usuario.php`,
   `actualizar_usuario.php` y `update_clave.php` usan `md5()`, que no es
   seguro para contrasenas. El proyecto ya define `PASSWORD_BCRYPT` en
   `db.php`, pero no se usa. Migrar a `password_hash()` /
   `password_verify()` requiere regenerar los hashes existentes.

2. **Respuestas de seguridad en texto plano.** La tabla `usuarios` guarda
   `pregunta` y `respuesta` sin cifrar, y `validar_res.php` las compara
   directamente en la base de datos.

3. **Inyeccion SQL en `validar_res.php`.** La linea 11 interpola
   `$_POST['respuesta']` directamente en la consulta. Lo mismo ocurre en
   `guardar_usuario.php` (lineas 15 y 23) y `guardar_materia_prima.php`
   (lineas 10-11). El resto de los archivos ya usa sentencias preparadas.

4. **Control de acceso debil.** La proteccion de `header.php` se basa en un
   `alert()` y un `window.location` de JavaScript, sin `exit()` en el
   servidor, de modo que el contenido de la pagina se envia igual. Ademas,
   los endpoints que no incluyen `header.php` (por ejemplo `get_*.php`,
   `guardar_*.php`, `eliminar_*.php`) no verifican la sesion en absoluto.

5. **Credenciales en el historial de git.** Este repositorio se creo
   teniendo la clave de la base de datos escrita en el codigo. Si alguna vez
   se hace publico, hay que **rotar esa clave**.

### Archivo no funcional

`reporte.php` **no funciona** y devuelve error 500. Consulta las tablas
`asistencia`, `docente` y `secciones`, que no existen en `osman_db`: es codigo
perteneciente a otro proyecto (un sistema de control de asistencias) que se
copio aqui por error. Ningun enlace del sistema lo apunta y no forma parte del
menu, por lo que se conserva tal cual y puede eliminarse sin ningun efecto.

---

## Licencia

Este repositorio usa **licencias distintas segun el tipo de archivo**:

| Elemento                            | Licencia          |
|-------------------------------------|-------------------|
| `*.php`, `css/`, `js/`, `database/` | MIT               |
| `README.md`, `image/`, `assets/`    | CC BY-NC-SA 4.0   |
| `tcpdf/`                            | LGPL-3.0-or-later |
| `fpdf/`                             | MIT (propia)      |

- Codigo fuente: [`LICENSE`](LICENSE)
- Documentacion e imagenes: [`LICENSE-CC-BY-NC-SA-4.0.txt`](LICENSE-CC-BY-NC-SA-4.0.txt)
- Detalle completo: [`NOTICIA-LICENCIAS.md`](NOTICIA-LICENCIAS.md)

Las librerias de terceros conservan sus propias licencias y no se relicencian.
