<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="UTF-8">
<meta name='robots' content='noindex,nofollow' />
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body class="client-access-body">
    <main>
        <?php do_action('client_access_main_content'); ?>
    </main>

    <footer class="client-access-footer">
        <ul>
            <?php do_action( 'client_access_footer_content' ); ?>
        </ul>
    </footer>

    <?php wp_footer(); ?>
</body>
</html>