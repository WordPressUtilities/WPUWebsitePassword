<?php
?><!DOCTYPE HTML>
<html lang="fr-FR">
    <head>
        <meta charset="UTF-8" />
        <title><?php echo apply_filters('wpuwebsitepassword_tpl_form__title', __('Website Protection','wpuwebsitepassword'), 'title') ?></title>
        <meta name="viewport" content="width=device-width" />
        <?php noindex() ?>
        <?php echo $wpuwebsitepassword_styles; ?>
<?php if(!isset($this->option['load_default_style']) || (isset($this->option['load_default_style']) && $this->option['load_default_style'] == '1')): ?>
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
<?php endif; ?>
    </head>
    <body class="wpuwebsitepassword-index">
        <?php do_action('wpuwebsitepassword_tpl_form__after_body_start') ?>
        <div class="wpuwebsitepassword-content">
            <?php do_action('wpuwebsitepassword_tpl_form__after_content_start') ?>
            <?php if(apply_filters('wpuwebsitepassword_tpl_form__display_title', true)): ?>
            <h1><?php echo apply_filters('wpuwebsitepassword_tpl_form__title', __('Website Protection','wpuwebsitepassword'), 'h1') ?></h1>
            <?php endif; ?>
            <?php
            if(isset($this->option['user_protection']) && $this->option['user_protection'] == '1'){
                echo wp_login_form(apply_filters('wpuwebsitepassword_tpl_login_form_args', array()));
            }
            else {
                include dirname( __FILE__ ) . '/form.php';
            }
            ?>
            <?php do_action('wpuwebsitepassword_tpl_form__before_content_end') ?>
        </div>
        <?php do_action('wpuwebsitepassword_tpl_form__before_body_end') ?>
    </body>
</html>
