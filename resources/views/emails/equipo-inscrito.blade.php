<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripción confirmada – {{ $inscripcion->torneo->nombre }}</title>
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
                        <td style="background: linear-gradient(135deg, #0a3d1f 0%, #0f5c2e 50%, #0a3d1f 100%);
                                   border-radius: 16px 16px 0 0;
                                   padding: 40px 40px 32px;
                                   text-align: center;
                                   position: relative;
                                   overflow: hidden;">

                            <div style="margin-bottom: 16px;">
                                <svg width="72" height="72" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="72" height="72" rx="12" fill="#10b77f" opacity="0.2"/>
                                    <rect x="10" y="18" width="52" height="36" rx="3" stroke="#10b77f" stroke-width="2.5" fill="none"/>
                                    <circle cx="36" cy="36" r="9" stroke="#10b77f" stroke-width="2.5" fill="none"/>
                                    <line x1="36" y1="18" x2="36" y2="54" stroke="#10b77f" stroke-width="2.5"/>
                                    <rect x="10" y="27" width="8" height="18" rx="1" stroke="#10b77f" stroke-width="2" fill="none"/>
                                    <rect x="54" y="27" width="8" height="18" rx="1" stroke="#10b77f" stroke-width="2" fill="none"/>
                                    <circle cx="36" cy="36" r="2" fill="#10b77f"/>
                                </svg>
                            </div>

                            <div style="font-family:'Bebas Neue', Impact, sans-serif; font-size: 13px; letter-spacing: 4px;
                                        color: #10b77f; margin-bottom: 4px;">
                                @if ($inscripcion->torneo->fecha_inicio)
                                    {{ $inscripcion->torneo->fecha_inicio->format('Y') }}
                                @else
                                    TEMPORADA {{ date('Y') }}
                                @endif
                            </div>
                            <div style="font-family:'Bebas Neue', Impact, sans-serif; font-size: 38px; letter-spacing: 3px;
                                        color: #ffffff; line-height: 1; margin-bottom: 6px;">
                                {{ $inscripcion->torneo->nombre }}
                            </div>
                            <div style="width: 60px; height: 3px; background: #10b77f; margin: 0 auto;
                                        border-radius: 2px;"></div>
                        </td>
                    </tr>

                    <tr>
                        <td style="background: #10b77f; padding: 10px 40px; text-align: center;">
                            <span style="font-family:'Bebas Neue', Impact, sans-serif; font-size: 13px;
                                         letter-spacing: 5px; color: #ffffff;">
                                INSCRIPCIÓN CONFIRMADA
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td style="background: #ffffff; padding: 44px 48px 36px;">

                            <div style="text-align: center; margin-bottom: 28px;">
                                <div style="display: inline-block; background: #f0faf4; border: 2px solid #d1f0e0;
                                            border-radius: 50%; width: 60px; height: 60px; line-height: 60px;
                                            font-size: 28px; text-align: center;">
                                    ✅
                                </div>
                            </div>

                            <h1 style="font-family:'Bebas Neue', Impact, sans-serif; font-size: 28px;
                                       color: #0d1a0f; letter-spacing: 2px; text-align: center;
                                       margin: 0 0 8px;">
                                ¡BIENVENIDO <span style="color:#10b77f;">{{ mb_strtoupper($inscripcion->equipo->name) }}</span>!
                            </h1>
                            <p style="text-align: center; font-size: 14px; color: #6b7a6e;
                                      letter-spacing: 1px; text-transform: uppercase;
                                      font-weight: 600; margin: 0 0 32px;">
                                {{ $inscripcion->torneo->tipo === 'relampago' ? 'Torneo Relámpago' : 'Torneo de Liga' }}
                            </p>

                            <p style="font-size: 16px; color: #1c2c1f; margin: 0 0 14px; font-weight: 500;">
                                ¡Hola! 👋
                            </p>
                            <p style="font-size: 15px; color: #4a5c4d; line-height: 1.7; margin: 0 0 24px;">
                                Nos complace informarte que el equipo
                                <strong style="color: #10b77f;">{{ $inscripcion->equipo->name }}</strong>
                                ha sido inscrito exitosamente en el torneo
                                <strong>{{ $inscripcion->torneo->nombre }}</strong>.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="background: #f8fdfa; border: 1px solid #d1f0e0; border-radius: 12px;
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
                                            @if ($inscripcion->torneo->categoria)
                                            <tr>
                                                <td style="padding: 4px 0;">
                                                    <span style="font-size: 13px; color: #6b7a6e; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;">Categoría</span>
                                                    <span style="display: block; font-size: 18px; color: #0d1a0f; font-weight: 700;">{{ $inscripcion->torneo->categoria }} · {{ $inscripcion->torneo->rama }}</span>
                                                </td>
                                            </tr>
                                            @endif
                                            @if ($inscripcion->seed)
                                            <tr>
                                                <td style="padding: 4px 0;">
                                                    <span style="font-size: 13px; color: #6b7a6e; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;">Seed</span>
                                                    <span style="display: block; font-size: 18px; color: #0d1a0f; font-weight: 700;">#{{ $inscripcion->seed }}</span>
                                                </td>
                                            </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            @if ($inscripcion->torneo->fecha_inicio)
                            <div style="background: #fffbea; border-left: 4px solid #f59e0b;
                                        border-radius: 6px; padding: 14px 18px; margin-bottom: 28px;">
                                <p style="margin: 0; font-size: 13px; color: #7c5e0a; font-weight: 600;">
                                    📅 &nbsp;El torneo inicia el <strong>{{ $inscripcion->torneo->fecha_inicio->format('d/m/Y') }}</strong>.
                                    Prepárate para el pitido inicial.
                                </p>
                            </div>
                            @endif

                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 24px;">
                                <tr>
                                    <td style="border-top: 1px dashed #d1e8d8;"></td>
                                    <td style="padding: 0 12px; white-space: nowrap;">
                                        <span style="font-size: 18px;">⚽</span>
                                    </td>
                                    <td style="border-top: 1px dashed #d1e8d8;"></td>
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
                                    @if ($inscripcion->torneo->fecha_inicio)
                                        {{ $inscripcion->torneo->fecha_inicio->format('Y') }}
                                    @else
                                        {{ date('Y') }}
                                    @endif
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
