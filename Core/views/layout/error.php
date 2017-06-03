<!DOCTYPE html>
<html>
    <head>
        <meta charset=utf-8>
        <link rel="icon" href="" type="image/png" />
        <title><?php echo isset($params['title']) ? $params['title'] : \Core\Config::$config['GENERAL']['title'] ?></title>
        <?php $this->loadCss(); ?>
        <?php $this->loadJs(); ?>
        <style>
            body {
                background-color: #fafafa;
                color: #333;
                margin: 0;
                font-family: helvetica,verdana,arial,sans-serif;
            }

            header {
                background: #c52f24 none repeat scroll 0 0;
                color: #f0f0f0;
                padding: 0.5em 1.5em;
            }

            h1 {
                font-size: 2em;
                line-height: 1.1em;
                margin: 0.2em 0;
                font-weight: bold;
            }

            h2 {
                margin-top: 50px;
                color: #c52f24;
                line-height: 25px;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <?php echo $yeslp; ?>
    </body>
</html>
