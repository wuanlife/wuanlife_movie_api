<?php
session_start();
if (!isset($_SESSION['id_token'])) {
    echo '<script>alert("请先登陆");location.href = "./login.php"; </script>';
}

include './Curl.php';
include './Config.php';

$header[] = 'ID-Token:' . $_SESSION['id_token'];

$result = Curl::send(Config::$wuanlife_oidc_url . '/auth', 'post', $header, ['scope' => 1]);

$_SESSION['access_token'] = $result['Access-Token'];

$header = array(
    'ID-Token:' . $_SESSION['id_token'],
    'Access-Token:' . $_SESSION['access_token'],
);

$admins = Curl::send(Config::$wuanlife_movie_api_url . '/admin', 'get', $header, []);
//var_dump($admins);exit;
if ($admins['code'] == 400) {
    echo '<script>alert("权限不足");location.href = "./login.php"; </script>';
}

include './views/_header.php';
?>
<div class="col-12">
    <br>
    <div class="row justify-content-center">
        <div class="col-10">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
                新增成员
            </button>
        </div>
    </div>
    <br>
    <div class="row justify-content-center">
        <div class="col-md-1 col-sm-4 ">
            团队管理
        </div>
        <div class="col-11">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>昵称</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($admins['admins'] as $admin):?>
                    <tr>
                        <td><?=$admin['name'] ?></td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-danger btn-sm delete" data-id="<?=$admin['id'] ?>">删除</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach;?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 模态框 -->
<div class="modal fade" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- 模态框头部 -->
            <div class="modal-header">
                <h4 class="modal-title">新增管理员</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- 模态框主体 -->
            <div class="modal-body">
                <form>
                    <div class="form-group row">
                        <label for="email" class="col-sm-2 col-form-label">邮箱</label>
                        <div class="col-sm-10">
                            <input type="email" class="form-control" id="email" placeholder="邮箱" required>
                        </div>
                    </div>

            </div>

            <!-- 模态框底部 -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
                <button type="submit" class="btn btn-primary" id="add-admin">确定</button>
            </div>
            </form>
        </div>
    </div>
</div>

<?php include './views/_scripts.php' ?>
</body>
<script>
    $(function() {
        $('.delete').click(function() {
            if(confirm('确定删除吗? ')) {
                let action = 'delete';
                let id = $(this).data('id');
                admins({action, id, method: 'delete'})
            }
        })

        $('form').submit(function(e) {
            e.preventDefault();
            let email = $('#email').val();
            admins({email, method: 'post'})
        })

        function admins(data)
        {
            $.post('./adminAct.php', data, function (res) {
                console.log(res)
                if (res.code === 204 || res.code === 200) {
                    alert('操作成功');
                    location.reload();
                } else {
                    alert(res.msg)
                }
            }, 'json')
        }
    })
</script>
</html>