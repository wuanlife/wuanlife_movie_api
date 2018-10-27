<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">

    <title>午安影视管理中心</title>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">午安影视管理中心</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="{{route('admins.index')}}">成员管理</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{route('resources.index')}}">内容管理</a>
            </li>

        </ul>

        <ul class="navbar-nav col-auto">
            <li class="nav-item">
                <a class="nav-link" href="#">{{session('wuan.user_info.name')}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="event.preventDefault();  document.getElementById('logout-form').submit();">退出</a>
                <form id="logout-form" action="{{route('auth.logout')}}" method="post" style="display: none;">
                    {{csrf_field()}}
                    <input type="hidden" name="_method" value="DELETE">
                </form>
            </li>

        </ul>

    </div>
</nav>