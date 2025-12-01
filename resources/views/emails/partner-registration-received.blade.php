<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Recibido</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #2457aa; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px;">¡Bienvenido a Printec!</h1>
    </div>
    
    <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;">
        <p style="font-size: 18px;">Hola <strong>{{ $user->name }}</strong>,</p>
        
        <p>¡Gracias por registrarte como partner de Printec! Hemos recibido tu solicitud y estamos emocionados de que quieras formar parte de nuestra red.</p>
        
        <div style="background: #fff; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #2457aa;">
            <h3 style="margin-top: 0; color: #2457aa;">Datos de tu registro:</h3>
            <p style="margin: 5px 0;"><strong>Empresa:</strong> {{ $partner->name }}</p>
            <p style="margin: 5px 0;"><strong>Email:</strong> {{ $user->email }}</p>
            <p style="margin: 5px 0;"><strong>Fecha:</strong> {{ $partner->created_at->format('d/m/Y H:i') }}</p>
        </div>
        
        <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 0; color: #856404;">
                <strong>⏳ Tu cuenta está pendiente de activación.</strong><br>
                Nuestro equipo revisará tu solicitud y te notificaremos por email cuando tu cuenta esté activa.
            </p>
        </div>
        
        <p>Si tienes alguna pregunta mientras tanto, no dudes en contactarnos.</p>
        
        <p style="margin-top: 30px;">
            ¡Saludos!<br>
            <strong>El equipo de Printec</strong>
        </p>
    </div>
    
    <div style="text-align: center; padding: 20px; color: #888; font-size: 12px;">
        <p>Este es un correo automático, por favor no respondas directamente.</p>
        <p>© {{ date('Y') }} Printec. Todos los derechos reservados.</p>
    </div>
</body>
</html>