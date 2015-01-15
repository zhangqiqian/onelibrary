<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>
<head>
	<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>OneLibrary</title>
<link href="/Public/static/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="/Public/static/css/common.css" rel="stylesheet">
<link href="/Public/Api/css/dashboard.css" rel="stylesheet">
<link rel="shortcut icon" href="/Public/static/img/favicon.png">

<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
<script src="/Public/static/js/html5shiv.min.js"></script>
<![endif]-->

<!--[if lt IE 9]>
<script type="text/javascript" src="/Public/static/js/jquery-1.11.1.min.js"></script>
<![endif]-->
<!--[if gte IE 9]><!-->
<script type="text/javascript" src="/Public/static/js/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="/Public/static/bootstrap/js/bootstrap.min.js"></script>

<!--<![endif]-->
<!-- 页面header，一般用于加载插件CSS文件和代码 -->

</head>
<body>
	<!-- 头部 -->
	<!-- 导航条
================================================== -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/admin">OneLibrary Center</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav navbar-right">
                <li class="active">
                    <a href="/admin"> Dashboard </a>
                </li>
                <li>
                    <a href="/admin"> Settings </a>
                </li>
                <li>
                    <a href="/admin"> Profile </a>
                </li>
                <li>
                    <a href="/"> Preview </a>
                </li>
                <?php if(is_login()): ?><li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-user"></span> <?php echo get_username();?> <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo U('Public/logout');?>">Sign out</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="<?php echo U('Public/login');?>">Sign in</a>
                    </li><?php endif; ?>
            </ul>
            
        </div>
    </div>
</nav>
	<!-- /头部 -->
	
	<!-- 主体 -->
	
<div class="container">
    
  <form class="form-signin" action="/index.php?s=/Api/Public/login.html" method="post" role="form">
      <h2 class="form-signin-heading">Please sign in</h2>
      <input type="text" name="username" class="form-control first-text" placeholder="Email address" ajaxurl="/member/checkUserNameUnique.html" errormsg="Username are 1-16 characters." nullmsg="Username is required." datatype="*1-16" value="" required autofocus>
      <input type="password" name="password" class="form-control middle-text" placeholder="Password" errormsg="Password are 6-20 characters." nullmsg="Password is required." datatype="*6-20" required>
      <input type="text" class="form-control last-text" placeholder="Verification code" errormsg="please input verification code." nullmsg="please input verification code." datatype="*5-5" name="verify">
      <img class="verifyimg reloadverify" alt="Click to switch" title="Click to switch" src="<?php echo U('verify');?>" style="cursor:pointer;">
      <div class="Validform_checktip text-warning"></div>
      <div class="checkbox">
          <label>
            <input type="checkbox"  value="remember-me"> Remember me
          </label>
      </div>
      <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
  </form>

</div>
	<!-- /主体 -->

	<!-- 底部 -->
	
    <!-- 底部
    ================================================== -->
    <footer class="common-footer">
        <p>
            Designed and built with all the love in the world by
            <a href="mailto:43874051@qq.com">@niko</a>. 
            Maintained by the niko team with the help of our contributors.
        </p>
        <p>
            All content copyright &copy; 2014 • All rights reserved.
        </p>
    </footer>


	<script type="text/javascript">

    	$(document)
	    	.ajaxStart(function(){
	    		$("button:submit").addClass("log-in").attr("disabled", true);
	    	})
	    	.ajaxStop(function(){
	    		$("button:submit").removeClass("log-in").attr("disabled", false);
	    	});


    	$("form").submit(function(){
    		var self = $(this);
    		$.post(self.attr("action"), self.serialize(), success, "json");
    		return false;

    		function success(data){
    			if(data.status){
    				window.location.href = data.url;
    			} else {
    				self.find(".Validform_checktip").text(data.info);
    				//刷新验证码
    				$(".reloadverify").click();
    			}
    		}
    	});

		$(function(){
			  var verifyimg = $(".verifyimg").attr("src");
        $(".reloadverify").click(function(){
            if( verifyimg.indexOf('?')>0){
                $(".verifyimg").attr("src", verifyimg+'&random='+Math.random());
            }else{
                $(".verifyimg").attr("src", verifyimg.replace(/\?.*$/,'')+'?'+Math.random());
            }
        });
        $(".common-footer").addClass("static-footer");
		});
	</script>
 <!-- 用于加载js代码 -->
<div class="hidden"><!-- 用于加载统计代码等隐藏元素 -->
	
</div>

	<!-- /底部 -->
</body>
</html>