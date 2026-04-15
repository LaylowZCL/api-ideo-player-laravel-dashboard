@extends('layouts.app')

@section('title', 'Pré-visualização - Banco de Moc')

@section('content')
    <div id="previews-section" class="content-section">
        <previewpage></previewpage>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/app.js'])
@endsection
