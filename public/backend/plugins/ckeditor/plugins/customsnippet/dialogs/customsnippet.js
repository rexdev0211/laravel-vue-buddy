/**
 * The customsnippet dialog definition.
 *
 * Created out of the CKEditor Plugin SDK:
 * http://docs.ckeditor.com/#!/guide/plugin_sdk_sample_1
 */
 
// var customsnippet = 0;
// Our dialog definition.
CKEDITOR.dialog.add( 'customsnippetDialog', function( editor ) {
	return {

		// Basic properties of the dialog window: title, minimum size.
		title: 'Add custom Snippet Criteria',
		minWidth: 400,
		minHeight: 150,

		// Dialog window contents definition.
		contents: [
			{
				// Definition of the Basic Settings dialog tab (page).
				id: 'tab-customsnippet',
				label: 'Basic Settings',

				// The tab contents.
				elements: [
					{
						type : 'select',
						labelLayout : 'horizontal',
						id: 'coCustomSnippet',
						default : 'All',
						label: 'Select Custom Snippet ',
						widths : [ '25%','75%' ],
						controlStyle: "width:190px",
						items : itemstrNormalSnippetdata
						// validate: CKEDITOR.dialog.validate.notEmpty( "Explanation field cannot be empty" )
					}

				]
			},
		],

		// This method is invoked once a user clicks the OK button, confirming the dialog.
		onOk: function() {
			// The context of this function is the dialog object itself.
			// http://docs.ckeditor.com/#!/api/CKEDITOR.dialog
			var dialog = this;
				strcoCustomSnippet=dialog.getValueOf( 'tab-customsnippet', 'coCustomSnippet' );
				$.ajax({
					type: 'POST',
					url: '/static_pages/getsnippetdataforckeditor',
					data: 'strcoCustomSnippet='+strcoCustomSnippet,
					success: function(data) {
						var finalleadformstr=data;
						editor.insertHtml(finalleadformstr+'<div class="c">&nbsp;</div><p>&nbsp;</p>');
					} 
				});
		}
	};
});
