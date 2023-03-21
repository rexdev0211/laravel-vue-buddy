/**
 * Basic sample plugin inserting abbreviation elements into CKEditor editing area.
 *
 * Created out of the CKEditor Plugin SDK:
 * http://docs.ckeditor.com/#!/guide/plugin_sdk_sample_1
 */
// Register the plugin within the editor.
CKEDITOR.plugins.add( 'featurelisting', {
	// Register the icons.
	icons: 'featurelisting',
	// The plugin initialization logic goes inside this method.
	init: function( editor ) {
		// Define an editor command that opens our dialog.
		editor.addCommand( 'featurelisting', new CKEDITOR.dialogCommand( 'featurelistingDialog' ) );
		// Create a toolbar button that executes the above command.
		editor.ui.addButton( 'Featurelisting', {
			// The text part of the button (if available) and tooptip.
			label: 'Add Feature Listing',
			// The command to execute on click.
			command: 'featurelisting',
			// The button placement in the toolbar (toolbar group name).
			toolbar: 'snippet'
		});
		// Register our dialog file. this.path is the plugin folder path.
		CKEDITOR.dialog.add( 'featurelistingDialog', this.path + 'dialogs/featurelisting.js');
	}
});

