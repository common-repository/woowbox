<?php
/**
 * Outputs the Settings panel.
 *
 * @since     1.0.0
 *
 * @var array $data
 *
 * @package   WoowBox
 * @author    Sergey Pasyuk
 */

// Get Skins models from database.
$skins_models  = get_option( 'woow_skins', [] );
$woowbox_skins = WoowBox_Skins::get_instance();

?>
<form method="post" style="padding-top: 20px;">
	<h1><?php _e( 'General Settings', 'woowbox' ); ?></h1>
	<div class="postbox">
		<div class="inside">
			<div class="form-group field-input license-field <?php echo $data['license']['key'] ? 'license-active' : 'license-inactive'; ?>">
				<label for="woowbox-license"><?php _e( 'License Key', 'woowbox' ); ?></label>
				<div class="field-wrap with-button">
					<div class="wrapper">
						<input name="_woowbox[license]" type="text" class="form-control" id="woowbox-license" value="<?php echo esc_attr( $data['license']['key'] ); ?>" <?php echo $data['license']['key'] ? 'readonly' : ''; ?>/>
						<input type="hidden" id="woowbox-license-plugin" value="<?php echo esc_attr( $data['license']['plugin'] ); ?>"/>
					</div>
					<?php
					if ( 'WoowBox' === $data['license']['plugin'] ) {
						?>
						<div class="field-button">
							<button type="button" class="woowbox-license-action-button button button-primary" data-action="activate"><?php _e( 'Activate', 'woowbox' ); ?></button>
							<button type="button" class="woowbox-license-action-button button button-danger" data-action="deactivate"><?php _e( 'Deactivate', 'woowbox' ); ?></button>
						</div>
						<?php
					} else {
						?>
						<div class="field-button">
							<button type="button" class="button button-default" disabled><?php echo esc_html( $data['license']['plugin'] ); ?></button>
						</div>
						<?php
					}
					?>
				</div>
			</div>
			<hr style="margin-bottom:20px"/>
			<div class="form-group field-input">
				<label for="woowbox-default-skin"><?php _e( 'Default skin for [gallery]', 'woowbox' ); ?></label>
				<div class="field-wrap">
					<div class="wrapper">
						<select name="_woowbox[default_skin]" id="woowbox-default-skin" class="form-control">
							<option value=""<?php selected( $data['default_skin'], '' ); ?>><?php _e( 'None', 'woowbox' ); ?></option>
							<?php
							// Iterate through the available skins, outputting them in a list.
							foreach ( $woowbox_skins->get_skins() as $skin_obj ) {
								$value = $skin_obj->slug;
								?>
								<option value="<?php echo esc_attr( $value ); ?>"<?php selected( $data['default_skin'], $value ); ?>><?php echo esc_html( $skin_obj->name ); ?></option>
								<?php
								if ( ! empty( $skins_models[ $skin_obj->slug ] ) ) {
									foreach ( $skins_models[ $skin_obj->slug ] as $preset_name => $preset_data ) {
										if ( 'default' === $preset_name ) {
											continue;
										}
										$value = $skin_obj->slug . ': ' . $preset_name;
										?>
										<option value="<?php echo esc_attr( $value ); ?>"<?php selected( $data['default_skin'], $value ); ?>><?php echo esc_html( $skin_obj->name . ': ' . $preset_name ); ?></option>
										<?php
									}
								}
							}
							?>
						</select>
					</div>
					<div class="hint"><?php _e( 'Replace default WordPress <code>[gallery]</code> layout with chosen skin.', 'woowbox' ); ?></div>
				</div>
			</div>
			<div class="form-group field-textarea">
				<label for="woowbox-custom-css"><?php _e( 'Custom CSS', 'woowbox' ); ?></label>
				<div class="field-wrap">
					<div class="wrapper">
						<textarea name="_woowbox[custom_css]" id="woowbox-custom-css" class="form-control" rows="10" cols="60"><?php echo esc_textarea( stripslashes( $data['custom_css'] ) ); ?></textarea>
					</div>
				</div>
			</div>
			<div class="alignright">
				<button type="submit" name="woowbox-settings-reset" class="button button-secondary" data-woowbox-confirm="<?php esc_attr_e( 'This will reset plugin\'s settings and delete all skins presets.' ); ?>"><?php _e( 'Reset Plugin', 'woowbox' ); ?></button>
				&nbsp;
				<button type="submit" name="woowbox-settings-submit" class="button button-primary"><?php _e( 'Save', 'woowbox' ); ?></button>
			</div>
		</div>
	</div>
	<?php
	wp_nonce_field( 'settings_save', '_nonce_woowbox_settings_save', false );
	?>
</form>
