@include('admin.comment.header')
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
            @include('admin.comment.messages')
            @include('admin.comment.errors')
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>昵称</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                @foreach($admins['admins'] as $admin)
                <tr class="item-{{$admin['id']}}">
                    <td>{{$admin['name']}}</td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-danger btn-sm delete" data-id="{{$admin['id']}}">删除</button>
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @include('admin.comment.pagination', ['total' => $admins['total'], 'name' => 'admins.index'])
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
@include('admin.comment.scripts')
</body>
<script>
    $(function() {
        $('table').on('click', '.delete', function () {
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
            data._token = '{{csrf_token()}}'
            $.ajax({
                method: "POST",
                url: "{{route('admins.action')}}",
                data: data
            }).done(function (res) {
                if (res.code === 204 || res.code === 200) {
                    alert('操作成功');
                    if (204 === res.code) {
                        $('.item-' + data.id).remove();
                    }
                    if (200 === res.code) {
                        $('#email').val('')
                        $('#myModal').modal('hide')
                        let tr = '<tr class="item-'+ res.data.id +'">\n' +
                            '    <td>'+ res.data.name +'</td>\n' +
                            '    <td>\n' +
                            '        <div class="btn-group">\n' +
                            '            <button type="button" class="btn btn-danger btn-sm delete" data-id="'+ res.data.id +'">删除</button>\n' +
                            '        </div>\n' +
                            '    </td>\n' +
                            '</tr>'
                        $('tbody').append(tr)
                    }
                } else {
                    alert(res.msg)
                }
            });
        }
    })
</script>
</html>