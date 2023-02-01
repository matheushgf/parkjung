@extends('layouts.app')

@php
    $action = 'receita';
@endphp

@section('content')
    <div class="row justify-content-center auto-height">
        @if ($message = Session::get('success'))
            <div class="col-sm-12 col-md-6 mb-3 alert alert-success alert-dismissible fade show success-park text-center" role="alert">
                <p>{{ $message }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="col-md-12">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th scope="col" width="15%">Nome</th>
                        <th scope="col">Descrição</th>
                        <th scope="col">Quantidade gerada</th>
                        <th scope="col">Preço</th>
                        <th scope="col">Status</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($receitas as $receita)
                        <tr>
                            <td>{{ $receita->nome }}</td>
                            <td>{{ $receita->descricao }}</td>
                            <td>{{ $receita->gerado }}</td>
                            <td>{{ $receita->preco_editado }}</td>
                            <td>{{ $receita->status_editado }}</td>
                            <td>
                                <form action="{{ route($receita->status == true ? 'receitas.delete' : 'receitas.restore', $receita->id) }}" method="POST" class="d-flex justify-content-evenly">
                                    <a class="btn btn-park" href="{{ route('receitas.editar', $receita->id) }}"><i class="bi bi-pencil-fill"></i></a>
                                    @csrf
                                    @if($receita->status == true)
                                        @method('DELETE')
                                    @else
                                        @method('PUT')
                                    @endif
                                    <button type="submit" class="btn btn-park"><i class="bi bi-{{ $receita->status == true ? 'toggle-off' : 'toggle-on' }}"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {!! $receitas->links() !!}
            </div>
        </div>
    </div>
@endsection