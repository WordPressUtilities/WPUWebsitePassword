<form class="wpuwebsitepassword-form" action="" method="post">
    <?php wp_nonce_field('wpuwebsitepassword_form', 'wpuwebsitepassword_nonce');?>
    <ul class="wpuwebsitepassword-form__items">
        <li class="wpuwebsitepassword-form__password">
            <label for="password_field"><?php echo __('Password', 'wpuwebsitepassword'); ?></label>
            <input id="password_field" type="password" name="password" value="" />
        </li>
        <li class="wpuwebsitepassword-form__submit">
            <button class="cssc-button" type="submit"><?php echo __('Send', 'wpuwebsitepassword'); ?></button>
        </li>
    </ul>
</form>
