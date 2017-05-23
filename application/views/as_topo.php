<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <head>
    <script src="<?php echo base_url(); ?>application/views/js/jquery-1.9.1.js"></script>
</head>
<body>

</body>
<script type="text/javascript">
    $.ajax({
        type: "GET",
        async:false,
        url: "<?php echo base_url(); ?>index.php/index/asTopoAjax",
        success: function(data){
            console.log(data);
            //            var d = JSON.parse(data);
            console.log(JSON.parse(data));
        }
    });
</script>
</html>