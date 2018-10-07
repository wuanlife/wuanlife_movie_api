<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css"
          integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">

    <title>午安影视管理中心</title>
</head>
<body>
<div class="container">
    <div class="row justify-content-md-center" style="margin-top: 15%;">
        <div class="col-md-6">

            <div class="card">
                <div class="card-header">午安影视管理中心</div>
                <div class="card-body">
                    @include('comment.messages')
                    @include('comment.errors')
                    <form action="{{route('admin.login')}}" method="post">
                        {{csrf_field()}}
                        <input type="hidden" name="client_id" value="wuan">
                        <div class="form-group row">
                            <label for="email" class="col-sm-2 col-form-label">邮箱</label>
                            <div class="col-sm-10">
                                <input type="email" class="form-control" name="email" placeholder="邮箱" value="{{ old('email') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="password" class="col-sm-2 col-form-label">密码</label>
                            <div class="col-sm-10">
                                <input type="password" class="form-control" name="password" placeholder="密码" required>
                            </div>
                        </div>

                        <div class="form-group row justify-content-center">
                            <div class="col-sm-2">
                                <button type="submit" data-loading-text="登录中..." id="login" class="btn btn-primary">登&nbsp;&nbsp;录</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
</body>
</html>