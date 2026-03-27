@extends('layouts.auth-app')

@section('content')
    <main class="form-signin">
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <img class="mb-4" src="{{ asset('assets/images/icone-bm.png') }}" alt="" width="100" height="100">
            <h1 class="h3 mb-3 fw-normal">Iniciar sessão</h1>

            <div class="form-floating">
                <input type="text" class="form-control @error('login') is-invalid @enderror" id="floatingInput"
                    name="login" value="{{ old('login') }}" required autocomplete="username" autofocus
                    placeholder="nome de utilizador ou email">
                <label for="floatingInput">Nome de utilizador ou email</label>

                @error('login')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-floating">
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="floatingPassword"
                    name="password" required autocomplete="current-password" placeholder="Palavra-passe">
                <label for="floatingPassword">Palavra-passe</label>

                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="checkbox mb-3">
                <label>
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}> Manter a sessão iniciada
                </label>
            </div>

            <button class="w-100 btn btn-lg btn-primary" type="submit">Entrar</button>
            @if (!config('ad.dashboard_uses_ad') && Route::has('password.request'))
                <a class="btn btn-link" href="{{ route('password.request') }}">
                    Esqueceu-se da palavra-passe?
                </a>
            @endif
            @if (config('ad.dashboard_uses_ad'))
                <p class="mt-3 text-muted" style="color: #ba9a69 !important;">
                    Autenticação via Active Directory
                </p>
            @else
                <p class="mt-3 text-muted" style="color: #ba9a69 !important;">
                    Autenticação local do painel com email ou nome de utilizador
                </p>
            @endif
            <p class="mt-5 mb-3 text-muted" style="color: #ba9a69 !important;">&copy; Banco de Moçambique
                {{ date('Y') }}</p>
        </form>
    </main>
@endsection
