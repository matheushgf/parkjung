@extends('layouts.app')

@php
    $action = 'grupo';
@endphp

@section('content')
    <div class="row justify-content-end auto-height">
        <span class="btn align-self-end me-3 btn-park text-center" id="btnVoltar"><a href="{{ route('grupos.list') }}">Voltar</a></span>
    </div>
    <div class="row justify-content-center auto-height">
        <h3 class="text-center mb-3">Edição - Grupo</h3>
        <div class="col-sm-12 col-md-6">
            <form action="{{ route('grupos.update', ['grupo' => $grupo]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3 form-floating">
                    <input name="nome" class="form-control @error('nome') mb-1 is-invalid @enderror" type="text" placeholder="Nome" value="{{ $grupo->nome }}">
                    <label for="nome">Nome<span class="text-danger">*</span></label>
                    @error('nome')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-floating">
                    <input name="descricao" class="form-control @error('descricao') mb-1 is-invalid @enderror" type="text" placeholder="Descrição" value="{{ $grupo->descricao }}">
                    <label for="descricao">Descrição</label>
                    @error('descricao')
                        <div class="alert text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-park">Salvar</button>
            </form>
        </div>
    </div>
@endsection