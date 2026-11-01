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

// Envío autenticado por el correo de Hostinger, para que el mensaje salga firmado (DKIM) y no caiga en spam.
// La contraseña del buzón vive en smtp-config.php, FUERA de public_html y fuera de este repositorio:
//   <?php return ['usuario' => 'contacto@factorh.co', 'clave' => '...'];
$config = __DIR__ . '/../smtp-config.php';
$smtp = is_file($config) ? include $config : null;

if (is_array($smtp) && !empty($smtp['clave'])) {
    $ok = enviar_smtp($smtp, $destino, $asunto, $cuerpo, $correo);
} else {
    $cabeceras  = "From: Factor H <" . $destino . ">\r\n";
    $cabeceras .= "Reply-To: " . $correo . "\r\n";
    $cabeceras .= "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
    $ok = @mail($destino, $asunto, $cuerpo, $cabeceras, '-f' . $destino);
}
responder($ok, $quiereJson);

function enviar_smtp($smtp, $destino, $asunto, $cuerpo, $responder_a) {
    $usuario = isset($smtp['usuario']) ? $smtp['usuario'] : $destino;
    $host = isset($smtp['servidor']) ? $smtp['servidor'] : 'smtp.hostinger.com';
    $s = @stream_socket_client('ssl://' . $host . ':465', $errno, $errstr, 15);
    if (!$s) { error_log("enviar.php: no conecta a $host: $errstr"); return false; }
    stream_set_timeout($s, 15);
    $leer = function () use ($s) {
        $r = '';
        while (($l = fgets($s, 515)) !== false) { $r .= $l; if (isset($l[3]) && $l[3] === ' ') break; }
        return $r;
    };
    $paso = function ($cmd, $espera) use ($s, $leer) {
        if ($cmd !== null) fwrite($s, $cmd . "\r\n");
        $r = $leer();
        if (substr($r, 0, 3) !== $espera) { error_log('enviar.php SMTP: ' . trim($r)); return false; }
        return true;
    };
    $id = '<' . bin2hex(random_bytes(12)) . '@factorh.co>';
    $mensaje = "From: Factor H <$usuario>\r\nTo: <$destino>\r\nReply-To: <$responder_a>\r\nSubject: $asunto\r\n"
        . "Date: " . date('r') . "\r\nMessage-ID: $id\r\nMIME-Version: 1.0\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($cuerpo));
    $ok = $paso(null, '220') && $paso('EHLO factorh.co', '250') && $paso('AUTH LOGIN', '334')
        && $paso(base64_encode($usuario), '334') && $paso(base64_encode($smtp['clave']), '235')
        && $paso("MAIL FROM:<$usuario>", '250') && $paso("RCPT TO:<$destino>", '250')
        && $paso('DATA', '354') && $paso($mensaje . "\r\n.", '250');
    fwrite($s, "QUIT\r\n"); fclose($s);
    return $ok;
}
