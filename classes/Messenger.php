<?php
// Admindash/classes/Messenger.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Messenger {

    // ─────────────────────────────────────────
    // REGISTRO DE LOG EN BD
    // ─────────────────────────────────────────
    public static function registrarLog(string $tipo, string $destinatario, string $asunto, string $template, string $estatus, ?string $error = null): void {
        try {
            $db   = Database::getConnection();
            $sql  = "INSERT INTO log_mensajeria (tipo, destinatario, asunto_o_mensaje, template, estatus, error_detalle)
                     VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$tipo, $destinatario, $asunto, $template, $estatus, $error]);
        } catch (\Exception $e) {
            error_log("Messenger::registrarLog error: " . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────
    // ENVÍO DE EMAIL
    // ─────────────────────────────────────────
    public static function sendEmail(string $to, string $subject, string $template, array $vars = []): bool {
        $mail = new PHPMailer(true);

        try {
            // Credenciales desde .env
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST']     ?? 'smtp-mail.outlook.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USER']     ?? '';
            $mail->Password   = $_ENV['MAIL_PASS']     ?? '';
            $mail->SMTPSecure = $_ENV['MAIL_SECURE']   ?? PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int)($_ENV['MAIL_PORT'] ?? 587);
            $mail->CharSet    = 'UTF-8';

            // Solo en desarrollo desactivar verificación SSL
            if (($_ENV['APP_ENV'] ?? 'development') !== 'production') {
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true,
                    ]
                ];
            }

            $fromEmail = $_ENV['MAIL_FROM']      ?? 'no-reply@admindash.com';
            $fromName  = $_ENV['MAIL_FROM_NAME'] ?? APP_NAME;

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = self::renderTemplate($template, $vars);
            $mail->AltBody = strip_tags($mail->Body);

            $mail->send();
            self::registrarLog('EMAIL', $to, $subject, $template, 'EXITO');
            return true;

        } catch (\Exception $e) {
            self::registrarLog('EMAIL', $to, $subject, $template, 'ERROR', $mail->ErrorInfo);
            error_log("Messenger::sendEmail error: " . $mail->ErrorInfo);
            return false;
        }
    }

    // ─────────────────────────────────────────
    // ENVÍO DE SMS (Twilio)
    // ─────────────────────────────────────────
    public static function sendSMS(string $toNumber, string $message): bool {
        $sid   = $_ENV['TWILIO_SID']   ?? '';
        $token = $_ENV['TWILIO_TOKEN'] ?? '';
        $from  = $_ENV['TWILIO_FROM']  ?? '';

        if (empty($sid) || empty($token) || empty($from)) {
            error_log("Messenger::sendSMS credenciales Twilio no configuradas en .env");
            return false;
        }

        $url  = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
        $data = ['From' => $from, 'To' => $toNumber, 'Body' => $message];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST,           true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD,        "$sid:$token");
        curl_setopt($ch, CURLOPT_POSTFIELDS,     http_build_query($data));
        curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status === 200 || $status === 201) {
            self::registrarLog('SMS', $toNumber, substr($message, 0, 50), 'N/A', 'EXITO');
            return true;
        } else {
            self::registrarLog('SMS', $toNumber, substr($message, 0, 50), 'N/A', 'ERROR', "HTTP Code: $status");
            return false;
        }
    }

    // ─────────────────────────────────────────
    // MOTOR DE TEMPLATES
    // ─────────────────────────────────────────
    private static function renderTemplate(string $name, array $data): string {
        $file = __DIR__ . "/../assets/templates/{$name}.html";

        // Crear directorio si no existe
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!file_exists($file)) {
            error_log("Messenger: template no encontrado → $file");
            return "Template '{$name}' no encontrado.";
        }

        $content = file_get_contents($file);
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        return $content;
    }
}
