<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bem-vindo ao Banco de Moçambique</title>
</head>
<body style="margin:0;padding:0;background:#eef3fb;font-family:'Segoe UI',Tahoma,Arial,sans-serif;color:#17324d;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eef3fb;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #d3dfef;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#023d7c,#0b5ab3);padding:28px 32px;text-align:center;">
                            <img src="{{ $logoUrl }}" alt="Banco de Moçambique" style="width:96px;height:96px;object-fit:contain;background:#fff;border-radius:16px;padding:10px;border:2px solid #c3a56b;">
                            <div style="font-size:28px;font-weight:700;color:#ffffff;margin-top:16px;">Banco de Moçambique</div>
                            <div style="font-size:15px;color:#d6e4fb;margin-top:8px;">Acesso à plataforma de gestão e distribuição de vídeos</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px;font-size:16px;">Caro(a) <strong>{{ $user->name }}</strong>,</p>
                            <p style="margin:0 0 16px;font-size:15px;line-height:1.65;">
                                Seja bem-vindo(a). Foi criada uma conta local no sistema do <strong>Banco de Moçambique</strong>
                                através do fluxo <strong>{{ $sourceLabel }}</strong>.
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:24px 0;background:#f6f9ff;border:1px solid #d9e6f7;border-radius:14px;">
                                <tr>
                                    <td style="padding:20px;">
                                        <div style="font-size:13px;color:#6b7f99;text-transform:uppercase;letter-spacing:.08em;">Dados de acesso</div>
                                        <div style="margin-top:14px;font-size:15px;line-height:1.8;">
                                            <div><strong>Nome:</strong> {{ $user->name }}</div>
                                            <div><strong>Email:</strong> {{ $user->email }}</div>
                                            <div><strong>Nome de utilizador:</strong> {{ $user->username ?? '-' }}</div>
                                            <div><strong>Palavra-passe temporária:</strong> {{ $plainPassword }}</div>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:24px 0;background:#fff7ec;border:1px solid #e8d1a5;border-radius:14px;">
                                <tr>
                                    <td style="padding:18px 20px;font-size:15px;line-height:1.65;color:#6c4d12;">
                                        <strong>Importante:</strong> no primeiro acesso será obrigatória a alteração da palavra-passe.
                                        Depois de definir a nova palavra-passe, o sistema poderá solicitar a activação ou validação do 2FA, conforme a política em vigor.
                                    </td>
                                </tr>
                            </table>

                            <div style="margin:28px 0;text-align:center;">
                                <a href="{{ $loginUrl }}" style="display:inline-block;background:#c3a56b;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:10px;font-weight:600;">
                                    Entrar na plataforma
                                </a>
                            </div>

                            <p style="margin:0 0 8px;font-size:15px;line-height:1.65;">
                                Se o botão acima não abrir, use este endereço no navegador:
                            </p>
                            <p style="margin:0 0 20px;font-size:14px;word-break:break-all;">
                                <a href="{{ $loginUrl }}" style="color:#0b5ab3;text-decoration:none;">{{ $loginUrl }}</a>
                            </p>

                            <p style="margin:0;font-size:15px;line-height:1.65;">
                                Cumprimentos,<br>
                                <strong>Banco de Moçambique</strong>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
