$(function () {
    $('img.back').click(function () {
        window.history.go(-1);
    });

    $(function() {
        $('.add-userBook').click(function(){
            let book_id = $(this).data('id');
            let obj = $(this);
            if (obj.attr('disabled')) {
                return false;
            }
            $.ajax({
                url: '/member/addBook?id='+book_id,
                dataType: 'json', //服务器返回json格式数据
                type: 'GET', //HTTP请求类型
                timeout: 10000, //超时时间设置为10秒；
                headers: {
                    'token': localStorage.getItem('token') ? localStorage.getItem('token') : ''
                },
                success: function(res) {
                    if (res.code == 201) {
                        $.confirm("您还未登录，是否前往登录？", function() {
                            window.location.href = '/member/login';
                        });
                    } else if (res.code == 0) {
                        $.toptip('加入成功', 'success');
                        obj.text('已在书架').attr('disabled', true);
                    } else {
                        $.toptip(res.msg, 'error');
                    }
                },
                error: function(xhr, type, errorThrown) {
                    $.toptip('系统错误', 'error');
                }
            });
        });
        $('.nav-top .back').click(function () {
            history.go(-1);
        });
    });
});