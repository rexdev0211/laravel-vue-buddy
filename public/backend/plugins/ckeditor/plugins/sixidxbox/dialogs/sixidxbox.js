/**
 * The sixidxbox dialog definition.
 *
 * Created out of the CKEditor Plugin SDK:
 * http://docs.ckeditor.com/#!/guide/plugin_sdk_sample_1
 */
 
// var sixidxbox = 0;
// Our dialog definition.
CKEDITOR.dialog.add( 'sixidxboxDialog', function( editor ) {
	return {
		title: 'Select Six Idx Criteria',
		minWidth: 450,
		minHeight: 200,
		contents: [
			{
				id: 'tab-sixidxbox',
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
						id: 'txtminprice',
						type: 'text',
						label: 'Minimum Price',
						labelLayout : 'horizontal',
						widths : [ '35%','65%' ],
						controlStyle: "width:200px"
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
							showTempleteData(dialog,'tab-sixidxbox',selectedtemplate);
						},
						onChange : function(){
							selectedtemplate = this.getValue();
							var dialog = this.getDialog();
							showTempleteData(dialog,'tab-sixidxbox',selectedtemplate);
						}
					},
					{
						id: 'coCommunitymbo',
						type : 'select',
						labelLayout : 'horizontal',
						default : 'All',
						label: 'Community Name ',
						widths : [ '35%','65%' ],
						controlStyle: "width:200px",
						items : itemdata
					},
					{
						id: 'coZipcode',
						type : 'select',
						labelLayout : 'horizontal',
						default : 'All',
						label: 'Zipcode',
						widths : [ '35%','65%' ],
						controlStyle: "width:200px",
						items : itemzipcodesdata
					},
					{
						id: 'coHighSchool',
						type : 'select',
						labelLayout : 'horizontal',
						default : 'All',
						label: 'High School',
						widths : [ '35%','65%' ],
						controlStyle: "width:200px",
						items : itempHighSchooldata
					},
					{
						id: 'coJrHighSchool',
						type : 'select',
						labelLayout : 'horizontal',
						default : 'All',
						label: 'Junior High School ',
						widths : [ '35%','65%' ],
						controlStyle: "width:200px",
						items : itempJr_High_Schooldata
					},
					{
						id: 'coElementary_School_3_5',
						type : 'select',
						labelLayout : 'horizontal',
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
			// The context of this function is the dialog object itself.
			// http://docs.ckeditor.com/#!/api/CKEDITOR.dialog
			var dialog = this;
			txtminprice=dialog.getValueOf( 'tab-sixidxbox', 'txtminprice' );
			strtxtminprice=txtminprice;
			if(dialog.getValueOf( 'tab-sixidxbox', 'foreclosure' )==true){
				strforeclosure='foreclosure';
			}else{
				strforeclosure='';
			}
			if(dialog.getValueOf( 'tab-sixidxbox', 'shortsale' )==true){
				strshortsale='shortsale';
			}else{
				strshortsale='';
			}
			strcoTemplate=dialog.getValueOf( 'tab-sixidxbox', 'coTemplate' );
			strcoCommunitymbo = getSelectedTempleteValue(dialog,'tab-sixidxbox',strcoTemplate);
			strcoCommunitymboVal = getTempleteCommunityVal(strcoTemplate,strcoCommunitymbo);
			strcoPropertytypembo=dialog.getValueOf( 'tab-sixidxbox', 'coProperty' );
			//+'<div id="sixidxbox" sib="1" sib_templatetype="'+strcoTemplate+'" sib_propertytype="'+strcoPropertytypembo+'" sib_communityname="'+strcoCommunitymboVal+'" sib_foreclosure="'+strforeclosure+'" sib_shortsale="'+strshortsale+'" sib_minprice="'+strtxtminprice+'" sixidxbox='+sixidxbox+'>'
			finalstr='<p>&nbsp;</p><div class="fullsixidx_'+sixidxbox+' capitalize"><div class="liner"><h1>'+strcoCommunitymbo+' HOMES</h1></div>'
						+'<div id="sixidxbox" sinfo="sib=1&sib_templatetype='+strcoTemplate+'&sib_propertytype='+strcoPropertytypembo+'&sib_communityname='+strcoCommunitymboVal+'&sib_foreclosure='+strforeclosure+'&sib_shortsale='+strshortsale+'&sib_minprice='+strtxtminprice+'&sixidxbox='+sixidxbox+'">'
						+'<ul class="sixidxbox">';
						for (var cnt=1;cnt <= 6;cnt++){ 
							counter = cnt+'_'+sixidxbox;
							strContent = '<li  id="showhidediv'+counter+'">'
												+'<a href="{link'+counter+'}"><img src="/resize_image.php?image=img/frontend/fh1.png" alt="{communityname'+counter+'} Home MLS# {mls_num'+counter+'}" title="{communityname'+counter+'} Home MLS# {mls_num'+counter+'}"/></a>'
												+'{idxlogo}<span class="prices">{price'+counter+'}</span>'
												+'<span class="description">{beds_full'+counter+'} Beds {baths_full'+counter+'} Baths {sq_feet'+counter+'} SqFt'
												+'<br />{propertytype'+counter+'}'
												+'<br/><strong>{address'+counter+'}</strong>'
												+'<div>{community'+counter+'}</div></span>'
											+'</li>';
							finalstr+=strContent;
						}
			finalstr+='</ul><div class="c"></div>';
			finalstr+='</div></div>';
			sixidxbox=sixidxbox+1;
			editor.insertHtml(finalstr+'<div class="c">&nbsp;</div><p>&nbsp;</p>');
		}
	};
});
