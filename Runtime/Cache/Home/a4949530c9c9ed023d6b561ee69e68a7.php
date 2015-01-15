<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>
<head>
	<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>OneLibrary</title>
<link href="/Public/static/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="/Public/static/css/common.css" rel="stylesheet">
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
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/">OneLibrary</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="active" >
                    <a href="/"> Home </a>
                </li>
                <li>
                    <a href="/"> Message </a>
                </li>
                <li>
                    <a href="/"> Document </a>
                </li>
                <li class="dropdown">
                    <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">
                        Settings <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="/">User Management</a>
                        </li>
                        <li>
                            <a href="/">System Settings</a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="/">Preference</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="/">About</a>
                </li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <?php if(is_login()): ?><li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-user"></span> <?php echo get_username();?> <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="<?php echo U('User/profile');?>">Change Password</a></li>
                                <li class="divider"></li>
                                <li><a href="<?php echo U('User/logout');?>">Sign out</a></li>
                            </ul>
                        </li>
                <?php else: ?>
                        <li>
                            <a href="<?php echo U('User/login');?>">Sign in</a>
                        </li>
                        <li>
                            <a href="<?php echo U('User/register');?>">Sign up</a>
                        </li><?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

	<!-- /头部 -->
	
	<!-- 主体 -->
	
<div class="container" style="padding-top: 50px;">
    
  <form class="form-signin" action="/index.php?s=/User/login.html" method="post" role="form">
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
(function(){
	var ThinkPHP = window.Think = {
		"ROOT"   : "", //当前网站地址
		"APP"    : "/index.php?s=", //当前项目地址
		"PUBLIC" : "/Public", //项目公共目录地址
		"DEEP"   : "<?php echo C('URL_PATHINFO_DEPR');?>", //PATHINFO分割符
		"MODEL"  : ["<?php echo C('URL_MODEL');?>", "<?php echo C('URL_CASE_INSENSITIVE');?>", "<?php echo C('URL_HTML_SUFFIX');?>"],
		"VAR"    : ["<?php echo C('VAR_MODULE');?>", "<?php echo C('VAR_CONTROLLER');?>", "<?php echo C('VAR_ACTION');?>"]
	}
})();
</script>

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