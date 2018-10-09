@include('admin.comment.header')

<div class="col-md-12">
    <br>
    <div class="row">
        <div class="col-1">
            资源审核
        </div>
        <div class="col-11">
            @include('admin.comment.messages')
            @include('admin.comment.errors')
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
                @foreach ($resources['resources'] as $resource)
                    <tr class="item-{{$resource['resource_id']}}">
                        <td style=" white-space:nowrap">{{$resource['name']}}</td>
                        <td style="word-wrap:break-word;word-break:break-all;">{!!$resource['instruction']!!}</td>
                        <td>{{$resource['sharer']}}</td>
                        <td style=" white-space:nowrap">{{$resource['created_at']}}</td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-success btn-sm award"
                                        data-movie_id="{{$resource['movie_id']}}"
                                        data-resource_id="{{$resource['resource_id']}}">加分
                                </button>
                                <button type="button" class="btn btn-sm pass" data-movie_id="{{$resource['movie_id']}}"
                                        data-resource_id="{{$resource['resource_id']}}">不加分
                                </button>
                                <button type="button" class="btn btn-danger btn-sm delete"
                                        data-movie_id="{{$resource['movie_id']}}>"
                                        data-resource_id="{{$resource['resource_id']}}">删除
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            @include('admin.comment.pagination', ['total' => $resources['total'], 'name' => 'resources.index'])
        </div>
    </div>
</div>
@include('admin.comment.scripts')
</body>
<script>
    $(function () {
        $('.award').click(function () {
            let action = 'award'
            let resource_id = $(this).data('resource_id')
            resource(action, resource_id)
        })

        $('.pass').click(function () {
            let action = 'pass'
            let resource_id = $(this).data('resource_id')
            resource(action, resource_id)
        })

        $('.delete').click(function () {
            if (confirm('确定删除吗? ')) {
                let action = 'delete'
                let resource_id = $(this).data('resource_id')
                resource(action, resource_id)
            }
        })

        function resource(action, resource_id) {
            $.ajax({
                method: "POST",
                url: "{{route('resources.index')}}",
                data: {action, resource_id}
            }).done(function (res) {
                if (res.code === 204) {
                    alert('操作成功');
                    $('.item-' + resource_id).remove();
                } else {
                    alert(res.msg)
                }
            });
        }
    })
</script>
</html>