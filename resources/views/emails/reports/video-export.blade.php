<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Relatório detalhado de vídeos</title>
</head>
<body style="margin:0;padding:0;background:#eef3fb;font-family:'Segoe UI',Tahoma,Arial,sans-serif;color:#17324d;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eef3fb;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #d3dfef;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#023d7c,#0b5ab3);padding:28px 32px;text-align:center;">
                            <img src="{{ rtrim(config('app.url'), '/') . '/assets/images/logo-bm.png' }}" alt="Banco de Moçambique" style="width:96px;height:96px;object-fit:contain;background:#fff;border-radius:16px;padding:10px;border:2px solid #c3a56b;">
                            <div style="font-size:28px;font-weight:700;color:#ffffff;margin-top:16px;">Banco de Moçambique</div>
                            <div style="font-size:15px;color:#d6e4fb;margin-top:8px;">Relatório detalhado de vídeos</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px;font-size:15px;line-height:1.65;">
                                Foi gerado um ficheiro Excel com o relatório detalhado de reprodução e eventos do sistema.
                            </p>
                            <p style="margin:0 0 20px;font-size:15px;"><strong>Destinatário:</strong> {{ $recipientLabel }}</p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;border:1px solid #d9e6f7;">
                                @foreach($summary as $item)
                                    <tr>
                                        <td style="padding:12px;border:1px solid #d9e6f7;background:#f6f9ff;font-weight:600;width:42%;">{{ $item['Campo'] ?? '-' }}</td>
                                        <td style="padding:12px;border:1px solid #d9e6f7;">{{ $item['Valor'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </table>

                            <p style="margin:24px 0 8px;font-size:15px;"><strong>O ficheiro em anexo inclui:</strong></p>
                            <ul style="margin:0 0 20px;padding-left:20px;line-height:1.7;">
                                <li>resumo executivo</li>
                                <li>eventos detalhados linha a linha</li>
                                <li>distribuição por plataforma</li>
                                <li>distribuição por evento</li>
                                <li>linha temporal</li>
                                <li>top vídeos</li>
                            </ul>

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
