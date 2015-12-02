<?php $this->render_partial("/commonViews/header.php"); ?>
<div class="form-box login-box">
    <?php if(isset($errorMessage)):?>
        <div class="alert alert-danger alert-dismissable">
            <i class="fa fa-ban"></i>
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <b>注意！</b> <?php echo $errorMessage; ?>
        </div>
    <?php endif ?>
    <div class="header">登陆</div>
    <form action="/?controller=index&action=login" method="post">
        <div class="body bg-gray">
            <div class="form-group">
                <input type="text" name="username" class="form-control" placeholder="用户名"/>
            </div>
            <div class="form-group">
                <input type="password" name="password" class="form-control" placeholder="密码"/>
            </div>
        </div>
        <div class="footer">
            <button type="submit" class="btn bg-olive btn-block">登陆</button>
        </div>
    </form>
</div>
<?php $this->render_partial("/commonViews/footer.php"); ?>
