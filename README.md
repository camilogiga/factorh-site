# factorh.co

Sitio web de Factor H Comunicaciones S.A.S. Cada cambio guardado en este repositorio se construye y se publica solo en Hostinger, normalmente en menos de dos minutos.

## Cambiar textos y fotos (sin código)

1. Entra a https://app.pagescms.org y conéctate con GitHub.
2. Abre **factorh-site**. En el menú de la izquierda está cada página, más **Menú, pie de página y correo**, que aplica a todas.
3. Dentro de cada página los textos están agrupados por sección. Cambia el que quieras y haz clic en **Save**.
4. Para cambiar una foto, haz clic en ella y sube otra o escoge una de la galería.

El sitio se actualiza unos dos minutos después de guardar. El avance se ve en la pestaña **Actions** de GitHub: verde significa publicado.

**Formato de los textos:**
- `**así**` pone el texto en negrita.
- `[[así]]` marca la palabra resaltada de un título (por ejemplo, la que lleva la barra de color en la portada). Si la quitas, el título se ve sin la barra.
- `_así_` pone el texto en cursiva.
- Un salto de línea en la caja es un salto de línea en la página.

**Fotos:** JPG o PNG, idealmente de no más de 2000 px de ancho y menos de 500 KB. Las fotos del sitio son ilustrativas: no uses fotos de pacientes reales sin su autorización escrita.

**Correo de contacto:** se cambia en **Menú, pie de página y correo → Datos de contacto**. Ese correo aparece en los enlaces del sitio y es el que recibe las solicitudes del formulario. Debe ser un buzón del dominio factorh.co, porque el formulario envía desde esa misma dirección.

**Google:** cada página tiene un grupo **Google y pestaña del navegador** con el título y la descripción que aparecen en los resultados de búsqueda y al compartir el enlace.

## Configuración, una sola vez

**Publicación por FTP a Hostinger.** En GitHub, abre **Settings → Secrets and variables → Actions** y agrega tres *secrets* con los datos de hPanel, en **Archivos → Cuentas FTP**:

| Secret | Valor |
|---|---|
| `FTP_SERVER` | Servidor FTP, por ejemplo `ftp.factorh.co` o la IP que muestra hPanel |
| `FTP_USERNAME` | Usuario FTP, por ejemplo `u123456789.factorh.co` |
| `FTP_PASSWORD` | Contraseña FTP |

Opcionales, como *variables* en la misma página:
- `FTP_SERVER_DIR`: la carpeta donde se sube, terminada en `/`. Por defecto es `/public_html/`, porque la cuenta FTP de Hostinger entra directamente a la carpeta del dominio factorh.co. Si la publicación termina en otra carpeta, la acción **Ver carpetas FTP** (pestaña Actions → Run workflow) muestra qué ve el usuario FTP.
- `FTP_PROTOCOL`: `ftps` por defecto. Cámbialo a `ftp` solo si la publicación falla con un error de TLS.

Nunca pongas contraseñas en los archivos del repositorio; van solo en los secrets.

**Correo del formulario (para que no llegue a spam).** El formulario envía con la cuenta contacto@factorh.co por el servidor de correo de Hostinger, así sale firmado. Necesita la contraseña de ese buzón en un archivo que **no** está en este repositorio:

1. En hPanel → Administrador de archivos, sube un nivel desde `public_html` (a la carpeta donde están `public_html` y `DO_NOT_UPLOAD_HERE`).
2. Crea ahí el archivo `smtp-config.php` con este contenido, cambiando la contraseña:

```php
<?php return ['usuario' => 'contacto@factorh.co', 'clave' => 'LA_CONTRASEÑA_DEL_BUZÓN'];
```

Si el archivo no existe, el formulario sigue funcionando con el envío básico del servidor, que suele caer en spam. Si cambias la contraseña del buzón, cámbiala también en ese archivo.

**Editor.** La primera vez que abras app.pagescms.org te pide instalar su app de GitHub. Autorízala para este repositorio.

## Cómo está hecho

```
content/*.yml          todos los textos y rutas de fotos, un archivo por página; site.yml es menú, pie y correo
static/img/            las fotos
static/                lo que se copia tal cual: assets (animaciones), .htaccess, robots.txt, sitemap.xml, íconos, og.png
src/templates/         el diseño de cada página, con espacios {{t:...}} para textos y {{img:...}} para fotos
src/templates/enviar.php  el formulario de contacto (usa mail() de Hostinger)
src/build.py           construye site/
src/cms_config.py      regenera .pages.yml (los formularios del editor) después de cambiar una plantilla
.github/workflows/     la publicación automática
```

Ver el sitio en tu computador:

```bash
pip install pyyaml
python3 src/build.py
python3 -m http.server 8000 -d site      # http://localhost:8000
```

El formulario de contacto solo funciona en el servidor (necesita PHP y correo).
