<html>
    <head>
        <title>大转盘</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script type="text/javascript" src="https://res.wx.qq.com/open/js/jweixin-1.3.2.js"></script>
        <script type="text/javascript" src="../layui/layui.all.js"></script>
        <script src="js/jquery.min.js"></script>
        <script src="js/lufylegend-1.10.1.min.js"></script>
        <style type="text/css">
            body {
                background-color: #5c77f9;
            }
            * {
                margin: 0px;
                padding: 0px;
            }
            #canvas {
                margin: auto;
            }
        </style>
        <script type="text/javascript">
            var sessionId = '<?php echo $_GET["session_id"] ?>';
            var activityId = '<?php echo $_GET["activity_id"] ?>';
            var publicDir = './';
            var rotate = 0;
            var width = 300;
            var height = 400;
            $(document).ready(function () {
                width = $(window).width();
                height = $(window).height();
                LInit(25, 'canvas', width, height, main);
                LGlobal.preventDefault = false;
                $('#canvas').width(width);
                $('#canvas').height(height);
                $('#rotate_option').change(function () {
                    rotate = $(this).val();
                });
            });
        </script>
        <script type="text/javascript" src="js/bigWheel.js"></script>
    </head>
    <body>
        <div id="canvas"></div>
    </body>
</html>
