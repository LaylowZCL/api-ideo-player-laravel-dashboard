@extends('layouts.app')

@section('title', 'Registos - Banco de Moc')

@section('content')
    <div id="logs-section" class="content-section">
        <logspage></logspage>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/app.js'])
@endsection
