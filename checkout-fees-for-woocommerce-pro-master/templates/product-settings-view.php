<div class="alg_checkout_fees">
	<ul class="tabs">
		<li class="labels">
			<?php 
				$i = 0;
			foreach ( $available_gateways as $gateway_key => $gateway ) {
				$i++;
				$gateway_title = ( '' === $gateway->title ? $gateway_key : $gateway->title );
				$label_class   = ( 1 === $i ? 'alg-clicked' : '' );
				?>
					<label for="tab-<?php echo esc_attr( $gateway_key ); ?>" id="label-<?php echo esc_attr( $gateway_key ); ?>" class="<?php echo esc_attr( $label_class ); ?>"><?php echo esc_attr( $gateway_title ); ?></label>
					<?php
			}
			?>
		</li>

	<?php
		// Tab Content.
		$i = 0;
		
	foreach ( $available_gateways as $gateway_key => $gateway ) {
		$i++;
		$gateway_title = ( '' === $gateway->title ) ? $gateway_key : $gateway->title;
		?>
			<li>
				<input type="radio" id="tab-<?php echo esc_attr( $gateway_key ); ?>" name="tabs" <?php echo checked( $i, 1, false ); ?>>
				<div class="tab-content" id="tab-content-<?php echo esc_attr( $gateway_key ); ?>">
				<?php if ( 1 !== $i && ! apply_filters( 'alg_wc_checkout_fees_option', false, 'per_product' ) ) : ?>
					<div style="padding: 20px; background-color: #d6d5d3; margin-bottom: 15px;">
						<?php echo wp_kses_post( __( 'In free version only <strong>Direct Bank Transfer (BACS)</strong> fees are available on per product basis.', 'checkout-fees-for-woocommerce' ) . ' ' . $pro_version_link ); ?>
					</div>
					<?php endif; ?>
					<table>
					<?php 
					foreach ( $meta_box_options as $option ) { 
						if ( 'title' === $option['type'] ) {
							?>
									<tr>
										<th style="text-align:right;padding:10px;" colspan="2"><span style="font-size:larger;font-weight:bold;"><?php echo esc_attr( $option['title'] ); ?></span></th>
									</tr>
							<?php continue; ?>
							<?php } ?>

							<?php 
							if ( ! isset( $option['custom_atts'] ) ) {
								$option['custom_atts'] = '';
							}
							$option['custom_atts'] .= ( 'bacs' !== $gateway_key && ! apply_filters( 'alg_wc_checkout_fees_option', false, 'per_product' ) ? ' disabled="disabled"' : '' );
							$option_name            = $option['name'] . '_' . $gateway_key;
							$option_value           = get_post_meta( $current_post_id, '_' . $option_name, true );
							$option_title           = ( '' === $option['title'] ) ? '<span style="font-size:large;font-weight:bold;">' . $gateway_title . '</span>' : $option['title'];
							$input_ending           = ' id="' . $option_name . '" name="' . $option_name . '" value="' . $option_value . '"' . $option['custom_atts'] . '>';
							$select_options         = '';
						
							$tooltip = ( isset( $option['tooltip'] ) ? wc_help_tip( $option['tooltip'], true ) : '' );

							$display = 'display:none';

							?>
									<tr>
										<th style="text-align:right;padding:10px;">
											<?php echo wp_kses_post( $tooltip . $option_title ); ?>
										</th>
										<td>



									<?php 
									
									switch ( $option['type'] ) {
										case 'text':
											?>
													<input style="min-width:300px;" class="short" type="<?php echo esc_attr( $option['type'] ); ?>" id="<?php echo esc_attr( $option_name ); ?>" name="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $option_value ); ?>" <?php echo esc_attr( $option['custom_atts'] ); ?>>
											<?php
											break;
										case 'number':
											?>
												<input style="min-width:300px;" class="short" type="<?php echo esc_attr( $option['type'] ); ?>" id="<?php echo esc_attr( $option_name ); ?>" name="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $option_value ); ?>" <?php echo esc_attr( $option['custom_atts'] ); ?>>
											<?php
											break;

										case 'select':
											$ro = ( 'bacs' !== $gateway_key && ! apply_filters( 'alg_wc_checkout_fees_option', false, 'per_product' ) ) ? ' disabled="disabled"' : '';
											?>
												<select style="min-width:300px;" name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $option_name ); ?>" style="" class="" <?php echo esc_attr( $ro ); ?>>
												<?php 
												//echo $select_options; 
												if ( isset( $option['options'] ) ) {
													foreach ( $option['options'] as $select_option_key => $select_option_value ) {
														?>

																<option value="<?php echo esc_attr( $select_option_key ); ?>" <?php echo selected( $option_value, $select_option_key, false ); ?>><?php echo esc_html( $select_option_value ); ?></option>

															<?php
															
													}
												}
												?>
												
												</select>
											<?php
											break;


									} 
									?>

										</td>
									</tr>
							<?php } ?>
					</table>
				</div>
			</li>
		<?php } ?>
	</ul>
</div>
