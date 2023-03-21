/**
 * The abbr dialog definition.
 *
 * Created out of the CKEditor Plugin SDK:
 * http://docs.ckeditor.com/#!/guide/plugin_sdk_sample_1
 */
 var strCommunity="[[ 'Select One' , 'None']";
// Our dialog definition.
CKEDITOR.dialog.add( 'pricerangelinkDialog', function( editor ) {
	return {
		title: 'Select Price Range Criteria',
		minWidth: 450,
		minHeight: 150,
		// Dialog window contents definition.
		contents: [
			{
				id: 'tab-basic',
				label: 'Basic Settings',
				// The tab contents.
				elements: [
					{
						id: 'foreclosure',
						type: 'checkbox',
						label: 'Foreclosure'
					},
					{
						id: 'shortsale',
						type: 'checkbox',
						label: 'Short Sale'
					},
					{
						id: 'coProperty',
						type : 'select',
						labelLayout : 'horizontal',
						default : 'All',
						label: 'Property Type ',
						widths : [ '35%','65%' ],
						controlStyle: "width:200px",
						items : itempdata
					},
					{
						id: 'coTemplate',
						type : 'select',
						labelLayout : 'horizontal',
						default : templateType,
						label: 'Template Type ',
						widths : [ '35%','65%' ],
						controlStyle: "width:200px",
						items : itemTemplatetypedata,
						onLoad : function(){
							selectedtemplate = this.getValue();
							var dialog = this.getDialog();
							showTempleteData(dialog,'tab-basic',selectedtemplate);
						},
						onChange : function(){
							selectedtemplate = this.getValue();
							var dialog = this.getDialog();
							showTempleteData(dialog,'tab-basic',selectedtemplate);
						}
					},
					{
						type : 'select',
						labelLayout : 'horizontal',
						id: 'coCommunitymbo',
						default : 'All',
						label: 'Community Name ',
						widths : [ '35%','65%' ],
						controlStyle: "width:200px",
						items : itemdata
					},
					{
						type : 'select',
						labelLayout : 'horizontal',
						id: 'coZipcode',
						default : 'All',
						label: 'Zipcode',
						widths : [ '35%','65%' ],
						controlStyle: "width:200px",
						items : itemzipcodesdata
					},
					{
						type : 'select',
						labelLayout : 'horizontal',
						id: 'coHighSchool',
						default : 'All',
						label: 'High School',
						widths : [ '35%','65%' ],
						controlStyle: "width:200px",
						items : itempHighSchooldata
					},
					{
						type : 'select',
						labelLayout : 'horizontal',
						id: 'coJrHighSchool',
						default : 'All',
						label: 'Junior High School ',
						widths : [ '35%','65%' ],
						controlStyle: "width:200px",
						items : itempJr_High_Schooldata
					},
					{
						type : 'select',
						labelLayout : 'horizontal',
						id: 'coElementary_School_3_5',
						default : 'All',
						label: 'Elementary School 3-5 ',
						widths : [ '35%','65%' ],
						controlStyle: "width:200px",
						items : itempElementary_School_3_5data
					},
					{
						type : 'select',
						labelLayout : 'horizontal',
						id: 'coElementary_School_K_2',
						default : 'All',
						label: 'Elementary School K-2 ',
						widths : [ '35%','65%' ],
						controlStyle: "width:200px",
						items : itemElementary_School_K_2data
					}

				]
			},
		],
		// This method is invoked once a user clicks the OK button, confirming the dialog.
		onOk: function() {
			// The context of this function is the dialog object itself. // http://docs.ckeditor.com/#!/api/CKEDITOR.dialog
			var dialog = this;
			//strCommnunityname=dialog.getValueOf( 'tab-basic', 'coCommunitymbo' )
			strcoTemplate=dialog.getValueOf( 'tab-basic', 'coTemplate' );
			strCommnunityname = getSelectedTempleteValue(dialog,'tab-basic',strcoTemplate);
			if(typeof isTempletePage != 'undefined'){
				strupperCommnunityname = strCommnunityname;
			}else{
				strupperCommnunityname = strCommnunityname.toUpperCase();
			}
			strlinkCommnunityname=strCommnunityname.replace(/ /g,"+");
			strpricerangelink='';
			if(dialog.getValueOf( 'tab-basic', 'foreclosure' )==true){
				strpricerangelink=strpricerangelink+'foreclosure/';
			}
			if(dialog.getValueOf( 'tab-basic', 'shortsale' )==true){
				strpricerangelink=strpricerangelink+'shortsale/';
			}
			strpricerangelink=strpricerangelink+dialog.getValueOf( 'tab-basic', 'coProperty' )+'/';
			strpricerangelink = strpricerangelink + getTempleteCommunityVal(strcoTemplate,strlinkCommnunityname,true);
			strpricerangelink = '/property-list/'+strpricerangelink.toLowerCase().replace(/ /g,"+");
			finalstr='<div class="pricertange"><div class="liner"><h1 class="midget">'+strupperCommnunityname+' MLS Quick Search</h1></div>'			
					+'<div class="pricesearch">'
						+'<h1>'
							+'<strong> '+strupperCommnunityname+' MLS QUICK SEARCH </strong><br />'
							+'SEARCH ALL OF THE REAL ESTATE FOR SALE IN '+strupperCommnunityname+' BY PRICE RANGE'
						+'</h1>'
						+'<ul>'
							+'<li><a href="'+strpricerangelink+'/75k-100k.html">$75,000 - $100,000</a></li>'
							+'<li><a href="'+strpricerangelink+'/100k-150k.html">$100,000 - $150,000</a></li>'
							+'<li><a href="'+strpricerangelink+'/150k-200k.html">$150,000 - $200,000</a></li>'
							+'<li><a href="'+strpricerangelink+'/200k-250k.html">$200,000 - $250,000</a></li>'
							+'<li><a href="'+strpricerangelink+'/250k-3000k.html">$250,000 - $300,000</a></li>'
							+'<li><a href="'+strpricerangelink+'/300k-400k.html">$300,000 - $400,000</a></li>'
							+'<li><a href="'+strpricerangelink+'/400k-500k.html">$400,000 - $500,000</a></li>'
							+'<li><a href="'+strpricerangelink+'/500k-600k.html">$500,000 - $600,000</a></li>'
							+'<li><a href="'+strpricerangelink+'/600k-800k.html">$600,000 - $800,000</a></li>'
							+'<li><a href="'+strpricerangelink+'/800k-1000k.html">$800,000 - $1000,000</a></li>'
						+'</ul>'
					+'</div>'
					+'<div class="over"><a href="'+strpricerangelink+'/1000k-1000000k.html">HOMES OVER $1000,000</a></div>'
					+'<div class="c">&nbsp;</div>'
					+'<p>&nbsp;</p></div>';
			editor.insertHtml(finalstr);			
		}
	};
});
