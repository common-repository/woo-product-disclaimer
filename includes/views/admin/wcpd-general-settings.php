<?php
/**
 * WCPD General Settings.
 *
 * @package WCPD
 */

?>
<!-- Create a header in the default WordPress 'wrap' container -->
<div class="wrap">
	  
	<h2><span style="margin-top: 5px" class="dashicons dashicons-yes-alt"></span> <?php echo esc_html__( 'WCPD General Settings', 'wcpd' ); ?></h2>


	<?php
		// prinitng errors for settings.
		settings_errors();
		// initializing variable to check which tab is active.
	$tab_from_input = filter_input( INPUT_GET, 'tab' );
	$active_tab     = isset( $tab_from_input ) ? sanitize_text_field( wp_unslash( $tab_from_input ) ) : 'general';
	?>
	   
	   
	<div class="nav-tab-wrapper">
		<a href="?post_type=wcpd&page=wcpd-settings&tab=general" class="nav-tab <?php echo ( 'general' === $active_tab ) ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Global', 'wcpd' ); ?></a>
		<a href="?post_type=wcpd&page=wcpd-settings&tab=sitewide" class="nav-tab <?php echo ( 'sitewide' === $active_tab ) ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Sitewide', 'wcpd' ); ?></a>
	</div>
	   
	<form method="post" action="options.php" enctype="multipart/form-data"> <!-- action always equal to options.php-->        
		<div class="rms-content-body">

		<?php

		if ( 'general' === $active_tab ) {

			settings_fields( 'wcpd-settings-general' );

			echo '<h3>General (Default)</h3>';

			$wcpd_global_cookie_activation = ! empty( get_option( 'wcpd-global-cookie-activation' ) ) ? get_option( 'wcpd-global-cookie-activation' ) : 'disabled';
			$wcpd_global_cookie_duration   = ! empty( get_option( 'wcpd-global-cookie-duration' ) ) ? get_option( 'wcpd-global-cookie-duration' ) : 'session';

			require_once WCPD_PATH . '/includes/views/admin/general/global-settings.php';

		} elseif ( 'sitewide' === $active_tab ) {

			settings_fields( 'wcpd-settings-sitewide' );

			echo '<h3>Sitewide</h3>';

			$wcpd_sitewide_cookie_activation = ! empty( get_option( 'wcpd-sitewide-cookie-activation' ) ) ? get_option( 'wcpd-sitewide-cookie-activation' ) : 'disabled';
			$wcpd_sitewide_cookie_duration   = ! empty( get_option( 'wcpd-sitewide-cookie-duration' ) ) ? get_option( 'wcpd-sitewide-cookie-duration' ) : 'session';

			require_once WCPD_PATH . '/includes/views/admin/general/sitewide-settings.php';

		}

		submit_button();
		?>
		</div> 
	</form>
</div><!-- /.wrap -->
