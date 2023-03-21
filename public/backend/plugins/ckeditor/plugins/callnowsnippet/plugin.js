/**
 * callnowsnippet plugin inserting abbreviation elements into CKEditor editing area.
 *
 * Created out of the CKEditor Plugin SDK:
 * http://docs.ckeditor.com/#!/guide/plugin_sdk_sample_1
 */

// Register the plugin within the editor.
CKEDITOR.plugins.add( 'callnowsnippet', {

	// Register the icons.
	icons: 'callnowsnippet',

	// The plugin initialization logic goes inside this method.
	init: function( editor ) {

		// Define an editor command that opens our dialog.
		//editor.addCommand( 'callnowsnippet', new CKEDITOR.dialogCommand( 'callnowsnippetDialog' ) );
		editor.addCommand( 'callnowsnippet',{
			exec : function( editor ){ 
				var finalcallstr='<!--CALLNOWSTART-->'
							+'<div class="clicktocall c">'
								+'<img src="/img/frontend/call-now-banner.png">'
							+'</div><div class="c">&nbsp;</div>'
						+'<!--CALLNOWEND-->';
				editor.insertHtml(finalcallstr);
			}
		});
		// Create a toolbar button that executes the above command.
		editor.ui.addButton( 'Callnowsnippet', {

			// The text part of the button (if available) and tooptip.
			label: 'Add Call Now',

			// The command to execute on click.
			command: 'callnowsnippet',

			// The button placement in the toolbar (toolbar group name).
			toolbar: 'others'
		});
		if ( editor.contextMenu ) {
			editor.addMenuGroup( 'callnowsnippetGroup' );
			editor.addMenuItem( 'callnowsnippetItem', {
				label: 'Edit callnowsnippet',
				icon: this.path + 'icons/callnowsnippet.png',
				command: 'callnowsnippetDialog',
				group: 'callnowsnippetGroup'
			});
			editor.contextMenu.addListener( function( element ) {
				if ( element.getAscendant( 'callnowsnippet', true ) ) {
					return { callnowsnippetItem: CKEDITOR.TRISTATE_OFF };
				}
			});
		}		
		// Register our dialog file. this.path is the plugin folder path.
		//CKEDITOR.dialog.add( 'callnowsnippetDialog', this.path + 'dialogs/callnowsnippet.js' );
	}
});
