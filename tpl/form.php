<!DOCTYPE HTML>
<html lang="fr-FR">
    <head>
        <meta charset="UTF-8" />
        <title><?php echo __('Website Protection','wpuwebsitepassword') ?></title>
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
        <h1><?php echo apply_filters('wpuwebsitepassword_tpl_form__title', __('Website Protection','wpuwebsitepassword')) ?></h1>
        <form action="#" method="post">
            <?php wp_nonce_field('wpuwebsitepassword_form', 'wpuwebsitepassword_nonce'); ?>
            <ul>
                <li>
                    <label for="password_field"><?php echo __( 'Password', 'wpuwebsitepassword' ); ?></label>
                    <input id="password_field" type="password" name="password" value="" />
                </li>
                <li>
                    <button class="cssc-button" type="submit"><?php echo __( 'Send', 'wpuwebsitepassword' ); ?></button>
                </li>
            </ul>
        </form>
    </body>
</html>
