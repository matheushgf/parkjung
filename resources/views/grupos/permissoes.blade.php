@extends('layouts.app')

@php
    $action = 'grupo';
@endphp
    
@section('content')
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
                        <label for="select-user">Usuários do grupo</label>
                        <select class="select-user" name="users[]" multiple="multiple" style="width: 100%"
                          id="select-user">
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
            <div id="jsonUsuarios" class="d-none">
                {{ $jsonUsuarios }}
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            var selectUsers = $('.select-user');
            selectUsers.select2({
                placeholder: "Usuários",
                allowClear: false,
                language: 'pt-BR',
                theme: 'bootstrap-5',
                minimumInputLength: 3,
                ajax: {
                    url: "{{ route("grupos.api.getUsers") }}",
                    dataType: 'json',
                    method: 'GET',
                    data: function (params) {
                        var query = {
                            search: params.term
                        }
                    
                        return query;
                    },
                    processResults: function (data) {
                        return {
                            results: data.data
                        };
                    },
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('Authorization', 'Bearer {{ $token }}');
                    }
                }
            });

            var usuariosGrupo = $.parseJSON($('#jsonUsuarios').html());
            $('#jsonUsuarios').remove();

            usuariosGrupo.forEach(function(usuario){
                var optionUsuario = new Option(usuario.name, usuario.id, true, true);
                selectUsers.append(optionUsuario).trigger('change');
            });
        });
    </script>
@endsection