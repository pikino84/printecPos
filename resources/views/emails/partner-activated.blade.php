<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta Activada</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #2457aa; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px;">🎉 ¡Tu cuenta ha sido activada!</h1>
    </div>
    
    <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;">
        <p style="font-size: 18px;">Hola <strong>{{ $user->name }}</strong>,</p>
        
        <p>¡Excelentes noticias! Tu cuenta de partner en Printec ha sido revisada y <strong style="color: #2457aa;">activada exitosamente</strong>.</p>
        
        <p>Ya puedes acceder al sistema y comenzar a disfrutar de todos los beneficios de ser parte de nuestra red de partners.</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="https://posprintec.com/login" 
               style="background: #2457aa; 
                      color: white; 
                      padding: 15px 40px; 
                      text-decoration: none; 
                      border-radius: 8px; 
                      font-weight: bold;
                      font-size: 16px;
                      display: inline-block;">
                🚀 Iniciar Sesión
            </a>
        </div>
        
        <div style="background: #fff; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #2457aa;">
            <h3 style="margin-top: 0; color: #2457aa;">¿Qué puedes hacer ahora?</h3>
            <ul style="margin: 0; padding-left: 20px;">
                <li>Explorar nuestro catálogo de productos</li>
                <li>Crear cotizaciones para tus clientes</li>
                <li>Gestionar tu cartera de clientes</li>
                <li>Configurar tu perfil y razones sociales</li>
            </ul>
        </div>
        
        <p>Si tienes alguna duda o necesitas ayuda, no dudes en escribirnos a <a href="mailto:ebutron@printec.mx" style="color: #2457aa;">ebutron@printec.mx</a></p>
        
        <p style="margin-top: 30px;">
            ¡Bienvenido al equipo!<br>
            <strong>El equipo de Printec</strong>
        </p>
    </div>
    
    <div style="text-align: center; padding: 20px; color: #888; font-size: 12px;">
        <p>Este es un correo automático, por favor no respondas directamente.</p>
        <p>© {{ date('Y') }} Printec. Todos los derechos reservados.</p>
    </div>
</body>
</html>