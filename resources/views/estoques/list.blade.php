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
        <div class="col-md-12">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th scope="col" width="15%">Nome</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Quantidade disponível</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($estoques as $estoque)
                        <tr>
                            <td>{{ $estoque->estocavel->nome }}</td>
                            <td>{{ $estoque->tipo_editado }}</td>
                            <td>{{ $estoque->quantidade }}</td>
                            <td>
                                <a class="btn btn-park" href="{{ route('estoques.historico', $estoque->id) }}"><i class="bi bi-pencil-fill"></i></a>  
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {!! $estoques->links() !!}
            </div>
        </div>
    </div>
@endsection