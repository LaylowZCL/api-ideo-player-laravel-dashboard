@extends('layouts.app')

@section('title', 'Agendamentos - Banco de Moc')

@section('content')
    <div id="schedule-section" class="content-section">
        <schedulepage></schedulepage>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/app.js'])
@endsection
