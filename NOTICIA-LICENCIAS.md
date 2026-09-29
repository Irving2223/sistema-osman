# Aviso de licencias

Este repositorio tiene **licencias distintas** segun el tipo de archivo. Esta
documentacion explica cual aplica a cada parte.

---

## 1. Codigo fuente propio — MIT

**Alcance:** todos los archivos `*.php`, las carpetas `css/` y `js/`, y los
scripts `database/schema.sql` y `database/seed_demo.sql`.

Copyright (c) 2025 U.P.Q.L - Unidad de Produccion de Quimica

Texto integro en [`LICENSE`](LICENSE).

---

## 2. Documentacion e imagenes — CC BY-NC-SA 4.0

**Alcance:** `README.md`, `image/` y `assets/`.

**Attribution-NonCommercial-ShareAlike 4.0 International**

Texto integro en [`LICENSE-CC-BY-NC-SA-4.0.txt`](LICENSE-CC-BY-NC-SA-4.0.txt).
Version resumida: <https://creativecommons.org/licenses/by-nc-sa/4.0/>

### Que significa en la practica

- **BY (Atribucion)** — hay que indicar la autoria y la licencia al reutilizar.
- **NC (No comercial)** — no se permite el uso comercial.
- **SA (Compartir igual)** — las obras derivadas deben llevar la misma licencia.

Al distribuir este material hay que incluir el aviso de atribucion:

> Sistema de inventario U.P.Q.L - Unidad de Produccion de Quimica.
> Licencia CC BY-NC-SA 4.0.

---

## 3. Librerias de terceros — licencias propias

Estas librerias **no** se relicencian con la MIT ni con la CC. Conservan la
licencia con la que fueron publicadas:

### TCPDF 6.10.0 — LGPL-3.0-or-later

- Carpeta: `tcpdf/`
- Autoria: Nicola Asuni — Tecnick.com LTD
- Licencia: LGPL-3.0-or-later (texto en `tcpdf/LICENSE.TXT`)

### FPDF 1.8 — MIT

- Carpeta: `fpdf/`
- Autoria: Olivier Plathey
- Licencia: texto en `fpdf/license.txt`

Ambas se distribuye sin garantia. La LGPL-3.0 de TCPDF obliga ademas a
mantenerla como libreria separada y reversible, y a permitir su reemplazo por
una version modificada del propio usuario.

---

## Nota sobre las imagenes de `image/` y `assets/`

Los logotipos incluidos en `image/` corresponden a la unidad institutional y
**no son necesariamente obra de este proyecto**. Antes de distribuir el
repositorio fuera de la institucion, conviene confirmar con la unidad quien
corresponde la autoria de esos logotipos: puede haber restricciones que la
licencia CC BY-NC-SA 4.0 no pueda cubrir por si sola.

---

## Resumen

| Elemento                              | Licencia              | Texto                                  |
|---------------------------------------|-----------------------|----------------------------------------|
| `*.php`, `css/`, `js/`, `database/`    | MIT                   | `LICENSE`                              |
| `README.md`, `image/`, `assets/`       | CC BY-NC-SA 4.0       | `LICENSE-CC-BY-NC-SA-4.0.txt`          |
| `tcpdf/`                               | LGPL-3.0-or-later     | `tcpdf/LICENSE.TXT`                    |
| `fpdf/`                                | MIT                   | `fpdf/license.txt`                     |
