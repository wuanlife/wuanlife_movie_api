<?php
session_start();
if (!isset($_SESSION['id_token'])) {
    header('Location: ./login.php');
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

$resources = Curl::send(Config::$wuanlife_movie_api_url . '/resources/background', 'get', $header, []);

if (200 !=$resources['code']) {
    echo '<script>alert('. $resources['error'] .'); </script>';
}
include './views/_header.php';
?>
<div class="col-md-12">
    <br>

    <div class="row">
        <div class="col-1">
            资源审核
        </div>
        <div class="col-11">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>影视名称</th>
                    <th>资源详情</th>
                    <th>发布人</th>
                    <th>发布时间</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($resources['resources'] as $resource):?>
                <tr>
                    <td><?=$resource['name'] ?></td>
                    <td><?=$resource['instruction'] ?></td>
                    <td><?=$resource['sharer'] ?></td>
                    <td><?=$resource['created_at'] ?></td>
                    <td>
                        <div class="btn-group">
                        <button type="button" class="btn btn-success btn-sm award" data-movie_id="<?=$resource['movie_id'] ?>" data-resource_id="<?=$resource['resource_id'] ?>">加分</button>
                        <button type="button" class="btn btn-sm pass" data-movie_id="<?=$resource['movie_id'] ?>" data-resource_id="<?=$resource['resource_id'] ?>">不加分</button>
                        <button type="button" class="btn btn-danger btn-sm delete" data-movie_id="<?=$resource['movie_id'] ?>" data-resource_id="<?=$resource['resource_id'] ?>">删除</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach;?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include './views/_scripts.php' ?>
</body>
<script>
    $(function() {
        $('.award').click(function() {
            let action = 'award';
            let resource_id = $(this).data('resource_id');
            resource(action, resource_id)
        })

        $('.pass').click(function() {
            let action = 'pass';
            let resource_id = $(this).data('resource_id');
            resource(action, resource_id)
        })

        $('.delete').click(function() {
            if(confirm('确定删除吗? ')) {
                let action = 'delete';
                let resource_id = $(this).data('resource_id');
                resource(action, resource_id, 'delete')
            }
        })

        function resource(action, resource_id, method = 'post')
        {
            $.post('./resourcesAct.php', {action, resource_id, method}, function (res) {
                console.log(res)
                if (res.code === 204) {
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