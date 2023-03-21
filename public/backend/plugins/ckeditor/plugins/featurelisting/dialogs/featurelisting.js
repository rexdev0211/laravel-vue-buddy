/**
 * The abbr dialog definition.
 *
 * Created out of the CKEditor Plugin SDK:
 * http://docs.ckeditor.com/#!/guide/plugin_sdk_sample_1
 */
// Our dialog definition.
CKEDITOR.dialog.add( 'featurelistingDialog', function( editor ) {
	return {
		// Basic properti es of the dialog window: title, minimum size.
		title: 'Select Feature Lisitng Criteria',
		minWidth: 400,
		minHeight: 200,
		// Dialog window contents definition.
		contents: [
			{
				// Definition of the Basic Settings dialog tab (page).
				id: 'tab-featurelisting',
				label: 'Basic Settings',
				// The tab contents.
				elements: [
					{
						id: 'foreclosure',
						type: 'checkbox',
						label: 'Foreclosure'
					},
					{
						type: 'checkbox',
						id: 'shortsale',
						label: 'Short Sale'
					},
					{
						id: 'txtminprice',
						type: 'text',
						labelLayout : 'horizontal',
						label: 'Minimum Price',
						widths : [ '35%','65%' ],
						controlStyle: "width:200px"
					},
					{
						id: 'coProperty',
						type : 'select',
						labelLayout : 'horizontal',
						label: 'Property Type',
						'default' : 'All',
						widths : [ '35%','65%' ],
						controlStyle: "width:200px",
						items : itempdata
					},
					{
						id: 'coTemplate',
						type : 'select',
						labelLayout : 'horizontal',
						label: 'Template Type ',
						default : templateType,
						widths : [ '35%','65%' ],
						controlStyle: "width:200px",
						items : itemTemplatetypedata,
						onLoad : function(){
							selectedtemplate = this.getValue();
							var dialog = this.getDialog();
							showTempleteData(dialog,'tab-featurelisting',selectedtemplate);
						},
						onChange : function(){
							selectedtemplate = this.getValue();
							var dialog = this.getDialog();
							showTempleteData(dialog,'tab-featurelisting',selectedtemplate);
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
						id: 'coElementary_School_K_2',
						type : 'select',
						labelLayout : 'horizontal',
						label: 'Elementary School K-2 ',
						default : 'All',
						widths : [ '35%','65%' ],
						controlStyle: "width:200px",
						items : itemElementary_School_K_2data
					}
				]
			},
		],
		// This method is invoked once a user clicks the OK button, confirming the dialog.
		onOk: function() {
			// The context of this function is the dialog object itself.
			var dialog = this;
			txtminprice=dialog.getValueOf( 'tab-featurelisting', 'txtminprice' );
			strtxtminprice=txtminprice;			
			if(dialog.getValueOf( 'tab-featurelisting', 'foreclosure' )==true){
				strforeclosure='foreclosure';
			}else{
				strforeclosure='';
			}				
			if(dialog.getValueOf( 'tab-featurelisting', 'shortsale' )==true){
				strshortsale='shortsale';
			}else{
				strshortsale='';
			}
			strcoTemplate=dialog.getValueOf( 'tab-featurelisting', 'coTemplate' );
			strcoCommunitymbo = getSelectedTempleteValue(dialog,'tab-featurelisting',strcoTemplate);
			strcoCommunitymboVal = getTempleteCommunityVal(strcoTemplate,strcoCommunitymbo);
			strcoPropertytypembo=dialog.getValueOf( 'tab-featurelisting', 'coProperty' );
			finalstr='<p>&nbsp;</p>'
						+'<center id="featurelisting" sinfo="fl=1&fl_templatetype='+strcoTemplate+'&fl_propertytype='+strcoPropertytypembo+'&fl_communityname='+strcoCommunitymboVal+'&fl_foreclosure='+strforeclosure+'&fl_shortsale='+strshortsale+'&fl_minprice='+strtxtminprice+'&featurelisting='+featurelisting+'"  class="featurelisting fullfl_'+featurelisting+' capitalize">'
						+'<div class="liner"><h1>'+strcoCommunitymbo+' Featured Homes</h1></div>'
						+'<ul class="fhomes">';
						for (var cnt=1;cnt <= 12;cnt++){ 
							counter = cnt+'_'+featurelisting;
							strContent = '<li id="showhidediv'+counter+'">'
											+'<a href="{link'+counter+'}"><img src="/resize_image.php?image=img/frontend/fh1.png" alt="{communityname'+counter+'} Home MLS# {mls_num'+counter+'}" title="{communityname'+counter+'} Home MLS# {mls_num'+counter+'}"/></a>'
											+'{idxlogo}<span class="prices">{price'+counter+'}</span>'
											+'<span class="description">{beds_full'+counter+'} Beds {baths_full'+counter+'} Baths {sq_feet'+counter+'} SqFt'
											+'<br />{propertytype'+counter+'}'
											+'<br /><strong>{address'+counter+'}</strong>'
											+'<div>{community'+counter+'}</div></span>'
										+'</li>';
							finalstr+=strContent;
						}
			finalstr+='</ul>';
			finalstr+='</center><div class="c"></div><div class="c">&nbsp;</div><p>&nbsp;</p>';
			featurelisting=featurelisting+1;
			editor.insertHtml(finalstr);
		}
	};
});
