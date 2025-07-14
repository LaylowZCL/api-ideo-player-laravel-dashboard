@extends('layouts.app')

@section('title', 'Agendamentos - ZK Interactive')

@section('content')
    <div id="schedule-section" class="content-section">
        <schedulepage></schedulepage>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/components/SchedulePage.vue'])
@endsection
