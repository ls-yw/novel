"use strict";
var isLoad = false;
function loadNext(bookId, id) {
    if (isLoad === true) {
        return;
    }
    isLoad = true;
    $.ajax({
        url:'/book/mAjaxArticle.html?book_id='+bookId+'&id='+id,
        type:'GET',
        dataType:'json',
        success:function (res) {
            if (res.code == 0) {
                $('#read .loadNext').data('id', res.data.nextId);
                let html = '<section class="chapter-list" data-id="'+res.data.article.id+'" data-prev="'+res.data.prevId+'" data-next="'+res.data.nextId+'"><h2>'+res.data.article.title+'</h2><div class="readContent">'+res.data.article.content+'</div></section>';
                $('#read .content .chapterContent').append(html);

            } else {
                $.toptip('系统错误', 'error');
            }
        },
        error:function () {
            $.toptip('加载失败', 'error');
        },
        complete:function () {
            isLoad = false;
        }
    });
}
$(function () {
    $('#read').on('click', '.loadNext', function () {
        $(this).find('a').text('加载中...');
        loadNext($(this).data('book'), $(this).data('id'));
        $(this).find('a').text('加载下一章');
    });
    /*********关灯********/
    $('#read .tool').on('click', '.night', function () {
        if($('body').hasClass('theme-night')) {
            $(this).find('i').toggleClass('fa-moon-o');
            $(this).find('i').removeClass('fa-sun-o');
            $('body').removeClass('theme-night');
            $(this).find('p').text('夜间');
            localStorage.removeItem('nightSet');
        } else {
            $(this).find('i').toggleClass('fa-sun-o');
            $(this).find('i').removeClass('fa-moon-o');
            $('body').removeClass('theme-night');
            $('body').addClass('theme-night');
            $(this).find('p').text('日间');
            localStorage.setItem('nightSet', 'theme-night');
        }
    });
    if (localStorage.getItem('nightSet')){
        $('#read .tool .night').find('i').toggleClass('fa-sun-o');
        $('#read .tool .night').find('i').removeClass('fa-moon-o');
        $('body').addClass('theme-night');
        $('#read .tool .night').find('p').text('日间');
    }
    /*********字号********/
    $('.stylePanel .font-jian').click(function () {
        let fontVal = '';
        if ($('body').hasClass('font-5')) {
            $('body').removeClass('font-5').addClass('font-4');
            fontVal = 'font-4';
        }else if ($('body').hasClass('font-4')) {
            $('body').removeClass('font-4').addClass('font-3');
            fontVal = 'font-3';
        }else if ($('body').hasClass('font-3')) {
            $('body').removeClass('font-3').addClass('font-2');
            $(this).addClass('disabled');
            fontVal = 'font-1';
        }else if ($('body').hasClass('font-1')) {
            fontVal = 'font-1';
        }else {
            $('body').removeClass('font-2').addClass('font-1');
            fontVal = 'font-2';
        }
        $('.stylePanel .font-jia').removeClass('disabled');
        localStorage.setItem('fontSet', fontVal);
    });
    $('.stylePanel .font-jia').click(function () {
        let fontVal = '';
        if ($('body').hasClass('font-1')) {
            $('body').removeClass('font-1').addClass('font-2');
            fontVal = 'font-2';
        }else if ($('body').hasClass('font-3')) {
            $('body').removeClass('font-3').addClass('font-4');
            fontVal = 'font-3';
        }else if ($('body').hasClass('font-4')) {
            $('body').removeClass('font-4').addClass('font-5');
            fontVal = 'font-5';
            $(this).addClass('disabled');
        }else if ($('body').hasClass('font-5')) {
            fontVal = 'font-5';
        }else {
            $('body').removeClass('font-2').addClass('font-3');
            fontVal = 'font-4';
        }
        $('.stylePanel .font-jian').removeClass('disabled');
        localStorage.setItem('fontSet', fontVal);
    });
    let fontSetting = localStorage.getItem('fontSet');
    $('body').addClass(fontSetting);
    if (fontSetting == 'font-1') {
        $('.stylePanel .font-jian').addClass('disabled');
    }else if (fontSetting == 'font-5') {
        $('.stylePanel .font-jia').addClass('disabled');
    }
    /*********背景********/
    $('.themeItem').click(function () {
        if (!$('body').hasClass($(this).data('theme'))) {
            $('body').removeClass('theme-white').removeClass('theme-default').removeClass('theme-green').removeClass('theme-pink');
            $('body').addClass($(this).data('theme'));
            localStorage.setItem('themeItem', $(this).data('theme'));
        }
        $('#read .tool .night').find('i').toggleClass('fa-moon-o');
        $('#read .tool .night').find('i').removeClass('fa-sun-o');
        $('body').removeClass('theme-night');
        $('#read .tool .night').find('p').text('夜间');
        localStorage.removeItem('nightSet');
    });
    $('body').addClass(localStorage.getItem('themeItem') ? localStorage.getItem('themeItem') : 'theme-default');
    /*********工具栏********/
    $('#read .tool').on('click', '.setting', function () {
        $('#read .tool .stylePanel').toggle();
    })
    $('#read').on('click', '.content,.tool', function (event) {
        var e = event || window.event;
        // var x = e.clientX;
        var y = e.clientY;
        var height = $(window).height();

        var num = height/ 4;
        var firstBoxStart = 0;
        var firstBoxEnd = num;
        var secondBoxStart = num;
        var secondBoxEnd = num * 3;
        var threeBoxStart = num * 3;
        var threeBoxEnd = height;

        if (y > secondBoxStart && y <= secondBoxEnd) {
            $('#read .tool').toggle();
            $('#read .tool .stylePanel').hide();
        }
        if (y > firstBoxStart && y <= firstBoxEnd) {
            $(window).scrollTop($(window).scrollTop() - height + 50);
        }
        if (y > threeBoxStart && y <= threeBoxEnd) {
            $(window).scrollTop($(window).scrollTop() + height - 50);
        }
    });
    /*********记忆********/
    let now_top = 0;
    let prev_top = 0;
    $(window).scroll(function () {
        var lastArticleId = articleId;
        var articleTitle = '';
        var prevId = 0;
        var nextId = 0;
        $('.chapter-list').each(function () {
            if(($(this).offset().top - $(window).scrollTop()) < 0) {
                lastArticleId = $(this).data('id');
                articleTitle = $(this).find('h2').text();
                prevId = $(this).data('prev');
                nextId = $(this).data('next');
            }
        });
        if (articleId != lastArticleId) {
            articleId = lastArticleId;
            var stateObject = {};
            var title = "Wow Title";
            var newUrl = "/book/article.html?id="+articleId+"&book_id="+bookId;
            history.pushState(stateObject,title,newUrl);
            $('#read .header h1').text(articleTitle);
            $.post('/book/userBook', {"book_id":bookId,"id":articleId});
            $('.f-prev').data('id', prevId);
            $('.f-next').data('id', nextId);
        }
        if (($(window).scrollTop() + $(window).height()) >= $(document).height()) {
            $('#read .loadNext').click();
        }

        $('.f-prev,.f-next').css('opacity', '0.8');
        now_top = $(document).scrollTop();
    });
    setInterval(function () {
        let op = parseFloat($('.f-prev').css('opacity'));
        if (op > 0.3 && prev_top === now_top && $('#read .tool').css('display') === 'none') {
            $('.f-prev,.f-next').css('opacity', 0.3);
        } else {
            prev_top = now_top;
        }
    }, 2000);
    $('.f-prev,.f-next').click(function () {
        let id = $(this).data('id');
        if (id == 0) {
            $.toptip('已没有章节', 'error');
            return;
        }
        window.location.href = '/book/article.html?id='+id+"&book_id="+bookId;
    });
});

