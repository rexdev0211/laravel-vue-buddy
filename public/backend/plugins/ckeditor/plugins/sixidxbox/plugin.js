/**
 * sixidxbox plugin inserting abbreviation elements into CKEditor editing area.
 *
 * Created out of the CKEditor Plugin SDK:
 * http://docs.ckeditor.com/#!/guide/plugin_sdk_sample_1
 */

// Register the plugin within the editor.
CKEDITOR.plugins.add( 'sixidxbox', {
	// Register the icons.
	icons: 'sixidxbox',
	// The plugin initialization logic goes inside this method.
	init: function( editor ) {
		// Define an editor command that opens our dialog.
		editor.addCommand( 'sixidxbox', new CKEDITOR.dialogCommand( 'sixidxboxDialog' ) );
		// Create a toolbar button that executes the above command.
		editor.ui.addButton( 'Sixidxbox', {
			// The text part of the button (if available) and tooptip.
			label: 'Add Six IDX Box',
			// The command to execute on click.
			command: 'sixidxbox',
			// The button placement in the toolbar (toolbar group name).
			toolbar: 'snippet'
		});
		if ( editor.contextMenu ) {
			editor.addMenuGroup( 'sixidxboxGroup' );
			editor.addMenuItem( 'sixidxboxItem', {
				label: 'Edit sixidxbox',
				icon: this.path + 'icons/sixidxbox.png',
				command: 'sixidxboxDialog',
				group: 'sixidxboxGroup'
			});
			editor.contextMenu.addListener( function( element ) {
				if ( element.getAscendant( 'sixidxbox', true ) ) {
					return { sixidxboxItem: CKEDITOR.TRISTATE_OFF };
				}
			});
		}
		// Register our dialog file. this.path is the plugin folder path.
		CKEDITOR.dialog.add( 'sixidxboxDialog', this.path + 'dialogs/sixidxbox.js' );
	}
});
