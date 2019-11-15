function login() {
    $.closeModal();
    $.login({
        title: '登录',
        text: '没有账号？那就<span class="registerAlert green">去注册</span>呀',
        onOK: function (username, password) {
            $.ajax({
                url: '/member/login',
                data: {
                    'username': username,
                    'password': password
                },
                dataType: 'json', //服务器返回json格式数据
                type: 'POST', //HTTP请求类型
                timeout: 10000, //超时时间设置为10秒；
                headers: {
                    'token': localStorage.getItem('token')
                },
                success: function(res) {
                    if (res.code == 0) {
                        localStorage.setItem('token', res.data);
                        $.toptip(res.msg, 'success');
                        getUserInfo();
                        return true;
                    } else {
                        $.toptip(res.msg, 'error');
                        return false;
                    }
                },
                error: function(xhr, type, errorThrown) {
                    $.toptip('系统错误', 'error');
                    return false;
                }
            });
        }
    });
}

function register() {
    $.closeModal();
    $.modal({
        title: "注册会员",
        text: '<p class="weui-prompt-text">我有帐号，<span class="loginAlert green">去登录</span> </p>' +
               '<input type="text" class="weui-input weui-prompt-input" id="register-username" value="" onkeyup="this.value=this.value.replace(/[^a-zA-Z]/ig,\'\')"  placeholder="输入用户名，只限字母" />'+
               '<input type="password" class="weui-input weui-prompt-input" id="register-password" value="" placeholder="输入密码" />'+
               '<input type="password" class="weui-input weui-prompt-input" id="register-repassword" value="" placeholder="确认密码" />',
        buttons: [
            { text: "注册", onClick: function(){
                    let username = $('#register-username').val();
                    let password = $('#register-password').val();
                    let repassword = $('#register-repassword').val();
                    if (username.length < 6) {
                        $.toptip('用户名不能少于6位', 'error');
                        return false;
                    }
                    if (password.length < 6) {
                        $.toptip('请输入正确的密码', 'error');
                        return false;
                    }
                    if (password != repassword) {
                        $.toptip('两次密码不一致', 'error');
                        return false;
                    }
                    $.ajax({
                        url: '/member/register',
                        data: {
                            'username': username,
                            'password': password
                        },
                        dataType: 'json', //服务器返回json格式数据
                        type: 'POST', //HTTP请求类型
                        timeout: 10000, //超时时间设置为10秒；
                        headers: {
                            'token': localStorage.getItem('token')
                        },
                        success: function(res) {
                            if (res.code == 0) {
                                localStorage.setItem('token', res.data);
                                $.toptip(res.msg, 'success');
                                getUserInfo();
                                $.closeModal();
                                return true;
                            } else {
                                $.toptip(res.msg, 'error');
                                return false;
                            }
                        },
                        error: function(xhr, type, errorThrown) {
                            $.toptip('系统错误', 'error');
                            return false;
                        }
                    });
            } },
            { text: "取消", className: "default", onClick: function (){$.closeModal();}} ,
        ],
        autoClose: false
    });
}

function getUserInfo()
{
    $.ajax({
        url: '/member/info',
        dataType: 'json', //服务器返回json格式数据
        type: 'GET', //HTTP请求类型
        timeout: 10000, //超时时间设置为10秒；
        headers: {
            'token': localStorage.getItem('token')
        },
        success: function(res) {
            if (res.code == 201) {

            } else if (res.code == 0) {
                $('.nav-top .member').html('<a href="/member/index" class="red">'+res.data.username+'</a>')
            } else {
                $.toptip(res.msg, 'error');
            }
        },
        error: function(xhr, type, errorThrown) {
            $.toptip('系统错误', 'error');
        }
    });
}

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
                'token': localStorage.getItem('token')
            },
            success: function(res) {
                if (res.code == 201) {
                    $.toptip('您未登录，请先登录', 'error');
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
});