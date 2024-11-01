<?php
/**
 * WCPD Global Setting.
 *
 * @package WCPD
 */

?>

<div class="wcpd-form-control">
	<div class="wcpd-leftSide">
		<label class="wcpd-label"><?php echo esc_html__( 'Cookie Activation', 'wcpd' ); ?></label>
	</div>
	<div class="wcpd-rightSide">
		<?php
		foreach ( WCPD_Main::$toggle as $key => $value ) {
			echo '<input type="radio" class="inp-cbx" name="wcpd-global-cookie-activation" id="wcpd-global-cookie-activation-' . esc_attr( $key ) . '" value="' . esc_attr( $key ) . '" ' . checked( esc_attr( $wcpd_global_cookie_activation ), esc_attr( $key ), false ) . ' >';
			echo '<label class="wcpd-cbx" for="wcpd-global-cookie-activation-' . esc_attr( $key ) . '">
					<span>
						<svg width="12px" height="9px" viewbox="0 0 12 9">
							<polyline points="1 5 4 8 11 1"></polyline>
						</svg>
					</span>
					<span>' . esc_attr( $value ) . '</span>
				</label>';
		}
		?>
		<span class="wcpd-tool-disp"><?php echo esc_html__( 'Show one time disclaimer message when product add to cart button is clicked until next cookie expires. Default is disabled.', 'wcpd' ); ?></span>
	</div>
</div>

<div class="wcpd-form-control">
	<div class="wcpd-leftSide">
		<label class="wcpd-label" for="wcpd-global-cookie-duration"><?php esc_html_e( 'Cookie Duration', 'wcpd' ); ?></label>
	</div>
	<div class="wcpd-rightSide">
		<select name="wcpd-global-cookie-duration" id="wcpd-global-cookie-duration">            
		<?php
		foreach ( WCPD_Main::$duration as $key => $value ) {
			echo '<option value="' . esc_attr( $key ) . '" ' . selected( esc_attr( $wcpd_global_cookie_duration ), esc_attr( $key ), false ) . ' >' . esc_attr( $value ) . '</option>';
		}
		?>
		</select>
		<span class="wcpd-tool-disp"><?php echo esc_html__( 'How much time disclaimer message should not appear when product add to cart button is clicked. Default is session', 'wcpd' ); ?></span>
	</div>
</div>

<div class="wcpd-form-control">
	<div class="wcpd-leftSide">
		<label class="wcpd-label"><?php echo esc_html__( 'Log Disclaimer Activity (PRO)', 'wcpd' ); ?></label>
	</div>
	<div class="wcpd-rightSide">
		<?php
		foreach ( WCPD_Main::$toggle as $key => $value ) {
			echo '<input type="radio" class="inp-cbx" name="wcpd-global-log-activation" disabled id="wcpd-global-log-activation-' . esc_attr( $key ) . '" >';
			echo '<label class="wcpd-cbx" for="wcpd-global-log-activation-">
					<span>
						<svg width="12px" height="9px" viewbox="0 0 12 9">
							<polyline points="1 5 4 8 11 1"></polyline>
						</svg>
					</span>
					<span>' . esc_attr( $value ) . '</span>
				</label>';
		}
		?>
		<span class="wcpd-tool-disp"><?php echo esc_html__( 'Check this if you want to log the disclaimer activity (accept or reject) on your products. Default is disabled.', 'wcpd' ); ?></span>
	</div>
</div>
