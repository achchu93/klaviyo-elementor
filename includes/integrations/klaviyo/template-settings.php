<?php
/**
 * Template for override the Klavito settings
 * 
 * @var array $klaviyo_settings
 * @var array $response
 * @since 1.1.0
 */
?>

<p>Insert your Klaviyo API key below to connect. You can find them on your Klaviyo <a href="https://www.klaviyo.com/account#api-keys-tab">account page</a>.</p>
<p>Insert your Klaviyo List ID to add a newsletter checkbox on the checkout page. <a href="https://help.klaviyo.com/hc/en-us/articles/115005078647-Find-a-List-ID">How to find list ID</a>.</p>
<?php wp_nonce_field( 'klaviyo-update-settings', '_wpnonce', true, true ); ?>

<table class="form-table">
	<tr>
		<th scope="row">
			<label for="klaviyo_public_api_key">Public API Key</label>
		</th>
		<td>
			<input type="text" class="regular-text" name="klaviyo_public_api_key" value="<?php echo $klaviyo_settings['public_api_key']; ?>" />
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="klaviyo_newsletter_list_id">Add a subscribe to newsletter checkbox on the checkout page</label>
		</th>
		<td>
			<?php if( $response['success'] ): ?>
			<select name="klaviyo_newsletter_list_id" id="klaviyo_newsletter_list_id" class="regular-text">
			<?php foreach( $response['data'] as $list ): ?>
				<option value="<?php echo $list->list_id; ?>"><?php echo "{$list->list_name} ({$list->list_id})"; ?></option>
			<?php endforeach; ?>
			</select>
			<?php else: ?>
			<input type="text" class="regular-text" name="klaviyo_newsletter_list_id" id="klaviyo_newsletter_list_id" placeholder="Klaviyo list ID" value="<?php echo $klaviyo_settings['klaviyo_newsletter_list_id']; ?>" />
			<?php endif; ?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="klaviyo_newsletter_text">Subscribe to newsletter text</label>
		</th>
		<td>
			<input type="text" class="regular-text" name="klaviyo_newsletter_text" placeholder="Eg. I do not mind if you send me relevant updates." value="<?php echo $klaviyo_settings['klaviyo_newsletter_text']; ?>" />
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="klaviyo_newsletter_position">Subscribe to newsletter text Position</label>
		</th>
		<td>
			<select name="klaviyo_newsletter_position" id="klaviyo_newsletter_position" class="regular-text">
				<?php $position = !empty( $klaviyo_settings['klaviyo_newsletter_position'] ) ? $klaviyo_settings['klaviyo_newsletter_position'] : 'woocommerce_after_checkout_billing_form'; ?>
				<?php foreach( $this->get_newsletter_positions() as $hook => $name ): ?>
				<option value="<?php echo $hook; ?>" <?php selected( $position, $hook, true ); ?> ><?php echo $name; ?></option>
				<?php endforeach; ?>
			</select>
			<p class="description">These positions may change with themes and plugins. We have covered most positions within the checkout page. Please test and choose the one as per your need.</p>
		</td>				
	</tr>
	<tr>
		<th scope="row">
			<label for="klaviyo_newsletter_checked_default">Subscribe to newsletter text Position</label>
		</th>
		<td>
			<?php $newsletter_checkbox_default = !empty( $klaviyo_settings['klaviyo_newsletter_checked_default'] ) ? $klaviyo_settings['klaviyo_newsletter_checked_default'] : false; ?>
			<input type="checkbox" name="klaviyo_newsletter_checked_default" id="klaviyo_newsletter_checked_default" value="true" <?php checked( $newsletter_checkbox_default, 'true', true ); ?> >
			<p class="description">Newsletter to be checked by default?</p>
		</td>				
	</tr>
	<tr>
		<th scope="row">
			<label for="klaviyo_configuration_warning">Disable Configuration Warning</label>
		</th>
		<td>
			<input type="checkbox" name="admin_settings_message" value="true" <?php checked( $klaviyo_settings['admin_settings_message'], 'true', true ); ?> />
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="klaviyo_popup">Enable Klaviyo signup forms</label>
		</th>
		<td>
			<input type="checkbox" name="klaviyo_popup" value="true" <?php  checked( $klaviyo_settings['klaviyo_popup'], 'true', true ); ?> />
		</td>
	</tr>
</table>
<p>This will automatically install the Klaviyo script needed for signup forms. This script is required for the built-in signup form widget. Learn more about Klaviyo <a href="https://help.klaviyo.com/hc/en-us/articles/360002035871-Install-Klaviyo-Signup-Forms#verify-your-installation">Signup forms.</a></p>