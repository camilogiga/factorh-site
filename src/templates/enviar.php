<?php
// Formulario de contacto de factorh.co
// El correo de destino se cambia en content/site.yml (datos.correo) o en el editor.
$destino = '{{a:site.datos.correo}}';

header('X-Content-Type-Options: nosniff');
$quiereJson = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

function responder($ok, $quiereJson) {
    if ($quiereJson) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($ok ? 200 : 400);
        echo json_encode(['ok' => $ok]);
    } else {
        header('Location: contacto.html' . ($ok ? '?enviado=1' : '?error=1'));
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { responder(false, $quiereJson); }

// Trampa para robots: un humano nunca llena este campo.
if (!empty($_POST['sitio_web'])) { responder(true, $quiereJson); }

function campo($n, $max = 200) {
    $v = isset($_POST[$n]) ? trim((string)$_POST[$n]) : '';
    $v = str_replace(["\r", "\0"], '', $v);
    return mb_substr($v, 0, $max);
}

$nombre   = campo('nombre');
$correo   = campo('correo');
$mensaje  = campo('mensaje', 3000);
$autoriza = campo('autorizacion');

if ($nombre === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL) || $autoriza !== 'si') {
    responder(false, $quiereJson);
}

$lineas = [
    'Escribe como'   => campo('tipo'),
    'Nombre'         => $nombre,
    'Especialidad o entidad' => campo('especialidad'),
    'Ciudad'         => campo('ciudad'),
    'Correo'         => $correo,
    'Teléfono'       => campo('telefono'),
    'Qué busca'      => campo('meta'),
    'Qué le interesa'=> campo('interes'),
    'Mensaje'        => $mensaje,
    'Autorizó tratamiento de datos (Ley 1581)' => 'Sí, ' . date('Y-m-d H:i'),
];
$cuerpo = "Nueva solicitud desde factorh.co\n\n";
foreach ($lineas as $k => $v) { $cuerpo .= $k . ': ' . ($v === '' ? '-' : $v) . "\n"; }

$asunto = '=?UTF-8?B?' . base64_encode('Nueva solicitud: ' . str_replace("\n", ' ', $nombre)) . '?=';
$cabeceras  = "From: Factor H <" . $destino . ">\r\n";
$cabeceras .= "Reply-To: " . $correo . "\r\n";
$cabeceras .= "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";

$ok = @mail($destino, $asunto, $cuerpo, $cabeceras, '-f' . $destino);
responder($ok, $quiereJson);
