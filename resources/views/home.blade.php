@extends('layouts.app')

@inject('grupo', \App\Models\Grupo::class)

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    {{-- {{ $grupo::with('permissoes')->get() }} --}}
                    {{-- {{ $grupo::with('usuarios')->get() }} --}}

                    {{ __('You are logged in!') }}
                </div>
            </div>
        </div>
    </div>
@endsection
