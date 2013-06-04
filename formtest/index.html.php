<html>
<head>
    <title>Standalone Form Component</title>
</head>
<body>
<?php var_dump($view['form']); exit; ?>
<form action="#" method="post">
    <?php echo $view['form']->widget($form); ?>
    <input type="submit" />
</form>
</body>
</html>