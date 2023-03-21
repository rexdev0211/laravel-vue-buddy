/**
 * customsnippet plugin inserting abbreviation elements into CKEditor editing area.
 *
 * Created out of the CKEditor Plugin SDK:
 * http://docs.ckeditor.com/#!/guide/plugin_sdk_sample_1
 */

// Register the plugin within the editor.
CKEDITOR.plugins.add( 'customsnippet', {

	// Register the icons.
	icons: 'customsnippet',

	// The plugin initialization logic goes inside this method.
	init: function( editor ) {

		// Define an editor command that opens our dialog.
		editor.addCommand( 'customsnippet', new CKEDITOR.dialogCommand( 'customsnippetDialog' ) );

		// Create a toolbar button that executes the above command.
		editor.ui.addButton( 'Customsnippet', {

			// The text part of the button (if available) and tooptip.
			label: 'Add custom Snippet Form',

			// The command to execute on click.
			command: 'customsnippet',

			// The button placement in the toolbar (toolbar group name).
			toolbar: 'others'
		});
		if ( editor.contextMenu ) {
			editor.addMenuGroup( 'customsnippetGroup' );
			editor.addMenuItem( 'customsnippetItem', {
				label: 'Edit customsnippet',
				icon: this.path + 'icons/customsnippet.png',
				command: 'customsnippetDialog',
				group: 'customsnippetGroup'
			});
			editor.contextMenu.addListener( function( element ) {
				if ( element.getAscendant( 'customsnippet', true ) ) {
					return { customsnippetItem: CKEDITOR.TRISTATE_OFF };
				}
			});
		}
		// Register our dialog file. this.path is the plugin folder path.
		CKEDITOR.dialog.add( 'customsnippetDialog', this.path + 'dialogs/customsnippet.js' );
	}
});
