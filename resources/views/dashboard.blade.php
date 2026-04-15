@extends('layouts.app')

@section('title', 'Painel de controlo - Banco de Moc')

@section('content')
    <div>
        <div id="dashboard-section" class="content-section">
            <dashboardpage></dashboardpage>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/app.js'])
@endsection
