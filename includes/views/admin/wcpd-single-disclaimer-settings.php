<?php
/**
 * Modal File.
 *
 * @package WCPD
 */

?>
<div class="wcpd-disclaimer-single-settings-wrapper">
	<div class="wcpd-form-control">
		<div class="wcpd-leftSide">
			<label class="wcpd-label"><?php echo esc_html__( 'Enable Disclaimer', 'wcpd' ); ?></label>
		</div>
		<div class="wcpd-rightSide">
			<?php
			foreach ( WCPD_Main::$toggle as $key => $value ) {
				echo '<input type="radio" class="inp-cbx" name="wcpd-disclaimer-toggle" id="wcpd-disclaimer-toggle-' . esc_attr( $key ) . '" value="' . esc_attr( $key ) . '" ' . checked( isset( $wcpd_disclaimer_toggle ) ? esc_attr( $wcpd_disclaimer_toggle ) : '', esc_attr( $key ), false ) . ' >';
				echo '<label class="wcpd-cbx" for="wcpd-disclaimer-toggle-' . esc_attr( $key ) . '">
						<span>
							<svg width="12px" height="9px" viewbox="0 0 12 9">
								<polyline points="1 5 4 8 11 1"></polyline>
							</svg>
						</span>
						<span>' . esc_attr( $value ) . '</span>
					</label>';
			}
			?>
		</div>
	</div>

	<div class="wcpd-form-control">
		<div class="wcpd-leftSide">
			<label class="wcpd-label"><?php echo esc_html__( 'Select Disclaimer Type', 'wcpd' ); ?></label>
		</div>
		<div class="wcpd-rightSide">
			<?php
			foreach ( WCPD_Main::$disclaimer_type as $key => $value ) {
				if ( 'specific' === $key || 'cart' === $key ) {
					$disabled = 'disabled="disabled"';
					$key      = '';
				} else {
					$disabled = '';
				}

				echo '<input type="radio" class="inp-cbx" name="wcpd-disclaimer-type" id="wcpd-disclaimer-type-' . esc_attr( $key ) . '" value="' . esc_attr( $key ) . '" ' . checked( esc_attr( $wcpd_disclaimer_type ), esc_attr( $key ), false ) . ' ' . esc_attr( $disabled ) . ' >';

				echo '<label class="wcpd-cbx" for="wcpd-disclaimer-type-' . esc_attr( $key ) . '">
						<span>
							<svg width="12px" height="9px" viewbox="0 0 12 9">
								<polyline points="1 5 4 8 11 1"></polyline>
							</svg>
						</span>
						<span>' . esc_attr( $value ) . '</span>
					</label>';
			}
			?>
			<span class="wcpd-tool-disp">
				<?php echo esc_html__( 'Select type of disclaimer. Default is specific.', 'wcpd' ); ?>
				<span class="wcpd-tooltip">
					<a href="javascript:void(0);">?</a>
					<ul class="wcpd-tooltiptext">
						<li><strong>Specific (PRO):</strong> Enabling this option to make it a Specific (Product /Category/ User) Disclaimer.</li>
						<li><strong>Global:</strong> Enabling this option to make it a (Default) General Disclaimer.</li>
						<li><strong>Sitewide:</strong> Enabling this option to make it a Sitewide Disclaimer.</li>
						<li><strong>Cart/Checkout (PRO):</strong> Enable this option to display a disclaimer on the cart & checkout page.</li>
					</ul>
				</span>
			</span>
		</div>
	</div>

	<div class="popup_wrapper">

		<br><h3><?php echo esc_html__( 'Age Verification', 'wcpd' ); ?></h3><br>

		<div class="wcpd-form-control">
			<div class="wcpd-leftSide">
				<label class="wcpd-label"><?php echo esc_html__( 'Enable Age', 'wcpd' ); ?></label>
			</div>
			<div class="wcpd-rightSide">
				<?php
				foreach ( WCPD_Main::$toggle as $key => $value ) {
					echo '<input type="radio" class="inp-cbx" name="wcpd-age-verification[toggle]" id="wcpd-age-verification-' . esc_attr( $key ) . '" value="' . esc_attr( $key ) . '" ' . checked( esc_attr( $wcpd_age_toggle ? $wcpd_age_toggle : '' ), esc_attr( $key ), false ) . ' >';
					echo '<label class="wcpd-cbx" for="wcpd-age-verification-' . esc_attr( $key ) . '">
							<span>
								<svg width="12px" height="9px" viewbox="0 0 12 9">
									<polyline points="1 5 4 8 11 1"></polyline>
								</svg>
							</span>
							<span>' . esc_attr( $value ) . '</span>
						</label>';
				}
				?>
				<span class="wcpd-tool-disp"><?php echo esc_html__( 'Default is disabled.', 'wcpd' ); ?></span>
			</div>
		</div>

		<div class="wcpd-form-control">
			<div class="wcpd-leftSide">
				<label class="wcpd-label"><?php echo esc_html__( 'Enter age in years', 'wcpd' ); ?></label>
			</div>
			<div class="wcpd-rightSide">
				<input type="text" id="wcpd-age-verification" name="wcpd-age-verification[values]" value="<?php echo esc_attr( $wcpd_age_verification_txt ); ?>">
				<span class="wcpd-tool-disp"><?php echo esc_html__( 'Users below this age will not be able to access.', 'wcpd' ); ?></span>
			</div>
		</div>

		<br><h3><?php echo esc_html__( 'Terms & Condition', 'wcpd' ); ?></h3><br>

		<div class="wcpd-form-control">
			<div class="wcpd-leftSide">
				<label class="wcpd-label"><?php echo esc_html__( 'Enable Terms & condition', 'wcpd' ); ?></label>
			</div>
			<div class="wcpd-rightSide">
				<?php
				foreach ( WCPD_Main::$toggle as $key => $value ) {
					echo '<input type="radio" class="inp-cbx" name="wcpd-terms-condition[toggle]" id="wcpd-terms-condition-' . esc_attr( $key ) . '" value="' . esc_attr( $key ) . '" ' . ( isset( $wcpd_terms_condition_toggle ) ? checked( esc_attr( $wcpd_terms_condition_toggle ), esc_attr( $key ), false ) : '' ) . ' />';
					echo '<label class="wcpd-cbx" for="wcpd-terms-condition-' . esc_attr( $key ) . '">
							<span>
								<svg width="12px" height="9px" viewbox="0 0 12 9">
									<polyline points="1 5 4 8 11 1"></polyline>
								</svg>
							</span>
							<span>' . esc_attr( $value ) . '</span>
						</label>';
				}
				?>
				<span class="wcpd-tool-disp"><?php echo esc_html__( 'Default is disabled.', 'wcpd' ); ?></span>
			</div>
		</div>

		<div class="wcpd-form-control">
			<div class="wcpd-leftSide">
				<label class="wcpd-label"><?php echo esc_html__( 'Terms & Conditions Message', 'wcpd' ); ?></label>
			</div>
			<div class="wcpd-rightSide">
				<input type="text" id="wcpd-terms-condition" name="wcpd-terms-condition[values]" value="<?php echo esc_attr( $wcpd_terms_condition_txt ); ?>">
				<span class="wcpd-tool-disp"><?php echo esc_html__( 'Check this if you want to enable terms & conditions checkbox on popup.', 'wcpd' ); ?></span>
			</div>
		</div>
	</div><!--popup wrapper-->

	<br><h3><?php echo esc_html__( 'Customization', 'wcpd' ); ?></h3><br>

	<div class="popup_wrapper">
		<div class="wcpd-form-control">
			<div class="wcpd-leftSide">
				<label class="wcpd-label"><?php echo esc_html__( 'Reject Button Text   ', 'wcpd' ); ?></label>
			</div>
			<div class="wcpd-righe">
				<input type="text" id="wcpd-reject-txt" name="wcpd-reject-txt" value="<?php echo esc_attr( isset($wcpd_reject_txt) ? $wcpd_reject_txt : '' ); ?>">
				<span class="wcpd-tool-disp"><?php echo esc_html__( 'Reject button text for product disclaimer popup. Default is Reject', 'wcpd' ); ?></span>
			</div>
		</div>

		<div class="wcpd-form-control">
			<div class="wcpd-leftSide">
				<label class="wcpd-label"><?php echo esc_html__( 'Reject URL   ', 'wcpd' ); ?></label>
			</div>
			<div class="wcpd-rightSide">
				<input type="text" id="wcpd-reject-url" name="wcpd-reject-url" value="<?php echo esc_attr( isset($wcpd_reject_url) ? $wcpd_reject_url : '' ); ?>">
				<span class="wcpd-tool-disp"><?php echo esc_html__( 'Customer will be redirected to this URL if customer does not accept product disclaimer.', 'wcpd' ); ?></span>
			</div>
		</div>

		<div class="wcpd-form-control">
			<div class="wcpd-leftSide">
				<label class="wcpd-label"><?php echo esc_html__( 'Accept Button Text   ', 'wcpd' ); ?></label>
			</div>
			<div class="wcpd-rightSide">
				<input type="text" id="wcpd-accept-txt" name="wcpd-accept-txt" value="<?php echo esc_attr( isset($wcpd_accept_txt) ? $wcpd_accept_txt : '' ); ?>">
				<span class="wcpd-tool-disp"><?php echo esc_html__( 'Accept button text for product disclaimer popup. Default is Accept', 'wcpd' ); ?></span>
			</div>
		</div>

		<div class="wcpd-form-control">
			<div class="wcpd-leftSide">
				<label class="wcpd-label"><?php esc_html_e( 'Popup style (PRO)', 'wcpd' ); ?></label>
			</div>
			<div class="wcpd-rightSide">
				<select name="wcpd-popup-style" id="wcpd-popup-style" disabled>            
				<?php
				foreach ( WCPD_Main::$popup_style as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '" ' . selected( esc_attr( isset($wcpd_popup_style) ?  $wcpd_popup_style : '' ), esc_attr( $key ), false ) . ' >' . esc_attr( $value ) . '</option>';
				}
				?>
				</select>
				<span class="wcpd-tool-disp"><?php echo esc_html__( 'Select style of disclaimer popup.', 'wcpd' ); ?></span>
			</div>
		</div>

		<div class="wcpd-form-control">
			<div class="wcpd-leftSide">
				<label class="wcpd-label"><?php esc_html_e( 'Popup Background Color', 'wcpd' ); ?></label>
			</div>
			<div class="wcpd-rightSide">
				<input type="text" class="jscolor" id="wcpd-popup-bg-color" name="wcpd-popup-bg-color" value="<?php echo esc_attr( isset($wcpd_popup_bg_color) ?  $wcpd_popup_bg_color :  '' ); ?>">			
			</div>
		</div>

		<div class="wcpd-form-control">
			<div class="wcpd-leftSide">
				<label class="wcpd-label"><?php esc_html_e( 'Popup Text Color', 'wcpd' ); ?></label>
			</div>
			<div class="wcpd-rightSide">
				<input type="text" class="jscolor" id="wcpd-popup-txt-color" name="wcpd-popup-txt-color" value="<?php echo esc_attr( isset($wcpd_popup_txt_color) ? $wcpd_popup_txt_color : '' ); ?>">			
			</div>
		</div>

		<div class="wcpd-form-control">
			<div class="wcpd-leftSide">
				<label class="wcpd-label"><?php esc_html_e( 'Button Background Color', 'wcpd' ); ?></label>
			</div>
			<div class="wcpd-rightSide">
				<input type="text" class="jscolor" id="wcpd-btn-bg-color" name="wcpd-btn-bg-color" value="<?php echo esc_attr( isset($wcpd_btn_bg_color) ? $wcpd_btn_bg_color : '' ); ?>">			
			</div>
		</div>

		<div class="wcpd-form-control">
			<div class="wcpd-leftSide">
				<label class="wcpd-label"><?php esc_html_e( 'Button Text Color', 'wcpd' ); ?></label>
			</div>
			<div class="wcpd-rightSide">
				<input type="text" class="jscolor" id="wcpd-btn-txt-color" name="wcpd-btn-txt-color" value="<?php echo esc_attr( isset($wcpd_btn_txt_color) ? $wcpd_btn_txt_color : '' ); ?>">
			</div>
		</div>
	
	</div><!--popup wrapper-->
</div>
