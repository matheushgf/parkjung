<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Park Jung') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/jquery.js') }}"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/pt-BR.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    
    @stack('script_pagina')

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>
<body>
    <div id="app">
        <div id="wrapper">
            <nav id="navbar-usuario" class="navbar navbar-expand-md">
                <div class="container-fluid">
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Park Jung') }}
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <!-- Left Side Of Navbar -->
                        <ul class="navbar-nav me-auto">

                        </ul>

                        <!-- Right Side Of Navbar -->
                        <ul class="navbar-nav ms-auto">
                            <!-- Authentication Links -->
                            @guest
                                @if (Route::has('login'))
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                    </li>
                                @endif

                                @if (Route::has('register'))
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                    </li>
                                @endif
                            @else
                                <li class="nav-item dropdown">
                                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                        {{ Auth::user()->name }}
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                        <a class="dropdown-item" href="{{ route('logout') }}"
                                        onclick="event.preventDefault();
                                                        document.getElementById('logout-form').submit();">
                                            {{ __('Logout') }}
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                            @csrf
                                        </form>
                                    </div>
                                </li>
                            @endguest
                        </ul>
                    </div>
                </div>
            </nav>

            <main class="">
                <div class="container-fluid">
                    <div class="row">
                        @auth
                            @section('menu')
                                @php
                                    $controller = explode('@', class_basename(app('request')->route()->getAction()['controller']))[0] ?: '';
                                @endphp
                                <div id="menu" class="col-sm-3 col-lg-2 ps-0 pt-3">
                                    <div class="list-group">
                                        <a href="{{ route('home') }}" class="list-group-item list-group-item-action {{ $controller == 'HomeController' ? 'active' : '' }}" aria-current="{{ $controller == 'HomeController' ? 'true' : 'false' }}">
                                            Início
                                        </a>
                                        <a href="{{ route('produtos.list') }}" class="list-group-item list-group-item-action {{ $controller == 'ProdutosController' ? 'active' : '' }}" aria-current="{{ $controller == 'ProdutosController' ? 'true' : 'false' }}">
                                            Produtos
                                        </a>
                                        <a href="{{ route('grupos.list') }}" class="list-group-item list-group-item-action {{ $controller == 'GruposController' ? 'active' : '' }}" aria-current="{{ $controller == 'GruposController' ? 'true' : 'false' }}">
                                            Grupos
                                        </a>
                                        <a href="{{ route('receitas.list') }}" class="list-group-item list-group-item-action {{ $controller == 'ReceitasController' ? 'active' : '' }}" aria-current="{{ $controller == 'ReceitasController' ? 'true' : 'false' }}">
                                            Receitas
                                        </a>
                                    </div>
                                </div>
                            @show
                            <div class="col-sm-9 col-lg-10 pt-3 box-sizing-content">
                        @endauth
                        @guest
                            <div class="col-md-12 pt-3 box-sizing-content">
                        @endguest
                        @auth
                                @if(!empty($listagem))
                                    <div class="row justify-content-end auto-height">
                                        <div class="col-md-12 justify-content-between" id="search">
                                            <div>
                                                <a id="btn-form-modal-novo" type="button" class="btn fs-4" href="{{ route($action . 's.new') }}">
                                                    <i class="bi bi-plus-lg"></i>
                                                </a>
                                            </div>
                                            <div class="main-search-input-wrap">
                                                <form class="main-search-input fl-wrap" action="">
                                                    <div class="main-search-input-item">
                                                        <input name="search" type="text" value="{{ !empty($params['search']) ? $params['search'] : '' }}" placeholder="Procurar" id="search-input">
                                                    </div>
                                                    <button type="submit" class="main-search-button" id="search-button"><i class="bi bi-search"></i></button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                        @endauth
                                @yield('content')
                            </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
<footer>
</footer>
</html>
