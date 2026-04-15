@extends('layouts.app')

@section('title', 'Vídeos - Banco de Moc')

@section('content')
    <div id="videos-section" class="content-section">
        <settingspage></settingspage>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/app.js'])
@endsection
