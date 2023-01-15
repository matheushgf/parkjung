@extends('layouts.app')

@php
    $action = 'grupo';
    $permissoesLer = $grupo->permissoes()
        ->where('ler', true)
        ->pluck('id')
        ->toArray();
    $permissoesEscrever = $grupo->permissoes()
        ->where('escrever', true)
        ->pluck('id')
        ->toArray();
@endphp

@section('content')
    {{ $token->plainTextToken }}
    <div class="row justify-content-end auto-height">
        <span class="btn align-self-end me-3 btn-park text-center" id="btnVoltar"><a href="{{ route('grupos.list') }}">Voltar</a></span>
    </div>
    <div class="row justify-content-center auto-height">
        <h3 class="text-center mb-3">Permissões</h3>
        @if ($message = Session::get('success'))
            <div class="row justify-content-center auto-height">
                <div class="col-sm-12 col-md-6 mb-3 alert alert-success alert-dismissible fade show success-park text-center" role="alert">
                    <p>{{ $message }}</p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif
        <div class="col-sm-12 col-md-6">
            <form action="{{ route('grupos.permissoes.store', ['grupo' => $grupo]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3 form-floating">
                    <input name="nome" class="form-control readonly" readonly="readonly" type="text" placeholder="Nome" value="{{ $grupo->nome }}">
                    <label for="nome">Grupo</label>
                </div>
                <div>
                    <div class="form-group mb-3">
                        <label for="select-user">Multiple Tags</label>
                        <select class="select-user form-control" name="users[]" multiple="multiple"
                          id="select-user">
                          <option value="tag1">tag1</option>
                          <option value="tag2">tag2</option>
                          <option value="tag3">tag3</option>               
                        </select>
                      </div>
                </div>
                @foreach($permissoes as $permissao)
                    @php
                        $id = $permissao->funcionalidade;
                    @endphp
                    <hr class="hr divider"/>
                    <div id="grupo-permissao-{{ $id }}" class="grupos-permissao">
                        <h6 class="mb-3">{{ $permissao->nome }}</h6>
                        <div class="d-flex flex-row">
                            <div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input permissoes-leitura" type="checkbox" role="switch" id="{{ $id }}-ler" name="{{ $id }}.ler" {{ in_array($permissao->id, $permissoesLer) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="produtos">Leitura</label>
                                </div>
                            </div>
                            <div class="ms-5">                     
                                <div class="form-check form-switch">
                                    <input class="form-check-input permissoes-gravacao" type="checkbox" role="switch" id="{{ $id }}-escrever" name="{{ $id }}.escrever" {{ in_array($permissao->id, $permissoesEscrever) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="produtos">Gravação/Edição</label>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                <button type="submit" class="btn btn-park mt-4">Salvar</button>
            </form>
        </div>
    </div>
@endsection