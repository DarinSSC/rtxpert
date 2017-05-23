<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<head>
    <script src="<?php echo base_url(); ?>application/views/js/jquery-1.9.1.js"></script>
</head>
<body>
    new warning num: <?=$new_warning_num ?><br/>
    <ul>
        <?php foreach ($new_warning as $cur_new){ ?>
            <li>
<!--                --><?//=$cur_new['code'] ?>
                <?php foreach ($cur_new as $key=>$value) { ?>
                    <?=$value ?>..
                <?php } ?>
            </li>
        <?php } ?>
    </ul>
    <br/>
    withdraw warning num: <?=$withdraw_warning_num ?><br/>
    <ul>
        <?php foreach ($withdraw_warning as $cur_withdraw){ ?>
            <li>
                <?=$cur_withdraw['code'] ?>
            </li>
        <?php } ?>
    </ul>
</body>
</html>