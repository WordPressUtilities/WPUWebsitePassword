<?php
?><!DOCTYPE HTML>
<html lang="fr-FR">
    <head>
        <meta charset="UTF-8" />
        <title><?php echo apply_filters('wpuwebsitepassword_tpl_form__title', __('Website Protection','wpuwebsitepassword'), 'title') ?></title>
        <meta name="viewport" content="width=device-width" />
        <?php noindex() ?>
        <?php echo $wpuwebsitepassword_styles; ?>
<style>
* {
    margin: 0;
    padding: 0;
}

html,
body {
    padding: 10vh 10vw;
    text-align: center;
}

h1 {
    margin-bottom: 0.5em;
}

h1 img{
    max-width: 200px;
}

ul,
li {
    list-style-type: none;
}

li {
    margin-bottom: 1em;
}

button {
    padding: 0.5em 1em;
}
</style>
    </head>
    <body>
        <h1><?php echo apply_filters('wpuwebsitepassword_tpl_form__title', __('Website Protection','wpuwebsitepassword'), 'h1') ?></h1>
        <?php include dirname( __FILE__ ) . '/form.php'; ?>
    </body>
</html>
