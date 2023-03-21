/**
 * Basic sample plugin inserting abbreviation elements into CKEditor editing area.
 *
 * Created out of the CKEditor Plugin SDK:
 * http://docs.ckeditor.com/#!/guide/plugin_sdk_sample_1
 */
// Register the plugin within the editor.
CKEDITOR.plugins.add( 'pricerangelink', {
	// Register the icons.
	icons: 'pricerangelink',
	// The plugin initialization logic goes inside this method.
	init: function( editor ) {
		// Define an editor command that opens our dialog.
		editor.addCommand( 'pricerangelink', new CKEDITOR.dialogCommand( 'pricerangelinkDialog' ) );
		// Create a toolbar button that executes the above command.
		editor.ui.addButton( 'Pricerangelink', {
			// The text part of the button (if available) and tooptip.
			label: 'Insert Price Range Link',
			// The command to execute on click.
			command: 'pricerangelink',
			// The button placement in the toolbar (toolbar group name).
			toolbar: 'snippet'
		});
		// Register our dialog file. this.path is the plugin folder path.
		CKEDITOR.dialog.add( 'pricerangelinkDialog', this.path + 'dialogs/pricerangelink.js' );
	}
});

