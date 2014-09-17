<div class="wrap clearfix">
    <div id="icon-options-general" class="icon32"><br></div>
    <h2><?php _e('Settings', 'base-plugin-admin'); ?></h2>
    <h3><?php _e('Label', 'base-plugin-admin'); ?></h3>
    <form action="" method="post" enctype="multipart/form-data">
        <label><?php _e('Value', 'base-plugin-admin'); ?></label>
        <input type="text" name="base_plugin_value"
          id="value" value="<?php echo $base_plugin_value; ?>" />
        <?php wp_nonce_field('base-plugin-settings', 'nonce'); ?>
        <?php submit_button(); ?>
    </form>
</div>
