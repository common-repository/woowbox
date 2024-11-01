<?php
/**
 * Outputs the Skins panel.
 *
 * @since     1.0.0
 *
 * @var array $data
 *
 * @package   WoowBox
 * @author    Sergey Pasyuk
 */

// Get Skins models from database.
$skins_models  = get_option( 'woow_skins', array() );
$default_model = array( 'default' => (object) array() );

// Prepare variable for JSON Skins Schemas.
$skins_schemas = array();

$woowbox_skins = WoowBox_Skins::get_instance();

?>
<form method="post" id="woowbox" style="padding-top: 20px;">
	<h1><?php _e( 'Skins', 'woowbox' ); ?></h1>
	<div class="postbox woowbox-postbox">
		<div class="inside">

			<!-- Skins -->
			<div id="woowbox-skins">
				<div class="woow-skins">
					<?php
					// Iterate through the available skins, outputting them in a list.
					foreach ( $woowbox_skins->get_skins() as $skin_obj ) {
						$skins_schemas[ $skin_obj->slug ] = array(
							'model'  => isset( $skins_models[ $skin_obj->slug ] ) && is_array( $skins_models[ $skin_obj->slug ] ) ? array_merge( $default_model, $skins_models[ $skin_obj->slug ] ) : $default_model,
							'schema' => $skin_obj->settings(),
							'info'   => get_object_vars( $skin_obj ),
						);
						?>
						<div class="woow-skin woow_skin_<?php echo esc_attr( $skin_obj->slug ); ?>">
							<label for="woow_skin_<?php echo esc_attr( $skin_obj->slug ); ?>">
								<input type="radio" id="woow_skin_<?php echo esc_attr( $skin_obj->slug ); ?>" v-model="skin" name="_woow_skin[skin]" value="<?php echo esc_attr( $skin_obj->slug ); ?>"<?php checked( $data['default_skin'], $skin_obj->slug ); ?>>
								<img src="<?php echo $skin_obj->screenshots[0]; ?>" alt="<?php echo esc_attr( $skin_obj->name ); ?>">
								<span class="skin-info"><span class="skin-title"><?php echo $skin_obj->name; ?></span> v<?php echo $skin_obj->version; ?></span>
							</label>
						</div>
						<?php
					}
					?>
				</div>
			</div>

			<template v-if="skin" v-cloak>
				<!-- Top Header -->
				<div class="woow-top-buttons">
					<div class="woowbox-skin-preset-selector clearfix">
						<h2><?php printf( _x( '%s Settings', 'SKIN_NAME Settings', 'woowbox' ), '{{ skin_info.name }}' ); ?></h2>

						<div class="woowbox-skin-preset">
							<template v-if="!new_preset">
								<label>
									<span class="label"><?php _e( 'Choose Preset', 'woowbox' ); ?></span>
									<select name="woowskin_preset" id="woowskin_preset" class="form-control" v-model="preset">
										<option value="default"><?php _e( 'default', 'woowbox' ); ?></option>
										<option v-for="preset in presets" v-if="preset !== 'default'" :value="preset">{{ preset }}</option>
									</select>
								</label>
								<button type="button" class="button button-danger button-small" @click.prevent="deletePreset" :disabled="preset === 'default'"><?php _e( 'Delete', 'woowbox' ); ?></button>
								<button type="button" class="button button-secondary button-small" @click.prevent="new_preset = true"><?php _e( 'Add New', 'woowbox' ); ?></button>
							</template>
							<template v-else>
								<label>
									<span class="label"><?php _e( 'New Preset', 'woowbox' ); ?></span>
									<input type="text" class="form-control" name="woowskin_preset" id="woowskin_preset" v-model="new_preset_name" placeholder="<?php _e( 'Preset Name', 'woowbox' ); ?>">
								</label>
								<button type="button" class="button button-secondary button-small" @click.prevent="new_preset = false"><?php _e( 'Cancel', 'woowbox' ); ?></button>
							</template>
						</div>
					</div>
					<div id="activity" class="woowbox-action-buttons" :class="{'activity': activity}">
						<button type="button" class="button button-secondary reset-changes-action" @click.prevent="resetSkinSettingsChanges" :disabled="!isSettingsChanged"><?php _e( 'Reset Changes', 'woowbox' ); ?></button>
						<button type="button" class="button button-secondary reset-to-defaults-action" @click.prevent="resetSkinSettings" :disabled="isSettingsDefault"><?php _e( 'Reset to Defaults', 'woowbox' ); ?></button>
						<button type="button" class="button button-primary save-action" @click.prevent="saveSkinSettings" v-if="new_preset" :disabled="new_preset_name === ''"><?php printf( __( 'Save `%s` Preset', 'woowbox' ), '{{ new_preset_name || \'???\' }}' ); ?></button>
						<button type="button" class="button button-primary save-action" @click.prevent="saveSkinSettings" v-else><?php printf( __( 'Save `%s` Preset', 'woowbox' ), '{{ preset }}' ); ?></button>
					</div>
				</div>

				<!-- Settings -->
				<div id="woowbox-skin-config" class="woowbox-skin-config">
					<div class="woowbox-content-config">
						<!-- Skin Screenshot -->
						<div class="woowbox-skin-sreenshot" v-if="skin_info.screenshots && skin_info.screenshots[0]">
							<img :src="skin_info.screenshots[0]" :alt="skin_info.name">
						</div>
						<!-- Title and Help -->
						<div class="woowbox-intro">
							<div class="skin-description" v-html="skin_info.description"></div>
							<div class="skin-info" v-if="!premium" v-html="skin_info.info"></div>
							<p><?php esc_html_e( 'The settings below adjust the basic configuration options for the gallery.', 'woowbox' ); ?></p>
						</div>

						<nav class="woowbox-config-tabs-nav">
							<div v-for="(group, tab_id) in schema" :key="skin + '[' + preset + ']' + tab_id">
								<a :href="'#field-' + tab_id" :class="{'woowbox-active': activeTab === tab_id}" @click.prevent="switchTab(tab_id)">{{ group.label }}</a>
							</div>
						</nav>

						<div class="woowbox-config-tabs">
							<div class="vue-form-generator" v-if="schema != null">
								<fieldset v-for="(group, tab_id) in schema" :id="'field-' + tab_id" :class="{'woowbox-active': activeTab === tab_id}" :key="skin + '[' + preset + ']' + tab_id">
									<div class="form-group" :class="getFieldRowClasses(field)" v-for="(field, key) in group.fields" v-if="fieldVisible(field)" :key="skin + '[' + preset + '][field]' + key">
										<label v-if="fieldTypeHasLabel(field)" :for="key">{{ field.label }}
											<div class="help" v-if="field.help"><i class="icon"></i>
												<div class="helpText" v-html="field.help"></div>
											</div>
										</label>
										<div class="field-wrap">
											<div v-if="field.premium && !premium" class="woowbox-pro-feature">
												<h6><?php _e( 'This feature is available only in the WoowBox Premium', 'woowbox' ); ?></h6>
												<a class="button button-primary" href="https://woowgallery.com/woowbox/pricing/" target="_blank"><span class="dashicons dashicons-cart"></span> <?php _e( 'Get WoowBox Premium', 'woowbox' ); ?></a>
											</div>
											<component v-else :is="getFieldTagType(field)" :skin="skin" :preset="preset" :schema="field" :id="key" :key="skin + '[' + preset + ']' + key" :options="options" :disabled="fieldDisabled(field)"></component>
											<div class="hint" v-if="field.text" v-html="field.text"></div>
										</div>
									</div>
								</fieldset>
							</div>
						</div>

					</div>
				</div>
			</template>

		</div>
	</div>

	<?php
	wp_nonce_field( 'skin_settings_save', '_nonce_woow_skin_settings_save', false );
	?>

</form>
<script><?php echo 'var woow_skin = ' . wp_json_encode( $skins_schemas ) . ';'; ?></script>
