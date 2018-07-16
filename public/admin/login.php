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
<div class="container">
    <div class="row justify-content-md-center" style="margin-top: 15%;">
        <div class="col-md-6">

            <div class="card">
                <div class="card-header">午安影视管理中心</div>
                <div class="card-body">
                    <form>
                        <div class="form-group row">
                            <label for="email" class="col-sm-2 col-form-label">邮箱</label>
                            <div class="col-sm-10">
                                <input type="email" class="form-control" id="email" placeholder="邮箱" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="password" class="col-sm-2 col-form-label">密码</label>
                            <div class="col-sm-10">
                                <input type="password" class="form-control" id="password" placeholder="密码" required>
                            </div>
                        </div>

                        <div class="form-group row justify-content-center">
                            <div class="col-sm-2">
                                <button type="submit" id="login" class="btn btn-primary">登&nbsp;&nbsp;陆</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>


</div>

<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
</body>
<script>
    $(function() {
        $('form').submit(function(e) {
            e.preventDefault();
            let email = $('#email').val();
            let password = $('#password').val();
            let action = 'login';
            $.post('./loginAct.php', {email, password, action}, function (res) {
                console.log(res)
                if (res.code === 200) {
                    localStorage.id_token = res.id_token;
                    alert('登陆成功');
                    location.href = './resources.php'
                } else {
                    alert(res.msg)
                    $('#password').val('');
                }
            }, 'json')
        })
    })
</script>
</html>