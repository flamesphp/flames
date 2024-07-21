<?php
/**
* Layout template file for ErrorHandler's pretty error output.
*/
?>
<!DOCTYPE html><?php echo $preface; ?>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="robots" content="noindex,nofollow"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
        <title>Flames | Error Handler</title>

        <link rel="icon" type="image/png" href="/.flames.png"/>
        <style><?php echo $stylesheet ?></style>
        <style><?php echo $prismCss ?></style>
        <style>
            .--flames-fullscreen-loading {
                background: #303030;
                position: fixed;
                width: 100%;
                height: 100%;
                top: 0;
                bottom: 0;
                left: 0;
                right: 0;
                opacity: 1;
                pointer-events: none;
                z-index: 999997;
                transition: opacity 0.5s;
            }

            .--flames-fullscreen-loading img {
                width: 100px;
                height: 100px;
                position: fixed;
                pointer-events: none;
                z-index: 999998;
                top: calc(50vh - 50px);
                left: calc(50vw - 50px);
                opacity: 0.75;
            }
        </style>
    </head>
    <body>
        <div class="--flames-fullscreen-loading">
            <img src="/.flames.png">
        </div>
        <div class="--flames-error-handler container">
            <div class="stack-container">
                <?php $tpl->render($panel_left_outer) ?>
                <?php $tpl->render($panel_details_outer) ?>
            </div>
        </div>
        <script data-manual><?php echo $prismJs ?></script>
        <script><?php echo $zepto ?></script>
        <script><?php echo $clipboard ?></script>
        <script><?php echo $javascript ?></script>
        <script>
            var flamesLoading = document.querySelector('.--flames-fullscreen-loading')
            window.setTimeout(function() {
                flamesLoading.style.opacity = '0';
            }, 1);
        </script>
    </body>
</html>
