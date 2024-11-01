<?php
/**
 * Outputs the WoowBox Header
 *
 * @since     1.0.0
 *
 * @package   WoowBox
 * @author    Sergey Pasyuk
 */
?>
<div id="woowbox-screen-meta-block"></div>
<div id="woowbox-header" class="woowbox-header">
	<h1 class="woowbox-logo" id="woowbox-logo">
		<img src="<?php echo $data['logo']; ?>" alt="<?php _e( 'WoowBox', 'woowbox' ); ?>" height="26" style="width:auto;"/>
		<?php
		if ( empty( $data['license']['key'] ) ) {
			_e( 'WoowBox', 'woowbox' );
		} else {
			_e( 'WoowBox <span>Premium</span>', 'woowbox' );
		}
		?>
	</h1>
</div>