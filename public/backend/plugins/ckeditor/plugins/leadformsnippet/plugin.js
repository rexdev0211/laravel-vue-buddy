/**
 * leadformsnippet plugin inserting abbreviation elements into CKEditor editing area.
 *
 * Created out of the CKEditor Plugin SDK:
 * http://docs.ckeditor.com/#!/guide/plugin_sdk_sample_1
 */

// Register the plugin within the editor.
CKEDITOR.plugins.add( 'leadformsnippet', {

	// Register the icons.
	icons: 'leadformsnippet',

	// The plugin initialization logic goes inside this method.
	init: function( editor ) {

		// Define an editor command that opens our dialog.
		editor.addCommand( 'leadformsnippet', new CKEDITOR.dialogCommand( 'leadformsnippetDialog' ) );

		// Create a toolbar button that executes the above command.
		editor.ui.addButton( 'Leadformsnippet', {

			// The text part of the button (if available) and tooptip.
			label: 'Add Contact Form',

			// The command to execute on click.
			command: 'leadformsnippet',

			// The button placement in the toolbar (toolbar group name).
			toolbar: 'others'
		});
		if ( editor.contextMenu ) {
			editor.addMenuGroup( 'leadformsnippetGroup' );
			editor.addMenuItem( 'leadformsnippetItem', {
				label: 'Edit leadformsnippet',
				icon: this.path + 'icons/leadformsnippet.png',
				command: 'leadformsnippetDialog',
				group: 'leadformsnippetGroup'
			});
			editor.contextMenu.addListener( function( element ) {
				if ( element.getAscendant( 'leadformsnippet', true ) ) {
					return { leadformsnippetItem: CKEDITOR.TRISTATE_OFF };
				}
			});
		}
		// Register our dialog file. this.path is the plugin folder path.
		CKEDITOR.dialog.add( 'leadformsnippetDialog', this.path + 'dialogs/leadformsnippet.js' );
	}
});
