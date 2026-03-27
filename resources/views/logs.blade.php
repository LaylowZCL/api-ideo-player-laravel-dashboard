@extends('layouts.app')

@section('title', 'Registos - ZK Interactive')

@section('content')
    <div id="logs-section" class="content-section">
        <logspage></logspage>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/app.js'])
@endsection
