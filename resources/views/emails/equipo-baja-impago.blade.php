<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baja por impago – {{ $inscripcion->torneo->nombre }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;500;600;700&display=swap');
    </style>
</head>
<body style="margin:0; padding:0; background-color:#0d1a0f; font-family:'Barlow', Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#0d1a0f; padding: 40px 16px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%;">

                    <tr>
                        <td style="background: linear-gradient(135deg, #3d0a0a 0%, #5c0f0f 50%, #3d0a0a 100%);
                                   border-radius: 16px 16px 0 0;
                                   padding: 40px 40px 32px;
                                   text-align: center;
                                   overflow: hidden;">
                            <div style="margin-bottom: 16px;">
                                <svg width="72" height="72" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="72" height="72" rx="12" fill="#ef4444" opacity="0.2"/>
                                    <line x1="22" y1="22" x2="50" y2="50" stroke="#ef4444" stroke-width="3" stroke-linecap="round"/>
                                    <line x1="50" y1="22" x2="22" y2="50" stroke="#ef4444" stroke-width="3" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div style="font-family:'Bebas Neue', Impact, sans-serif; font-size: 13px; letter-spacing: 4px;
                                        color: #ef4444; margin-bottom: 4px;">
                                {{ $inscripcion->torneo->fecha_inicio ? $inscripcion->torneo->fecha_inicio->format('Y') : date('Y') }}
                            </div>
                            <div style="font-family:'Bebas Neue', Impact, sans-serif; font-size: 38px; letter-spacing: 3px;
                                        color: #ffffff; line-height: 1; margin-bottom: 6px;">
                                {{ $inscripcion->torneo->nombre }}
                            </div>
                            <div style="width: 60px; height: 3px; background: #ef4444; margin: 0 auto; border-radius: 2px;"></div>
                        </td>
                    </tr>

                    <tr>
                        <td style="background: #ef4444; padding: 10px 40px; text-align: center;">
                            <span style="font-family:'Bebas Neue', Impact, sans-serif; font-size: 13px;
                                         letter-spacing: 5px; color: #ffffff;">
                                BAJA POR FALTA DE PAGO
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td style="background: #ffffff; padding: 44px 48px 36px;">

                            <div style="text-align: center; margin-bottom: 28px;">
                                <div style="display: inline-block; background: #fef2f2; border: 2px solid #fecaca;
                                            border-radius: 50%; width: 60px; height: 60px; line-height: 60px;
                                            font-size: 28px; text-align: center;">
                                    ⚠️
                                </div>
                            </div>

                            <h1 style="font-family:'Bebas Neue', Impact, sans-serif; font-size: 28px;
                                       color: #0d1a0f; letter-spacing: 2px; text-align: center;
                                       margin: 0 0 8px;">
                                EQUIPO <span style="color:#ef4444;">{{ mb_strtoupper($inscripcion->equipo->name) }}</span>
                            </h1>
                            <p style="text-align: center; font-size: 14px; color: #6b7a6e;
                                      letter-spacing: 1px; text-transform: uppercase;
                                      font-weight: 600; margin: 0 0 32px;">
                                Ha sido dado de baja del torneo
                            </p>

                            <p style="font-size: 16px; color: #1c2c1f; margin: 0 0 14px; font-weight: 500;">
                                Lamentamos informarte que 🚫
                            </p>
                            <p style="font-size: 15px; color: #4a5c4d; line-height: 1.7; margin: 0 0 24px;">
                                El equipo <strong style="color: #ef4444;">{{ $inscripcion->equipo->name }}</strong>
                                ha sido dado de baja del torneo
                                <strong>{{ $inscripcion->torneo->nombre }}</strong>
                                por no haber completado el pago de inscripción dentro del plazo establecido
                                ({{ $jornadasTranscurridas }} jornadas transcurridas, máximo {{ $maxJornadas }}).
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px;
                                          padding: 20px 24px; margin-bottom: 32px;">
                                <tr>
                                    <td>
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="padding: 4px 0;">
                                                    <span style="font-size: 13px; color: #6b7a6e; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;">Equipo</span>
                                                    <span style="display: block; font-size: 18px; color: #0d1a0f; font-weight: 700;">{{ $inscripcion->equipo->name }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 4px 0;">
                                                    <span style="font-size: 13px; color: #6b7a6e; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;">Torneo</span>
                                                    <span style="display: block; font-size: 18px; color: #0d1a0f; font-weight: 700;">{{ $inscripcion->torneo->nombre }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 4px 0;">
                                                    <span style="font-size: 13px; color: #6b7a6e; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;">Motivo</span>
                                                    <span style="display: block; font-size: 18px; color: #ef4444; font-weight: 700;">Falta de pago</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 4px 0;">
                                                    <span style="font-size: 13px; color: #6b7a6e; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;">Jornadas sin pago</span>
                                                    <span style="display: block; font-size: 18px; color: #0d1a0f; font-weight: 700;">{{ $jornadasTranscurridas }} / {{ $maxJornadas }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 15px; color: #4a5c4d; line-height: 1.7; margin: 0 0 24px;">
                                Si crees que esto es un error o deseas regularizar la situación,
                                contacta al administrador del torneo para más información.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 24px;">
                                <tr>
                                    <td style="border-top: 1px dashed #fecaca;"></td>
                                    <td style="padding: 0 12px; white-space: nowrap;">
                                        <span style="font-size: 18px;">⚽</span>
                                    </td>
                                    <td style="border-top: 1px dashed #fecaca;"></td>
                                </tr>
                            </table>

                            <p style="font-size: 12px; color: #a0b0a3; line-height: 1.6; margin: 0;">
                                Este correo fue enviado automáticamente al contacto registrado del equipo
                                <strong>{{ $inscripcion->equipo->name }}</strong>.
                                Si tienes dudas, contacta al administrador del torneo.
                            </p>

                        </td>
                    </tr>

                    <tr>
                        <td style="background: #0a2212; border-radius: 0 0 16px 16px; padding: 24px 40px;
                                   text-align: center;">
                            <div style="display: inline-block; background: #0f3d1e; border: 1px solid #10b77f40;
                                        border-radius: 8px; padding: 8px 24px; margin-bottom: 16px;">
                                <span style="font-family:'Bebas Neue', Impact, sans-serif; font-size: 20px;
                                             color: #ffffff; letter-spacing: 6px;">
                                    {{ $inscripcion->torneo->nombre }} &nbsp;<span style="color:#10b77f;">—</span>&nbsp;
                                    {{ $inscripcion->torneo->fecha_inicio ? $inscripcion->torneo->fecha_inicio->format('Y') : date('Y') }}
                                </span>
                            </div>
                            <p style="font-size: 11px; color: #4a6b52; margin: 0;">
                                &copy; {{ date('Y') }} {{ $inscripcion->torneo->nombre }} &middot; Todos los derechos reservados<br>
                                Este correo fue generado autom&aacute;ticamente, por favor no respondas.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
