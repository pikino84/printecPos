<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Partner</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px;">🎉 Nuevo Partner Registrado</h1>
    </div>
    
    <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;">
        <p style="font-size: 18px;">¡Hola!</p>
        
        <p>Se ha registrado un nuevo partner en el sistema y está pendiente de activación.</p>
        
        <div style="background: #fff; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #f5576c;">
            <h3 style="margin-top: 0; color: #f5576c;">Datos del nuevo partner:</h3>
            <p style="margin: 5px 0;"><strong>Empresa:</strong> {{ $partner->name }}</p>
            <p style="margin: 5px 0;"><strong>Contacto:</strong> {{ $user->name }}</p>
            <p style="margin: 5px 0;"><strong>Email:</strong> {{ $user->email }}</p>
            <p style="margin: 5px 0;"><strong>Fecha de registro:</strong> {{ $partner->created_at->format('d/m/Y H:i') }}</p>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="https://posprintec.com/partners/{{ $partner->id }}/edit" 
               style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); 
                      color: white; 
                      padding: 15px 30px; 
                      text-decoration: none; 
                      border-radius: 8px; 
                      font-weight: bold;
                      display: inline-block;">
                📝 Revisar y Activar Partner
            </a>
        </div>
        
        <p style="color: #888; font-size: 14px;">
            Para activar este partner, haz clic en el botón de arriba o ve directamente a:<br>
            <a href="https://posprintec.com/partners/{{ $partner->id }}/edit">https://posprintec.com/partners/{{ $partner->id }}/edit</a>
        </p>
    </div>
    
    <div style="text-align: center; padding: 20px; color: #888; font-size: 12px;">
        <p>Este es un correo automático del sistema PrintecPOS.</p>
        <p>© {{ date('Y') }} Printec. Todos los derechos reservados.</p>
    </div>
</body>
</html>