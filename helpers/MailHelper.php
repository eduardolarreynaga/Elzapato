<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailHelper {
    
    private static function getConfig() {
        return require __DIR__ . '/../src/config/mail_config.php';
    }
    
    /**
     * Enviar correo con historial como PDF adjunto
     */
    public static function enviarHistorialPDF($para, $nombre_destinatario, $pdf_content, $filtros = [], $total_registros = 0) {
        $config = self::getConfig();
        
        $mail = new PHPMailer(true);
        
        try {
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['username'];
            $mail->Password   = $config['password'];
            $mail->SMTPSecure = $config['encryption'];
            $mail->Port       = $config['port'];
            $mail->CharSet    = 'UTF-8';
            $mail->Timeout    = 30;
            
            $mail->setFrom($config['from_email'], 'ElZapato - Sistema de Ventas');
            $mail->addAddress($para, $nombre_destinatario);
            $mail->addReplyTo($config['from_email'], 'Soporte ElZapato');
            
            // Adjuntar PDF
            $mail->addStringAttachment($pdf_content, 'historial_elzapato.pdf', 'base64', 'application/pdf');
            
            $fecha_actual = date('d/m/Y H:i:s');
            $fecha_desde = $filtros['fecha_desde'] ?? 'Todos';
            $fecha_hasta = $filtros['fecha_hasta'] ?? 'Todos';
            $usuario_filtro = $filtros['usuario'] ?? 'Todos';
            $tipo_filtro = $filtros['tipo'] ?? 'Todos';
            
            $mail->Subject = '=?UTF-8?B?' . base64_encode("Historial de Actividades - ElZapato") . '?=';
            
            // Cuerpo del correo sin iconos
            $mail->isHTML(true);
            $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Historial ElZapato</title>
            </head>
            <body style='font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f5f5f5;'>
                <div style='max-width: 600px; margin: 20px auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                    <div style='text-align: center; background: #AB886D; color: white; padding: 20px;'>
                        <h1 style='margin: 0; font-size: 22px;'>Historial de Actividades</h1>
                        <p style='margin: 8px 0 0; opacity: 0.9;'>ElZapato - Sistema de Ventas</p>
                    </div>
                    
                    <div style='padding: 20px;'>
                        <div style='background: #f8f9fa; padding: 12px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #AB886D;'>
                            <p style='margin: 5px 0;'><strong>Fecha de generacion:</strong> $fecha_actual</p>
                            <p style='margin: 5px 0;'><strong>Destinatario:</strong> $nombre_destinatario</p>
                            <p style='margin: 5px 0;'><strong>Total de registros:</strong> $total_registros</p>
                        </div>
                        
                        <div style='background: #f8f9fa; padding: 12px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #AB886D;'>
                            <p style='margin: 5px 0;'><strong>Periodo:</strong> $fecha_desde - $fecha_hasta</p>
                            <p style='margin: 5px 0;'><strong>Filtro por usuario:</strong> $usuario_filtro</p>
                            <p style='margin: 5px 0;'><strong>Filtro por tipo:</strong> $tipo_filtro</p>
                        </div>
                        
                        <div style='text-align: center; padding: 15px; background: #E4E0E1; border-radius: 8px; margin-bottom: 15px;'>
                            <p style='margin: 0; font-size: 14px;'>
                                Se adjunta el archivo <strong>historial_elzapato.pdf</strong><br>
                                con el detalle completo del historial.
                            </p>
                        </div>
                    </div>
                    
                    <div style='text-align: center; padding: 12px; background: #E4E0E1; font-size: 10px; color: #666;'>
                        <p style='margin: 0;'>Este es un reporte automatico generado por el Sistema de Ventas ElZapato.</p>
                        <p style='margin: 5px 0 0;'>(c) " . date('Y') . " ElZapato - Todos los derechos reservados.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $mail->AltBody = "Historial de Actividades - ElZapato\n\n";
            $mail->AltBody .= "Fecha de generacion: $fecha_actual\n";
            $mail->AltBody .= "Destinatario: $nombre_destinatario\n";
            $mail->AltBody .= "Total de registros: $total_registros\n";
            $mail->AltBody .= "Periodo: $fecha_desde - $fecha_hasta\n";
            $mail->AltBody .= "Filtro por usuario: $usuario_filtro\n";
            $mail->AltBody .= "Filtro por tipo: $tipo_filtro\n\n";
            $mail->AltBody .= "Se adjunta el archivo PDF con el detalle completo del historial.\n";
            
            $mail->send();
            return ['success' => true, 'message' => 'Correo enviado exitosamente con PDF adjunto'];
        } catch (Exception $e) {
            error_log("Error al enviar correo: {$mail->ErrorInfo}");
            return ['success' => false, 'message' => "Error al enviar correo: {$mail->ErrorInfo}"];
        }
    }
}
?>