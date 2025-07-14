@extends('layouts.app')

@section('title', 'Vídeos - ZK Interactive')

@section('content')
    <div id="videos-section" class="content-section">
        <videospage></videospage>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/components/VideosPage.vue'])
@endsection
