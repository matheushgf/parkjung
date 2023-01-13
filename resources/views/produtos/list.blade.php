@extends('layouts.app')

@php
    $action = 'produto';
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
                        <th scope="col">Preço</th>
                        <th scope="col" width="10%">Status</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($produtos as $produto)
                        <tr>
                            <td>{{ $produto->nome }}</td>
                            <td>{{ $produto->descricao }}</td>
                            <td>{{ $produto->preco_editado }}</td>
                            <td>{{ $produto->status_editado }}</td>
                            <td>
                                <form action="{{ route($produto->status == true ? 'produtos.delete' : 'produtos.restore', $produto->id) }}" method="POST" class="d-flex justify-content-evenly">
                                    <a class="btn btn-park" href="{{ route('produtos.edit', $produto->id) }}"><i class="bi bi-pencil-fill"></i></a>

                                    @csrf
                                    @if($produto->status == true)
                                        @method('DELETE')
                                    @else
                                        @method('PUT')
                                    @endif
                                    <button type="submit" class="btn btn-park"><i class="bi bi-{{ $produto->status == true ? 'toggle-off' : 'toggle-on' }}"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {!! $produtos->links() !!}
            </div>
        </div>
    </div>
@endsection