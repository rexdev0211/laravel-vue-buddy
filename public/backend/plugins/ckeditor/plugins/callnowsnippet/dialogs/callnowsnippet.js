/**
 * The callnowsnippet dialog definition.
 *
 * Created out of the CKEditor Plugin SDK:
 * http://docs.ckeditor.com/#!/guide/plugin_sdk_sample_1
 */
 
// Our dialog definition.
CKEDITOR.dialog.add( 'callnowsnippetDialog', function( editor ) {
	return {

		// Basic properties of the dialog window: title, minimum size.
		title: 'Call For',
		minWidth: 250,
		minHeight: 50,

		// Dialog window contents definition.
		contents: [
			{
				// Definition of the Basic Settings dialog tab (page).
				id: 'tab-callnowsnippet',
				label: 'Basic Settings',

				// The tab contents.
				elements: [
					{
						id: 'callfor',
						type: 'text',
						default: 'Michelle Sterling'
					}
				]
			},
		],
		// This method is invoked once a user clicks the OK button, confirming the dialog.
		onOk: function() {
			// The context of this function is the dialog object itself.
			// http://docs.ckeditor.com/#!/api/CKEDITOR.dialog
			var dialog = this;
				callfor = dialog.getValueOf( 'tab-callnowsnippet','callfor');
				
				var finalcallstr='<!--CALLNOWSTART-->'
									+'<div class="clicktocall c">'
										+'<div>CALL <strong>'+callfor.toUpperCase()+'</strong> DIRECTLY FOR</div>'
										+'<div>IMMEDIATE ANSWERS TO ALL YOUR QUESTIONS</div>'
										+'<div class="ph">{~~Site.toll_free_number~~} or {~~Site.direct_number~~}</div>'
									+'</div><div class="c">&nbsp;</div><p>&nbsp;</p>'
								+'<!--CALLNOWEND-->';
				editor.insertHtml(finalcallstr);
		}
	};
});
//+'<div class="callbutton"><a href="http://www.ringcentral.com" target="Callback_RingMe" onclick="var wind = window; var winop = wind.open; winop(&quot;http://www.ringcentral.com/ringme/?uc=43546551,0,0&amp;s=no&amp;v=2&quot;, &quot;Callback_RingMe&quot;, &quot;resizable=no,width=380,height=240&quot;); return false;"><img alt="click-to-call from the web" src="http://www.ringcentral.com/ringme/click-to-call-large-o.gif" border="0"></a></div>'
