@extends('layouts.admin')

@section('body')
    <div class="white p20">
        <table class="layui-hide" id="test" lay-filter="test"></table>

        <!-- 表头 -->
        <script type="text/html" id="toolbarDemo">
            @if($role_group->hasRule('auth.user.add'))
            <div class="layui-btn-container" style="margin-top: 10px;">
                <form class="layui-form" action="" lay-filter="form">
                    <div class="layui-row">
                        <div class="layui-col-md2">
                            <div class="layui-inline">
                                <input class="layui-input layui-btn-sm" name="nicname" id="mobile" autocomplete="off" placeholder="用户昵称">
                            </div>
                        </div>
                        <div class="layui-col-md2">
                            <button class="layui-btn layui-btn-sm searchBtn">搜索</button>
                        </div>
                    </div>
                </form>
            </div>
            @endif
        </script>

        <!-- 状态 -->
        <script type="text/html" id="switchStatus">
            <input type="checkbox" name="status" value="@{{d.id}}" lay-skin="switch"
                   @if(!$role_group->hasRule('auth.rule.set')) disabled="off" @endif lay-text="启动|禁用"
                   lay-filter="status" @{{ d.status== 1 ? 'checked' : '' }}>
        </script>


        <!-- 操作 -->
        <script type="text/html" id="barDemo">
            @if($role_group->hasRule('setting.problem'))
            <a class="layui-btn layui-btn-xs" lay-event="edit">查看</a>
            @endif

            @if($role_group->hasRule('setting.problem'))
            <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">标记处理</a>
            @endif
        </script>
    </div>
@endsection


@section('javascriptFooter')
    <script>
        var datatable;
        $(document).on('click','.searchBtn',function () {
            datatable.reload({
                where:{
                    mobile:$('#mobile').val().trim()
                },
                page:{
                    curr:1
                }
            })
        });
        layui.use('table', function () {
            var table = layui.table, form = layui.form;

            datatable = table.render({
                elem: '#test'
                , url: '/setting/problem'
                , method: 'post'
                , toolbar: '#toolbarDemo'
                , title: '用户列表'
                , cols: [[
                    {field: 'id', title: 'ID', width: 80, fixed: 'left'}
                    , {field: 'uid', title: '用户ID', width: 100}
                    , {field: 'content', title: '用户联系方式', width: 300}
                    , {field: 'content', title: '反馈内容', width: 200}
                    , {field: 'created_at', title: '提交时间', width: 150}
                    , {field: 'status', title: '状态', width: 100, templet: function(res) {
                            return res.status == 1 ? '处理中' : '已处理'
                        }}
                    , {fixed: 'right', title: '操作', toolbar: '#barDemo', width: 150}
                ]]
                ,	parseData:function(res){
                    //这个函数非常实用，是2.4.0版本新增的，当后端返回的数据格式不符合layuitable需要的格式，用这个函数对返回的数据做处理，在2.4.0版本之前，只能通过修改table源码来解决这个问题
                    return {
                        code: res.code,
                        msg:res.status,
                        count:res.count, //总页数，用于分页
                        data:res.data
                    }
                }
                , defaultToolbar: []
                , page: true
            });


            //头工具栏事件
            table.on('toolbar(test)', function (obj) {
                var checkStatus = table.checkStatus(obj.config.id);
                switch (obj.event) {
                    case 'add':
                        location.href = "/user/add";
                        break;
                }
                ;
            });


            window.refresh = function()
            {

                datatable.reload();
            }
            form.on('switch(status)', function (obj) {
                let datajson = {key: 'status', value: obj.elem.checked ? '1' : '0'};

                $.post('/user/set/' + this.value, datajson, function (data) {
                    if (data.code != 0) {
                        layer.msg(data.msg);
                        obj.elem.checked = !obj.elem.checked;
                        form.render();
                    }
                });
            });


            //监听行工具事件
            table.on('tool(test)', function (obj) {
                var data = obj.data;
                switch (obj.event) {
                    case 'add_rule':
                        location.href = '/user/add/' + data.id;
                        break;
                    case 'del':
                        layer.confirm('真的删除行么', function (index) {
                            $.post('/user/del/' + data.id, '', function (data) {
                                layer.close(index);
                                if (data.code != 0) {
                                    layer.msg(data.msg);
                                } else {
                                    obj.del();
                                }
                            });
                        });
                        break;
                    case 'edit':
                        layer.open({
                            title: '编辑权限'
                            , type: 2
                            , content: '/user/edit/' + data.id
                            , area: ['500px', '505px']
                        });
                        break;
                }
            });
        });
    </script>
@endsection
