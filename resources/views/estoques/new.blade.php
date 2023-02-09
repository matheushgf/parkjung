@extends('layouts.app')

@php
    $action = 'estoque';
@endphp

@section('content')
    <div class="row justify-content-end auto-height">
        <span class="btn align-self-end me-3 btn-park text-center" id="btnVoltar"><a href="{{ route('combos.list') }}">Voltar</a></span>
    </div>
    <div class="row justify-content-center auto-height">
        <h3 class="text-center mb-3">Operação de estoque</h3>
        <div class="col-sm-12 col-md-6">
            <form action="{{ route('estoques.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('POST')

                <div class="form-group mb-3" id="div-add-estocavel">
                    <select class="select-estocavel" name="estocavel_id" style="width: 100%" id="select-estocavel">
                    </select>
                    <label for="select-estocavel">Selecionar item</label>
                    @error('estocavel_id')
                        <div class="alert text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-floating">
                    <select class="select-tipo" name="tipo" style="width: 100%" id="select-tipo">
                        @foreach ($tipos as $tipo => $texto)
                            <option value="{{ $tipo }}" {{ old('tipo') == $tipo ? 'selected = "selected"' : '' }}>{{ $texto }}</option>
                        @endforeach
                    </select>
                    <label for="tipo">Tipo de Operação<span class="text-danger">*</span></label>
                    @error('tipo')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-floating">
                    <input type="number" name="quantidade" min="1" value="{{ old('quantidade') }}" placeholder="Quantidade" class="form-control @error('quantidade') mb-1 is-invalid @enderror">
                    <label for="quantidade">Quantidade<span class="text-danger">*</span></label>
                    @error('quantidade')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <input type="hidden" name="estocavel_type" id="estocavel_type">
                <input type="hidden" name="estocavel_nome" id="estocavel_nome">

                <button type="submit" class="btn btn-park">Salvar</button>
            </form>
        </div>
    </div>
    <script>
        const regexTipo = /\./g;
        toastr.options = {
            "closeButton" : true,
            "newestOnTop": true,
            "positionClass": "toast-bottom-right",
            "progressBar": true,
            "timeOut": "4000",
            "extendedTimeOut": "2000"
        };
        var item_selecionado_id = {{ !empty(old('estocavel_id')) ? old('estocavel_id') : 'undefined' }};
        var item_selecionado_type = '{{ !empty(old('estocavel_type')) ? preg_replace('/\\\/', '.', old('estocavel_type')) : 'undefined' }}';
        var item_selecionado_nome = '{{ !empty(old('estocavel_nome')) ? old('estocavel_nome') : 'undefined' }}';

        $(document).ready(function() {
            var selectItem = $('.select-estocavel');
            var selectTipo = $('.select-tipo');

            selectItem.select2({
                placeholder: "Selecionar Item",
                allowClear: false,
                language: 'pt-BR',
                theme: 'bootstrap-5',
                minimumInputLength: 2,
                ajax: {
                    url: "{{ route("receitas.api.getIngredientes") }}",
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
                            results: data
                        };
                    },
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('Authorization', 'Bearer {{ $token }}');
                    }
                }
            });

            selectItem.on('select2:select', function (e) {
                console.log(e.params.data);
                var tipo = e.params.data.tipo.replace(regexTipo, '\\');

                $('#estocavel_type').val(tipo);
                $('#estocavel_nome').val(e.params.data.text);
            });

            selectTipo.select2({
                placeholder: 'Selecionar tipo de operação',
                allowClear: false,
                language: 'pt-BR',
                theme: 'bootstrap-5',
                minimumResultsForSearch: Infinity
            });

            if (item_selecionado_id != undefined && item_selecionado_nome != 'undefined' && item_selecionado_type != 'undefined') {
                var option = new Option(item_selecionado_nome, item_selecionado_id, true, true);
                selectItem.append(option).trigger('change');

                selectItem.trigger({
                    type: 'select2:select',
                    params: {
                        data: {
                            id: item_selecionado_id,
                            text: item_selecionado_nome,
                            tipo: item_selecionado_type
                        }
                    }
                });
            }
        });
    </script>
@endsection