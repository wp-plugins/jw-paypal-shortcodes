// closure to avoid namespace collision
(function(){
	// creates the plugin
	tinymce.create('tinymce.plugins.jwpaypal', {
		// creates control instances based on the control's id.
		// our button's id is "jwpaypal_button"
		createControl : function(id, controlManager) {
			if (id == 'jwpaypal_button') {
				// creates the button
				var button = controlManager.createButton('jwpaypal_button', {
					title : 'PayPal Shortcode', // title of the button
					image : '../wp-content/plugins/jw-paypal-shortcodes/jw-pp-icon.gif',
					onclick : function() {
						// triggers the thickbox
						var width = jQuery(window).width(), H = jQuery(window).height(), W = ( 720 < width ) ? 720 : width;
						W = W - 80;
						H = H - 84;
						tb_show( 'JW PayPal', '#TB_inline?width=' + W + '&height=' + H + '&inlineId=jwpaypal-form' );
					}
				});
				return button;
			}
			return null;
		}
	});
	
	// registers the plugin. DON'T MISS THIS STEP!!!
	tinymce.PluginManager.add('jwpaypal', tinymce.plugins.jwpaypal);
	
	// executes this when the DOM is ready
	jQuery(function(){
		// creates a form to be displayed everytime the button is clicked
		// you should achieve this using AJAX instead of direct html code like this
		var form = jQuery('<div id="jwpaypal-form"><table id="jwpaypal-table" class="form-table">\
			<tr>\
				<th><label for="jwpaypal-type">Type</label></th>\
				<td><select name="type" id="jwpaypal-type">\
					<option value="add">Add to Cart</option>\
					<option value="view">View Cart / Checkout</option>\
				</select><br />\
				<small>select button type</small></td>\
			</tr>\
			<tr>\
				<th><label for="jwpaypal-amount">Amount</label></th>\
				<td><input type="text" id="jwpaypal-amount" name="amount" value="" /><br />\
				<small>specify the price</small></td>\
			</tr>\
			<tr>\
				<th><label for="jwpaypal-productname">Product Name</label></th>\
				<td><input type="text" name="productname" id="jwpaypal-productname" value="" /><br />\
				<small>specify the product name</small>\
			</tr>\
			<tr>\
				<th><label for="jwpaypal-sku">Product SKU</label></th>\
				<td><input type="text" name="sku" id="jwpaypal-sku" value="" /><br />\
				<small>specify product sku</small></td>\
			</tr>\
			<tr>\
				<th><label for="jwpaypal-extra">Product Input</label></th>\
				<td><input type="text" name="extra" id="jwpaypal-extra" value="" /><br />\
				<small>specify product extra info</small></td>\
			</tr>\
			<tr>\
				<th><label for="jwpaypal-shipadd">Shipping Address Required</label></th>\
				<td><select name="shipadd" id="jwpaypal-shipadd">\
				<option value="0">prompt for an address, but do not require one</option>\
				<option value="1">do not prompt for an address</option>\
				<option value="2">prompt for an address, and require one</option>\
				</select>\
				</td>\
			</tr>\
			<tr>\
				<th><label for="jwpaypal-shipcost">Shipping Cost</label></th>\
				<td><input type="text" name="shipcost" id="jwpaypal-shipcost" value="" /><br />\
				<small>the cost of shipping this item.</small></td>\
			</tr>\
			<tr>\
				<th><label for="jwpaypal-shipcost2">Additional Shipping Cost</label></th>\
				<td><input type="text" name="shipcost2" id="jwpaypal-shipcost2" value="" /><br />\
				<small>the cost of shipping each additional unit of this item.</small></td>\
			</tr>\
			<tr>\
				<th><label for="jwpaypal-weight">Weight in Pounds</label></th>\
				<td><input type="text" name="weight" id="jwpaypal-weight" value="" /><br />\
				<small>specify product weight in pounds, if profile-based shipping rates are configured with a basis of weight, the sum of weight values is used to calculate the shipping charges for the transaction.</small></td>\
			</tr>\
		</table>\
		<p class="submit">\
			<input type="button" id="jwpaypal-submit" class="button-primary" value="Insert PayPal Button" name="submit" />\
		</p>\
		</div>');
		
		var table = form.find('table');
		form.appendTo('body').hide();
		
		// handles the click event of the submit button
		form.find('#jwpaypal-submit').click(function(){
			// defines the options and their default values
			// again, this is not the most elegant way to do this
			// but well, this gets the job done nonetheless
			var options = { 
				'type' : '',
				'amount'    : '',
				'productname' : '',
				'sku'       : '',
				'extra'    : '',
				'shipadd'	: '',
				'shipcost'	: '',
				'shipcost2'	: '',								
				'weight'	: ''
			};
			var shortcode = '[paypal';
			
			for( var index in options) {
				var value = table.find('#jwpaypal-' + index).val();
				
				// attaches the attribute to the shortcode only if it's different from the default value
				if ( value !== options[index] )
					shortcode += ' ' + index + '="' + value + '"';
			}
			
			shortcode += ']';
			
			// inserts the shortcode into the active editor
			tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
			
			// closes Thickbox
			tb_remove();
		});
	});
})()