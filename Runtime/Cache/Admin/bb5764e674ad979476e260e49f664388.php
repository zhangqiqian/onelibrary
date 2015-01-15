<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>
<head>
	<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>OneLibrary</title>
<link href="/Public/static/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="/Public/static/css/common.css" rel="stylesheet">
<link href="/Public/Admin/css/dashboard.css" rel="stylesheet">
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
    
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
          <ul class="nav nav-sidebar">
            <li class="active">
              <a href="/admin">Overview</a>
            </li>
            <li>
              <a href="/admin">Reports</a>
            </li>
            <li>
              <a href="/admin">Analytics</a>
            </li>
            <li>
              <a href="/admin">Export</a>
            </li>
          </ul>
        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
          <h1 class="page-header">Dashboard</h1>
          <div class="jumbotron">
            <h1>Hello, world!</h1>
            <p>This is a template for a simple marketing or informational website. It includes a large callout called a jumbotron and three supporting pieces of content. Use it as a starting point to create something more unique.</p>
            <p><a href="#" class="btn btn-primary btn-lg" role="button">Learn more &raquo;</a></p>
          </div>
      
          <h2 class="sub-header">List groups</h2>
          <div class="row">
            <div class="col-sm-4">
              <ul class="list-group">
                <li class="list-group-item">Cras justo odio</li>
                <li class="list-group-item">Dapibus ac facilisis in</li>
                <li class="list-group-item">Morbi leo risus</li>
                <li class="list-group-item">Porta ac consectetur ac</li>
                <li class="list-group-item">Vestibulum at eros</li>
              </ul>
            </div><!-- /.col-sm-4 -->
            <div class="col-sm-4">
              <div class="list-group">
                <a href="#" class="list-group-item active">
                  Cras justo odio
                </a>
                <a href="#" class="list-group-item">Dapibus ac facilisis in</a>
                <a href="#" class="list-group-item">Morbi leo risus</a>
                <a href="#" class="list-group-item">Porta ac consectetur ac</a>
                <a href="#" class="list-group-item">Vestibulum at eros</a>
              </div>
            </div><!-- /.col-sm-4 -->
            <div class="col-sm-4">
              <div class="list-group">
                <a href="#" class="list-group-item active">
                  <h4 class="list-group-item-heading">List group item heading</h4>
                  <p class="list-group-item-text">Donec id elit non mi porta gravida at eget metus. Maecenas sed diam eget risus varius blandit.</p>
                </a>
                <a href="#" class="list-group-item">
                  <h4 class="list-group-item-heading">List group item heading</h4>
                  <p class="list-group-item-text">Donec id elit non mi porta gravida at eget metus. Maecenas sed diam eget risus varius blandit.</p>
                </a>
                <a href="#" class="list-group-item">
                  <h4 class="list-group-item-heading">List group item heading</h4>
                  <p class="list-group-item-text">Donec id elit non mi porta gravida at eget metus. Maecenas sed diam eget risus varius blandit.</p>
                </a>
              </div>
            </div><!-- /.col-sm-4 -->
          </div>

          <h2 class="sub-header">Panels</h2>
          <div class="row">
            <div class="col-sm-4">
              <div class="panel panel-default">
                <div class="panel-heading">
                  <h3 class="panel-title">Panel title</h3>
                </div>
                <div class="panel-body">
                  Panel content
                </div>
              </div>
              <div class="panel panel-primary">
                <div class="panel-heading">
                  <h3 class="panel-title">Panel title</h3>
                </div>
                <div class="panel-body">
                  Panel content
                </div>
              </div>
            </div><!-- /.col-sm-4 -->
            <div class="col-sm-4">
              <div class="panel panel-success">
                <div class="panel-heading">
                  <h3 class="panel-title">Panel title</h3>
                </div>
                <div class="panel-body">
                  Panel content
                </div>
              </div>
              <div class="panel panel-info">
                <div class="panel-heading">
                  <h3 class="panel-title">Panel title</h3>
                </div>
                <div class="panel-body">
                  Panel content
                </div>
              </div>
            </div><!-- /.col-sm-4 -->
            <div class="col-sm-4">
              <div class="panel panel-warning">
                <div class="panel-heading">
                  <h3 class="panel-title">Panel title</h3>
                </div>
                <div class="panel-body">
                  Panel content
                </div>
              </div>
              <div class="panel panel-danger">
                <div class="panel-heading">
                  <h3 class="panel-title">Panel title</h3>
                </div>
                <div class="panel-body">
                  Panel content
                </div>
              </div>
            </div><!-- /.col-sm-4 -->
          </div>

          <h2 class="sub-header">Section title</h2>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Header</th>
                  <th>Header</th>
                  <th>Header</th>
                  <th>Header</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>1,001</td>
                  <td>Lorem</td>
                  <td>ipsum</td>
                  <td>dolor</td>
                  <td>sit</td>
                </tr>
                <tr>
                  <td>1,002</td>
                  <td>amet</td>
                  <td>consectetur</td>
                  <td>adipiscing</td>
                  <td>elit</td>
                </tr>
                <tr>
                  <td>1,003</td>
                  <td>Integer</td>
                  <td>nec</td>
                  <td>odio</td>
                  <td>Praesent</td>
                </tr>
                <tr>
                  <td>1,003</td>
                  <td>libero</td>
                  <td>Sed</td>
                  <td>cursus</td>
                  <td>ante</td>
                </tr>
                <tr>
                  <td>1,004</td>
                  <td>dapibus</td>
                  <td>diam</td>
                  <td>Sed</td>
                  <td>nisi</td>
                </tr>
                <tr>
                  <td>1,005</td>
                  <td>Nulla</td>
                  <td>quis</td>
                  <td>sem</td>
                  <td>at</td>
                </tr>
                <tr>
                  <td>1,006</td>
                  <td>nibh</td>
                  <td>elementum</td>
                  <td>imperdiet</td>
                  <td>Duis</td>
                </tr>
                <tr>
                  <td>1,007</td>
                  <td>sagittis</td>
                  <td>ipsum</td>
                  <td>Praesent</td>
                  <td>mauris</td>
                </tr>
                <tr>
                  <td>1,008</td>
                  <td>Fusce</td>
                  <td>nec</td>
                  <td>tellus</td>
                  <td>sed</td>
                </tr>
                <tr>
                  <td>1,009</td>
                  <td>augue</td>
                  <td>semper</td>
                  <td>porta</td>
                  <td>Mauris</td>
                </tr>
                <tr>
                  <td>1,010</td>
                  <td>massa</td>
                  <td>Vestibulum</td>
                  <td>lacinia</td>
                  <td>arcu</td>
                </tr>
                <tr>
                  <td>1,011</td>
                  <td>eget</td>
                  <td>nulla</td>
                  <td>Class</td>
                  <td>aptent</td>
                </tr>
                <tr>
                  <td>1,012</td>
                  <td>taciti</td>
                  <td>sociosqu</td>
                  <td>ad</td>
                  <td>litora</td>
                </tr>
                <tr>
                  <td>1,013</td>
                  <td>torquent</td>
                  <td>per</td>
                  <td>conubia</td>
                  <td>nostra</td>
                </tr>
                <tr>
                  <td>1,014</td>
                  <td>per</td>
                  <td>inceptos</td>
                  <td>himenaeos</td>
                  <td>Curabitur</td>
                </tr>
                <tr>
                  <td>1,015</td>
                  <td>sodales</td>
                  <td>ligula</td>
                  <td>in</td>
                  <td>libero</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

</div>
	<!-- /主体 -->
</body>
</html>