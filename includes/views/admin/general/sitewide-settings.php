<?php
/**
 * WCPD Sitewide Settings.
 *
 * @package WCPD
 */

?>

<div class="wcpd-form-control">
	<div class="wcpd-leftSide">
		<label class="wcpd-label"><?php echo esc_html__( 'Site Cookie Activation', 'wcpd' ); ?></label>
	</div>
	<div class="wcpd-rightSide">
		<?php
		foreach ( WCPD_Main::$toggle as $key => $value ) {
			echo '<input type="radio" class="inp-cbx" name="wcpd-sitewide-cookie-activation" id="wcpd-sitewide-cookie-activation-' . esc_attr( $key ) . '" value="' . esc_attr( $key ) . '" ' . checked( esc_attr( $wcpd_sitewide_cookie_activation ), esc_attr( $key ), false ) . ' >';
			echo '<label class="wcpd-cbx" for="wcpd-sitewide-cookie-activation-' . esc_attr( $key ) . '">
					<span>
						<svg width="12px" height="9px" viewbox="0 0 12 9">
							<polyline points="1 5 4 8 11 1"></polyline>
						</svg>
					</span>
					<span>' . esc_attr( $value ) . '</span>
				</label>';
		}
		?>
		<span class="wcpd-tool-disp"><?php echo esc_html__( 'Show one time disclaimer message when site is loaded until next cookie expires. Default is disabled.', 'wcpd' ); ?></span>
	</div>
</div>

<div class="wcpd-form-control">
	<div class="wcpd-leftSide">
		<label class="wcpd-label" for="wcpd-sitewide-cookie-duration"><?php esc_html_e( 'Site Cookie Duration', 'wcpd' ); ?></label>
	</div>
	<div class="wcpd-rightSide">
		<select name="wcpd-sitewide-cookie-duration" id="wcpd-sitewide-cookie-duration">            
		<?php
		foreach ( WCPD_Main::$duration as $key => $value ) {
			echo '<option value="' . esc_attr( $key ) . '" ' . selected( esc_attr( $wcpd_sitewide_cookie_duration ), esc_attr( $key ), false ) . ' >' . esc_attr( $value ) . '</option>';
		}
		?>
		</select>
		<span class="wcpd-tool-disp"><?php echo esc_html__( 'How much time disclaimer message should not appear when site is loaded. Default is session', 'wcpd' ); ?></span>
	</div>
</div>
