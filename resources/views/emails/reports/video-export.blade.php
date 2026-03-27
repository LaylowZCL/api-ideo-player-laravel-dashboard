@component('mail::message')
# Relatório detalhado de vídeos

Foi gerado um ficheiro Excel com o relatório detalhado de reprodução e eventos do sistema.

**Destinatário:** {{ $recipientLabel }}

@component('mail::table')
| Campo | Valor |
| :---- | :---- |
@foreach($summary as $item)
| {{ $item['Campo'] ?? '-' }} | {{ $item['Valor'] ?? '-' }} |
@endforeach
@endcomponent

O ficheiro em anexo inclui:

- resumo executivo
- eventos detalhados linha a linha
- distribuição por plataforma
- distribuição por evento
- linha temporal
- top vídeos

Cumprimentos,  
{{ config('app.name') }}
@endcomponent
