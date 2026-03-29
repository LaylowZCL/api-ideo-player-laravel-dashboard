@extends('layouts.auth-app')

@section('title', 'Primeiro acesso')

@section('content')
    <main class="form-signin" style="max-width: 420px;">
        <form method="POST" action="{{ route('force-password.update') }}">
            @csrf
            <img class="mb-4" src="{{ asset('assets/images/icone-bm.png') }}" alt="" width="100" height="100">
            <h1 class="h3 mb-3 fw-normal">Definir nova palavra-passe</h1>
            <p class="mb-4" style="color: #d7e7ff !important;">
                Por razões de segurança, deve alterar a sua palavra-passe antes de entrar na plataforma.
            </p>

            <div class="form-floating mb-3">
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                    name="password" required autocomplete="new-password" placeholder="Nova palavra-passe">
                <label for="password">Nova palavra-passe</label>
                @error('password')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password_confirmation"
                    name="password_confirmation" required autocomplete="new-password" placeholder="Confirmar palavra-passe">
                <label for="password_confirmation">Confirmar palavra-passe</label>
            </div>

            <button class="w-100 btn btn-lg btn-primary" type="submit">Guardar e continuar</button>
            <p class="mt-4 mb-0 text-muted" style="color: #ba9a69 !important;">&copy; Banco de Moçambique {{ date('Y') }}</p>
        </form>
    </main>
@endsection
