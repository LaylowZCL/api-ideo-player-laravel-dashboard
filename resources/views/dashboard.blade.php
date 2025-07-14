@extends('layouts.app')

@section('title', 'Dashboard - ZK Interactive')

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
