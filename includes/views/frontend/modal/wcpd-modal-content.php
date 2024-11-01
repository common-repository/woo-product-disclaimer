<?php
/**
 * Modal Content File.
 *
 * @package WCPD
 */

defined( 'ABSPATH' ) || exit;
$post_disclaimer = get_post( $data->wcpd_disclaimer_id );
?>
<div class="wcpd-modal <?php echo esc_attr( $data->wcpd_popup_style ); ?>">
	<div class="wcpd-modal-loader"></div>
	<?php
	if ( 'sitewide' !== $data->wcpd_disclaimer_type ) {
		?>
		<div class="wcpd-modal-header">
			<a href="#" class="close-wcpd-rest-modal" data-id="<?php echo esc_attr( $product_id ); ?>">&#10005;</a>
		</div>
		<?php
	}
	?>
	
	<div  class="wcpd-modal-content">			
		<div class="wcpd-modal-content-head">
			<div class="top">
				<h3 class="wcpd-prod-title"><?php echo esc_html( get_the_title( $data->wcpd_disclaimer_id ) ); ?></h3>				
			</div>			
		</div>
		<?php
		$wcpd_logo = get_the_post_thumbnail_url( $post_disclaimer, 'post-thumbnail' );
		if ( ! empty( $wcpd_logo ) ) {
			?>
			<div class="wcpd-modal-logo">
				<img src="<?php echo esc_url( $wcpd_logo ); ?>" alt="<?php echo esc_html( get_the_title( $data->wcpd_disclaimer_id ) ); ?>" title="<?php echo esc_html( get_the_title( $data->wcpd_disclaimer_id ) ); ?>">
			</div>
			<?php
		}
		?>
		<div class="wcpd-modal-content-body">			
			<input type="hidden" id="wcpd_product_id" name="wcpd_product_id" value="<?php echo esc_attr( $product_id ); ?>">
			<input type="hidden" id="wcpd_disclaimer_id" name="wcpd_disclaimer_id" value="<?php echo esc_attr( $data->wcpd_disclaimer_id ); ?>">
			<input type="hidden" id="wcpd_disclaimer_type" name="wcpd_disclaimer_type" value="<?php echo esc_attr( $data->wcpd_disclaimer_type ); ?>">
			<input type="hidden" id="wcpd_product_quantity" name="wcpd_product_quantity" value="<?php echo esc_attr( $quantity ); ?>">
			<?php
			echo wp_kses_post( $post_disclaimer->post_content );

			if ( 'enabled' === $data->wcpd_age_verification['toggle'] ) {
				if ( isset( $data->wcpd_age_verification['values'] ) && ! empty( trim( $data->wcpd_age_verification['values'] ) ) ) {
					echo '<div class="wcpd_age_verification_wrapper">';
					echo '<p class="wcpd_p_format">' . esc_html__( 'Please enter your age:', 'wcpd' ) . '</p>';
					echo '<input type="number" name="wcpd_age_verification" id="wcpd_age_verification" min="1" data-min="' . esc_attr( $data->wcpd_age_verification['values'] ) . '" placeholder="' . esc_attr__( 'Enter your age', 'wcpd' ) . '" step="1" oninput="this.value = this.value.slice(0, this.maxLength || this.value.length);" maxlength="3">';

					echo '</div>';
				}
			}

			if ( 'enabled' === $data->wcpd_terms_condition['toggle'] ) {
				if ( isset( $data->wcpd_terms_condition['values'] ) && ! empty( trim( $data->wcpd_terms_condition['values'] ) ) ) {

					echo '<div class="wcpd_terms_condition_wrapper">';
					echo '<input type="checkbox" class="inp-cbx" name="wcpd_terms_condition" id="wcpd_terms_condition" value="yes" >';
					echo '<label class="wcpd-cbx" for="wcpd_terms_condition">
							<span>
								<svg width="12px" height="9px" viewbox="0 0 12 9">
									<polyline points="1 5 4 8 11 1"></polyline>
								</svg>
							</span>
						</label>';
					echo '<label class="wcpd-content" for="wcpd_terms_condition">
							<span>' . wp_kses_post( $data->wcpd_terms_condition['values'] ) . '</span>
						</label>';
					echo '</div>';

				}
			}
			?>
		</div>

		<div class="wcpd-modal-content-footer">
			<?php
			echo '<a href="#" class="wcpd_action wcpd_accept">' . esc_attr( $data->wcpd_accept_txt ) . '</a>';

			if ( isset( $data->wcpd_reject_url ) && ! empty( trim( $data->wcpd_reject_url ) ) ) {
				echo '<a href="' . esc_url( $data->wcpd_reject_url ) . '" class="wcpd_action wcpd_reject">' . esc_attr( $data->wcpd_reject_txt ) . '</a>';
			}
			?>
		</div>
	</div>
</div>
