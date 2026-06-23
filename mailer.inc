<?php
/**
 * mailer.inc
 * Helper para enviar mails usando la API de SendGrid.
 * Incluir con include_once('../mailer.inc') desde cualquier módulo.
 *
 * - En producción (Render): configurá SENDGRID_API_KEY como variable de entorno.
 * - En local (XAMPP): podés poner la key directamente en el segundo parámetro del ?:
 *
 * ESTE ARCHIVO NO DEBE SUBIRSE A GIT. Verificá que esté en .gitignore.
 */

define('SENDGRID_API_KEY', getenv('SENDGRID_API_KEY') ?: 'SG.PONETU-NUEVA-KEY-AQUI');
define('MAIL_REMITENTE_EMAIL', 'vuelaseguro.soporte@gmail.com');
define('MAIL_REMITENTE_NOMBRE', 'VuelaSeguro');

/**
 * Envía un mail HTML usando SendGrid.
 */
function enviarMail($destinatario, $asunto, $contenidoHtml) {

    $datos = [
        'personalizations' => [[
            'to' => [['email' => $destinatario]]
        ]],
        'from' => [
            'email' => MAIL_REMITENTE_EMAIL,
            'name'  => MAIL_REMITENTE_NOMBRE
        ],
        'subject' => $asunto,
        'content' => [[
            'type'  => 'text/html',
            'value' => $contenidoHtml
        ]]
    ];

    $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . SENDGRID_API_KEY,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $respuesta = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 202) {
        error_log('Error SendGrid [' . $httpCode . ']: ' . $respuesta);
        return false;
    }

    return true;
}

/**
 * Construye la URL base del sitio para armar links de verificación / reset.
 */
function urlBase() {
    $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $protocolo . '://' . $_SERVER['HTTP_HOST'];
}

/**
 * Plantilla HTML para todos los mails del sistema.
 */
function plantillaMail($titulo, $textoHtml, $textoBoton, $linkBoton) {
    return '
    <div style="font-family: Arial, sans-serif; background:#f5f7fb; padding:30px;">
      <div style="max-width:480px; margin:0 auto; background:#ffffff; border-radius:14px; overflow:hidden; border:1px solid #d9dee7;">
        <div style="background:#18283c; padding:24px; text-align:center;">
          <span style="color:#ffffff; font-size:20px; font-weight:700;">VuelaSeguro</span>
        </div>
        <div style="padding:30px;">
          <h2 style="color:#18283c; margin-top:0;">' . $titulo . '</h2>
          <p style="color:#666666; font-size:14px; line-height:1.6;">' . $textoHtml . '</p>
          <div style="text-align:center; margin:28px 0;">
            <a href="' . $linkBoton . '" style="background:#0d6efd; color:#ffffff; text-decoration:none; padding:12px 28px; border-radius:9px; font-weight:600; font-size:14px; display:inline-block;">' . $textoBoton . '</a>
          </div>
          <p style="color:#999999; font-size:12px;">Si no fuiste vos quien solicitó esto, podés ignorar este mail.</p>
        </div>
      </div>
    </div>';
}
