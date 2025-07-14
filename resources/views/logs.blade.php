@extends('layouts.app')

@section('title', 'Logs - ZK Interactive')

@section('content')
    <div id="logs-section" class="content-section">
        <logspage></logspage>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/components/LogsPage.vue'])
@endsection
