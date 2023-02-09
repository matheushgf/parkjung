@extends('layouts.app')

@php
    $action = 'estoque';
@endphp

@section('content')
    <div class="row justify-content-center auto-height">
        @if ($message = Session::get('success'))
            <div class="col-sm-12 col-md-6 mb-3 alert alert-success alert-dismissible fade show success-park text-center" role="alert">
                <p>{{ $message }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        {{ var_dump($params) }}
        <div class="col-md-12">
            <h3 class="text-center mb-3">Histórico Entradas/Saídas</h3>
            <div class="col-sm-12 col-md-6 offset-md-3 mt-2 mb-3">
                <div class="mb-1 form-floating">
                    <input type="text" name="nome" value="{{ $estoque->estocavel->nome }}" placeholder="Item" class="form-control" readonly="readonly">
                    <label for="nome">Nome</label>
                </div>
                <div class="mb-1 form-floating">
                    <input type="text" name="tipo" value="{{ $estoque->tipo_editado }}" placeholder="Tipo" class="form-control" readonly="readonly">
                    <label for="tipo">Tipo</label>
                </div>
                <div class="mb-1 form-floating">
                    <input type="number" name="quantidade" value="{{ $estoque->quantidade }}" placeholder="Quantidade" class="form-control" readonly="readonly">
                    <label for="quantidade">Quantidade em estoque</label>
                </div>
            </div>
            <div class="col-sm-12 mt-2 mb-3 filtros-div p-3">
                <form class="" action="">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="col-sm-2 d-flex align-items-center">
                                <h5 class="mb-0 me-3">Filtros</h5>
                                <button type="submit" class="search-button" id="search-button">Aplicar <i class="bi bi-search ms-2"></i></button>
                            </div>
                        </div>
                        <div class="col-sm-2 filtro">
                            <select name="user" value="" class="select-user input-width-100" placeholder="Usuário" id="select-user"></select>
                        </div>
                        <div class="col-sm-2 filtro">
                            <select name="tipo" class="select-tipo input-width-100" placeholder="Tipo" id="select-tipo">
                                <option value="" {{ empty($params['tipo']) ? 'selected' : '' }}></option>
                                @foreach ($tipos as $idTipo => $descricao)
                                    <option value="{{ $idTipo }}" {{ !empty($params['tipo']) && $params['tipo']==$idTipo ? 'selected' : '' }}>{{ $descricao }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-2 filtro">
                            <div class="form-group">
                                <label for="data-inicio">Data - Início</label>
                                <input type="date" name="data-inicio" value="{{ !empty($params['data-inicio']) ? $params['data-inicio'] : '' }}" placeholder="Data" class="form-control">
                            </div>
                        </div>
                        <div class="col-sm-2 filtro">
                            <div class="form-group">
                                <label for="data-final">Data - Final</label>
                                <input type="date" name="data-final" value="{{ !empty($params['data-final']) ? $params['data-final'] : '' }}" placeholder="Data" class="form-control">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th scope="col" width="15%">Usuário</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Quantidade</th>
                        <th scope="col">Data</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($historicos as $historico)
                        <tr>
                            <td>{{ $historico->usuario->name }}</td>
                            <td>{{ $historico->tipo_editado }}</td>
                            <td>{{ $historico->quantidade }}</td>
                            <td>{{ $historico->created_at }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {!! $historicos->links() !!}
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            var selectUsers = $('.select-user');
            var selectTipo = $('.select-tipo');
            var userSelecionado = {{ !empty($params['user']) ? $params['user'] : 'undefined' }};

            selectUsers.select2({
                placeholder: "Usuário",
                allowClear: true,
                language: 'pt-BR',
                theme: 'bootstrap-5',
                minimumInputLength: 2,
                width: 'element',
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
                        xhr.setRequestHeader('Authorization', 'Bearer {{ $tokenUsuarios }}');
                    }
                }
            });

            selectTipo.select2({
                placeholder: 'Tipo de operação',
                allowClear: true,
                language: 'pt-BR',
                theme: 'bootstrap-5',
                minimumResultsForSearch: Infinity,
                width: 'element'
            });

            if (userSelecionado) {
                $.ajax({
                    url: "{{ route("grupos.api.getUsers") }}",
                    dataType: 'json',
                    method: 'GET',
                    data: {
                        user_id: userSelecionado
                    },
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('Authorization', 'Bearer {{ $tokenUsuarios }}');
                    }
                }).done(function(data) {
                    var usuarios = data.data;

                    if (usuarios.length > 0) {
                        var usuario = usuarios[0];
                        var newOption = new Option(usuario.text, usuario.id, true, true);
                        selectUsers.append(newOption).trigger('change');
                    }
                });
            }
        });
    </script>
@endsection