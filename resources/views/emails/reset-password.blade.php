<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña – Liga de Fútbol</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;500;600;700&display=swap');
    </style>
</head>
<body style="margin:0; padding:0; background-color:#0d1a0f; font-family:'Barlow', Arial, sans-serif;">

    <!-- Wrapper -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#0d1a0f; padding: 40px 16px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%;">

                    <!-- ===== HEADER ===== -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0a3d1f 0%, #0f5c2e 50%, #0a3d1f 100%);
                                   border-radius: 16px 16px 0 0;
                                   padding: 40px 40px 32px;
                                   text-align: center;
                                   position: relative;
                                   overflow: hidden;">

                            <!-- Campo decorativo SVG -->
                            <div style="margin-bottom: 16px;">
                                <svg width="72" height="72" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <!-- Fondo del ícono -->
                                    <rect width="72" height="72" rx="12" fill="#1aad5e" opacity="0.2"/>
                                    <!-- Ícono campo de fútbol -->
                                    <rect x="10" y="18" width="52" height="36" rx="3" stroke="#1aad5e" stroke-width="2.5" fill="none"/>
                                    <circle cx="36" cy="36" r="9" stroke="#1aad5e" stroke-width="2.5" fill="none"/>
                                    <line x1="36" y1="18" x2="36" y2="54" stroke="#1aad5e" stroke-width="2.5"/>
                                    <rect x="10" y="27" width="8" height="18" rx="1" stroke="#1aad5e" stroke-width="2" fill="none"/>
                                    <rect x="54" y="27" width="8" height="18" rx="1" stroke="#1aad5e" stroke-width="2" fill="none"/>
                                    <circle cx="36" cy="36" r="2" fill="#1aad5e"/>
                                </svg>
                            </div>

                            <!-- Título -->
                            <div style="font-family:'Bebas Neue', Impact, sans-serif; font-size: 13px; letter-spacing: 4px;
                                        color: #1aad5e; margin-bottom: 4px;">
                                ⚽ TEMPORADA 2026
                            </div>
                            <div style="font-family:'Bebas Neue', Impact, sans-serif; font-size: 38px; letter-spacing: 3px;
                                        color: #ffffff; line-height: 1; margin-bottom: 6px;">
                                LIGA DE <span style="color:#1aad5e;">FÚTBOL</span>
                            </div>
                            <div style="width: 60px; height: 3px; background: #1aad5e; margin: 0 auto;
                                        border-radius: 2px;"></div>
                        </td>
                    </tr>

                    <!-- ===== FRANJA TÁCTICA ===== -->
                    <tr>
                        <td style="background: #1aad5e; padding: 10px 40px; text-align: center;">
                            <span style="font-family:'Bebas Neue', Impact, sans-serif; font-size: 13px;
                                         letter-spacing: 5px; color: #ffffff;">
                                SISTEMA DE ACCESO DE JUGADORES
                            </span>
                        </td>
                    </tr>

                    <!-- ===== CUERPO ===== -->
                    <tr>
                        <td style="background: #ffffff; padding: 44px 48px 36px;">

                            <!-- Ícono recuperación -->
                            <div style="text-align: center; margin-bottom: 28px;">
                                <div style="display: inline-block; background: #f0faf4; border: 2px solid #d1f0e0;
                                            border-radius: 50%; width: 60px; height: 60px; line-height: 60px;
                                            font-size: 28px; text-align: center;">
                                    🔑
                                </div>
                            </div>

                            <!-- Título -->
                            <h1 style="font-family:'Bebas Neue', Impact, sans-serif; font-size: 28px;
                                       color: #0d1a0f; letter-spacing: 2px; text-align: center;
                                       margin: 0 0 8px;">
                                ¿PROBLEMAS CON TU <span style="color:#1aad5e;">CLAVE</span>?
                            </h1>
                            <p style="text-align: center; font-size: 14px; color: #6b7a6e;
                                      letter-spacing: 1px; text-transform: uppercase;
                                      font-weight: 600; margin: 0 0 32px;">
                                No te quedes fuera del partido
                            </p>

                            <!-- Saludo -->
                            <p style="font-size: 16px; color: #1c2c1f; margin: 0 0 14px; font-weight: 500;">
                                Hola, <strong style="color: #0a3d1f;">{{ $user->name }}</strong> 👋
                            </p>
                            <p style="font-size: 15px; color: #4a5c4d; line-height: 1.7; margin: 0 0 32px;">
                                Recibimos una solicitud para restablecer tu contraseña de acceso a la plataforma.
                                Haz clic en el botón para volver al campo y recuperar el control de tu cuenta.
                            </p>

                            <!-- Botón CTA -->
                            <div style="text-align: center; margin: 0 0 32px;">
                                <a href="{{ $url }}"
                                   style="display: inline-block;
                                          background: linear-gradient(135deg, #1aad5e 0%, #0f8a48 100%);
                                          color: #ffffff;
                                          font-family: 'Bebas Neue', Impact, sans-serif;
                                          font-size: 18px;
                                          letter-spacing: 3px;
                                          padding: 16px 44px;
                                          text-decoration: none;
                                          border-radius: 8px;
                                          box-shadow: 0 4px 18px rgba(26,173,94,0.35);">
                                    ⚽ &nbsp;RESTABLECER CONTRASEÑA
                                </a>
                            </div>

                            <!-- Alerta tiempo -->
                            <div style="background: #fffbea; border-left: 4px solid #f59e0b;
                                        border-radius: 6px; padding: 14px 18px; margin-bottom: 28px;">
                                <p style="margin: 0; font-size: 13px; color: #7c5e0a; font-weight: 600;">
                                    ⏱ &nbsp;Este enlace expirará en <strong>60 minutos</strong>. Actúa antes de que el árbitro pite el final.
                                </p>
                            </div>

                            <!-- URL alternativa -->
                            <p style="font-size: 12px; color: #8a9e8d; margin: 0 0 6px;">
                                Si el botón no funciona, copia y pega este enlace en tu navegador:
                            </p>
                            <p style="font-size: 11px; color: #1aad5e; word-break: break-all; margin: 0 0 28px;">
                                {{ $url }}
                            </p>

                            <!-- Divider campo -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 24px;">
                                <tr>
                                    <td style="border-top: 1px dashed #d1e8d8;"></td>
                                    <td style="padding: 0 12px; white-space: nowrap;">
                                        <span style="font-size: 18px;">⚽</span>
                                    </td>
                                    <td style="border-top: 1px dashed #d1e8d8;"></td>
                                </tr>
                            </table>

                            <!-- Aviso seguridad -->
                            <p style="font-size: 12px; color: #a0b0a3; line-height: 1.6; margin: 0;">
                                Si <strong>no solicitaste</strong> este cambio, puedes ignorar este mensaje con total seguridad.
                                Tu contraseña permanecerá sin cambios.
                            </p>

                        </td>
                    </tr>

                    <!-- ===== FOOTER ===== -->
                    <tr>
                        <td style="background: #0a2212; border-radius: 0 0 16px 16px; padding: 24px 40px;
                                   text-align: center;">

                            <!-- Marcador decorativo -->
                            <div style="display: inline-block; background: #0f3d1e; border: 1px solid #1aad5e40;
                                        border-radius: 8px; padding: 8px 24px; margin-bottom: 16px;">
                                <span style="font-family:'Bebas Neue', Impact, sans-serif; font-size: 20px;
                                             color: #ffffff; letter-spacing: 6px;">
                                    LIGA &nbsp;<span style="color:#1aad5e;">—</span>&nbsp; 2026
                                </span>
                            </div>

                            <p style="font-size: 11px; color: #4a6b52; margin: 0;">
                                © 2026 Liga de Fútbol · Todos los derechos reservados<br>
                                Este correo fue generado automáticamente, por favor no respondas.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>