@extends('layouts.app')

@php
    $action = 'receita';
    $qtdIngredientes = 0;
@endphp

@section('content')
    <div class="row justify-content-end auto-height">
        <span class="btn align-self-end me-3 btn-park text-center" id="btnVoltar"><a href="{{ route('receitas.list') }}">Voltar</a></span>
    </div>
    <div class="row justify-content-center auto-height">
        <h3 class="text-center mb-3">Edição - Receita</h3>
        <div class="col-sm-12 col-md-6">
            <form action="{{ route('receitas.update', ['receita' => $receita]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3 form-floating">
                    <input name="nome" class="form-control @error('nome') mb-1 is-invalid @enderror" type="text" placeholder="Nome" value="{{ $receita->nome }}">
                    <label for="nome">Nome<span class="text-danger">*</span></label>
                    @error('nome')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-floating">
                    <input name="descricao" class="form-control @error('descricao') mb-1 is-invalid @enderror" type="text" placeholder="Descrição" value="{{ $receita->descricao }}">
                    <label for="descricao">Descrição</label>
                    @error('descricao')
                        <div class="alert text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-floating">
                    <input name="gerado" class="form-control @error('gerado') mb-1 is-invalid @enderror" type="number" step="1" placeholder="Quantidade gerada" value="{{ $receita->gerado }}">
                    <label for="gerado">Quantidade gerada</label>
                    @error('gerado')
                        <div class="alert text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-floating">
                    <input name="preco" class="form-control @error('preco') mb-1 is-invalid @enderror" type="number" step="0.01" placeholder="Preço" value="{{ $receita->preco }}">
                    <label for="preco">Preço<span class="text-danger">*</span></label>
                    @error('preco')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div id="produtos-receita">
                    <hr class="hr divider"/>
                    <h5 class="mb-3">Ingredientes</h5>
                    <div>
                        <div class="form-group mb-3" id="div-add-produto">
                            <label for="select-produto">Adicionar Produto</label>
                            <select class="select-produto" name="produto-add" style="width: 100%" id="select-produto">
                            </select>
                        </div>
                        <div class="form-group mb-3" id="div-add-receita">
                            <label for="select-receita">Adicionar Receita</label>
                            <select class="select-receita" name="receita-add" style="width: 100%" id="select-receita">
                            </select>
                        </div>
                    </div>
                    <div id="ingredientes" class="mt-3">
                        @error('ingredientes.*.quantidade')
                            <div class="text-danger">Todos os campos de quantidade devem ser preenchidos</div>
                        @enderror
                    </div>
                </div>
                <button type="submit" class="btn btn-park">Salvar</button>
            </form>
            <div id="jsonIngredientes" class="d-none">
                {{ $jsonIngredientes }}
            </div>
        </div>
    </div>
    <script>
        const regexTipo = /\./g;
        var ingredientes = [];
        var ingredientesTipo = {};
        var proximoItem = 0;
        var qtdIngredientes = 0;
        toastr.options = {
            "closeButton" : true,
            "newestOnTop": true,
            "positionClass": "toast-bottom-right",
            "progressBar": true,
            "timeOut": "4000",
            "extendedTimeOut": "2000"
        };

        function addItem(item){
            var tipo = item.type.replace(regexTipo, '\\');

            if (!(tipo in ingredientesTipo)) {
                ingredientesTipo[tipo] = [];
            }

            if (ingredientesTipo[tipo].includes(item.id)) {
                toastr.error("Ingrediente já adicionado", "Erro ao adicionar ingrediente");
                return ;
            }
            if (
                item.id == {{ $receita->id }}
                && tipo == 'App\\Models\\Receita'
            ) {
                toastr.error("Você não pode adicionar a própria receita como ingrediente", "Erro ao adicionar ingrediente");
                return ;
            }

            ingredientesTipo[tipo].push(item.id);

            var novoItem = `
            <div class="ingrediente-item" id="ingrediente-${proximoItem}">
                <hr class="hr divider"/>
                <h6 class="mb-3">Item <span class="ingrediente-title">${qtdIngredientes+1}</span> <button type="button" class="btn btn-park ingrediente-item-remover ms-2" id="ingrediente-remover-${proximoItem}" data-id="${proximoItem}"><i class="bi bi-x-lg"></i></button></h6>
                <div class="mb-1 form-floating">
                    <input type="text" name="ingredientes[${proximoItem}][nome]" value="${item.text}" placeholder="Nome" readonly="readonly" class="form-control @error('ingredientes[${proximoItem}][nome]') mb-1 is-invalid @enderror">
                    <label for="ingredientes[${proximoItem}][nome]">Nome</label>
                </div>
                <div class="mb-1 form-floating">
                    <input type="number" name="ingredientes[${proximoItem}][quantidade]" ${item.quantidade ? 'value="' + item.quantidade + '"' : ''} placeholder="Quantidade" class="form-control @error('ingredientes.${proximoItem}.quantidade') mb-1 is-invalid @enderror">
                    <label for="ingredientes[${proximoItem}][quantidade]">Quantidade<span class="text-danger">*</span></label>
                </div>
                <input type="hidden" name="ingredientes[${proximoItem}][tipo]" value="${tipo}">
                <input type="hidden" name="ingredientes[${proximoItem}][id]" value="${item.id}">
            </div>`;

            $('#ingredientes').append(novoItem);
            comportamentoRemover();
            qtdIngredientes++;
            proximoItem++;
        }

        function removerItem(index){
            var item = $(`#ingrediente-${index}`);
            var id = $(`input[name="ingredientes[${index}][id]"]`).val();
            var tipo = $(`input[name="ingredientes[${index}][tipo]"]`).val();

            ingredientesTipo[tipo] = ingredientesTipo[tipo].filter(function(valor){ 
                return valor != id;
            });

            item.remove();
            qtdIngredientes--;
            var novaQtdIngredientes = 1;
            
            $('.ingrediente-item').each(function(){
                var elTitulo = $(this).find('.ingrediente-title');
                elTitulo.html('');
                elTitulo.append(`${novaQtdIngredientes}`);
                novaQtdIngredientes++;
            });            
        }

        function comportamentoRemover(){
            $('.ingrediente-item-remover').unbind('click');
            $('.ingrediente-item-remover').click(function(){
                removerItem($(this).data('id'));
            });
        }

        $(document).ready(function() {
            var selectProduto = $('.select-produto');
            var selectReceita = $('.select-receita');

            selectProduto.select2({
                placeholder: "Selecionar Produto",
                allowClear: false,
                language: 'pt-BR',
                theme: 'bootstrap-5',
                minimumInputLength: 3,
                ajax: {
                    url: "{{ route("produtos.api.getProdutos") }}",
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
            selectProduto.on('select2:select', function (e) { 
                addItem(e.params.data);
                selectProduto.val(null).trigger('change');
            });
            selectReceita.select2({
                placeholder: "Selecionar Receita",
                allowClear: false,
                language: 'pt-BR',
                theme: 'bootstrap-5',
                minimumInputLength: 3,
                ajax: {
                    url: "{{ route("receitas.api.getReceitas") }}",
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
            selectReceita.on('select2:select', function (e) {
                addItem(e.params.data);
                selectReceita.val(null).trigger('change');
            });

            ingredientes = $.parseJSON($('#jsonIngredientes').html());
            $('#jsonIngredientes').remove();
            ingredientes.forEach(item => addItem(item));

            comportamentoRemover();
        });
    </script>
@endsection