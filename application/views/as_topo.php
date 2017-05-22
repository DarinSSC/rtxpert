<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
</head>
<body>
<h1>as_list</h1>
<ul>
    <?php foreach ($as_list as $as){?>
        <li>
            <?=$as?>
        </li>
    <?php } ?>
</ul>
<h1>inter_link</h1>
<ul>
    <?php foreach ($inter_link as $cur_link){?>
        <li>
            <?=$cur_link['src_as']?>=><?=$cur_link['dest_as']?>
            <?php foreach ($cur_link['links'] as $links){?>
                <li>
                    <?=$links['src']?>=><?=$links['dest']?>
                </li>
        <?php } ?>
        </li>
    <?php } ?>
</ul>
<br/><br/><br/>
<h1>inner_link</h1>
<ul>
    <?php foreach ($as_list as $as){?>
        <?=$as ?>
        <?php foreach ($inner_link[$as] as $cur_link){?>
            <li>
                <?=$cur_link['src']?>=><?=$cur_link['dest']?>
            </li>
        <?php } ?>
        <br/>
    <?php } ?>
</ul>
</body>
</html>