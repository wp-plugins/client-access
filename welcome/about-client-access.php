<img src="<?php echo CLIENT_ACCESS_URL; ?>welcome/assets/images/screenshot-menu.png" alt="Settings submenu" title="Settings submenu"  />
<h2>Settings</h2>
<p><?php _e('All settings are available via the WordPress admin under Settings > Client Access. With the settings you can customize the following:', CLIENT_ACCESS_NAMESPACE ); ?></p>
<ul>
    <li><?php _e( 'Allow by Role – Only logged in users with the roles you\'ve selected can access the front-end.', CLIENT_ACCESS_NAMESPACE ); ?></li>
    <li><?php _e( 'Allow by IP – Only allowed IPs can access the front-end.', CLIENT_ACCESS_NAMESPACE ); ?></li>
    <li><?php _e( 'Allow by universal password – Only those you\'ve shared the universal password with can access the front-end.', CLIENT_ACCESS_NAMESPACE ); ?></li>
    <li><?php _e( 'Expire Password – Set the time at which the password will expire.', CLIENT_ACCESS_NAMESPACE ); ?></li>
</ul>
<hr />

<div class="larger">

    <div class="col1">
    <img src="<?php echo CLIENT_ACCESS_URL; ?>welcome/assets/images/screenshot-1.png" alt="General Option" title="General Option" />
    <h2><?php _e( 'Allow Administrators', CLIENT_ACCESS_NAMESPACE ); ?></h2>
    <p><?php _e('Enabling the General option will allow only WordPress administrators.', CLIENT_ACCESS_NAMESPACE ); ?></p>
    </div>

    <div class="col2">
    <img src="<?php echo CLIENT_ACCESS_URL; ?>welcome/assets/images/screenshot-2.png" alt="Allow by IP" title="Allow by IP" />
    <h2><?php _e( 'Allow by IP', CLIENT_ACCESS_NAMESPACE ); ?></h2>
    <p><?php _e('Enable this option, and enter the IP address for each allowed IP. Users that are not allowed will see a default message that you provide.', CLIENT_ACCESS_NAMESPACE ); ?></p>
    </div>

    <div class="col3">
    <img src="<?php echo CLIENT_ACCESS_URL; ?>welcome/assets/images/screenshot-3.png" alt="Allow by Universal Password" title="Allow by Universal Password" />
    <h2><?php _e( 'Allow by Universal Password', CLIENT_ACCESS_NAMESPACE ); ?></h2>
    <p><?php _e('Enable this option, and enter the universal password. This will only allow those that you have shared the password with.', CLIENT_ACCESS_NAMESPACE ); ?></p>
    </div>
</div>
