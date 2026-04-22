@extends('layouts.auth-app')

@section('content')
    <main class="form-signin">
        <form method="POST" action="{{ route('two-factor.verify') }}">
            @csrf
            <img class="mb-4" src="{{ asset('assets/images/icone-bm.png') }}" alt="" width="100" height="100">
            <h1 class="h4 mb-3 fw-normal">Verificação 2FA</h1>

            <div class="form-floating">
                <input type="text" class="form-control @error('code') is-invalid @enderror" id="codeInput"
                    name="code" autofocus placeholder="123456">
                <label for="codeInput">Código do autenticador</label>

                @error('code')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="mt-3 text-muted">Ou use um código de recuperação</div>

            <div class="form-floating mt-2">
                <input type="text" class="form-control @error('recovery_code') is-invalid @enderror" id="recoveryInput"
                    name="recovery_code" placeholder="recovery">
                <label for="recoveryInput">Código de recuperação</label>

                @error('recovery_code')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <button class="w-100 btn btn-lg btn-primary mt-3" type="submit">Continuar</button>
            @include('auth.partials.docs-link')
        </form>
    </main>
@endsection
