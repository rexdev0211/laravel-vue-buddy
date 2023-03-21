﻿/**
 * The leadformsnippet dialog definition.
 *
 * Created out of the CKEditor Plugin SDK:
 * http://docs.ckeditor.com/#!/guide/plugin_sdk_sample_1
 */
 
// var leadformsnippet = 0;
// Our dialog definition.
CKEDITOR.dialog.add( 'leadformsnippetDialog', function( editor ) {
	return {

		// Basic properties of the dialog window: title, minimum size.
		title: 'Add Form Title',
		minWidth: 400,
		minHeight: 150,

		// Dialog window contents definition.
		contents: [
			{
				// Definition of the Basic Settings dialog tab (page).
				id: 'tab-leadformsnippet',
				label: 'Basic Settings',

				// The tab contents.
				elements: [
					{
						// Text input field for the leadformsnippet text.
						type: 'text',
						id: 'txtleadformtitle',
						label: 'Title'
						// Validation checking whether the field is not empty.
						//validate: CKEDITOR.dialog.validate.notEmpty( "Abbreviation field cannot be empty" )
					}
				]
			},
		],

		// This method is invoked once a user clicks the OK button, confirming the dialog.
		onOk: function() {

			// The context of this function is the dialog object itself.
			// http://docs.ckeditor.com/#!/api/CKEDITOR.dialog
			var dialog = this;
				txtleadformtitle=dialog.getValueOf( 'tab-leadformsnippet', 'txtleadformtitle' );
				strtxtleadformtitle=txtleadformtitle;
				var finalleadformstr='<p>&nbsp;</p><h1>'+txtleadformtitle+'</h1><form accept-charset="utf-8" action="/users/contactus" class="form-validate" id="contactus" method="post"><div style="display:none;"><input name="_method" type="hidden" value="POST" /></div><table class="contact_form" width="100%"><tbody><tr><td><table width="100%"><tbody><tr><td><fieldset class="headerfieldset"><legend class="headerfieldset">Contact Information</legend><table width="100%"><tbody><tr><td><div class="input text required"><label for="txtfirstname">First Name</label></div></td><td><input id="txtfirstname" maxlength="250" name="first_name" type="text" /></td><td><div class="input text required"><label for="txtlastname">Last Name</label></div></td><td><input id="txtlastname" maxlength="250" name="last_name" type="text" /></td></tr><tr><td><div class="input text required"><label for="txtemail">Email</label></div></td><td><input id="txtemail" maxlength="250" name="email" type="text" /></td><td><div class="input text required"><label for="txtphone">Phone</label></div></td><td><input id="txtphone" maxlength="250" name="phone" type="text" /></td></tr></tbody></table></fieldset></td></tr><tr><td><fieldset class="headerfieldset"><legend class="headerfieldset">Property Information</legend><table width="100%"><tbody><tr><td><div class="input select"><label for="num_of_bedrooms">Number of bedrooms </label></div></td><td><select id="num_of_bedrooms" name="num_of_bedrooms"><option value="">Choose</option><option value="1">1+</option><option value="2">2+</option><option value="3">3+</option><option value="4">4+</option><option value="5">5+</option><option value="6">6+</option></select></td><td><div class="input select"><label for="num_of_bathrooms">Number of bathrooms</label></div></td><td><select id="num_of_bathrooms" name="num_of_bathrooms"><option value="">Choose</option><option value="1">1+</option><option value="2">2+</option><option value="3">3+</option><option value="4">4+</option><option value="5">5+</option><option value="6">6+</option></select></td></tr><tr><td><div class="input select"><label for="square_feet">Square Feet</label></div></td><td><select id="square_feet" name="square_feet"><option value="">Choose</option><option value="1">&lt; 1000</option><option value="2">1000 - 1500</option><option value="3">1500 - 2000</option><option value="4">2000 - 2500</option><option value="5">2500 - 3000</option><option value="6">3000 - 3500</option><option value="7">3500 - 4000</option><option value="8">4000 - 4500</option><option value="9">5000 - 6000</option><option value="10">7000 - 7000</option><option value="11">8000 - 8000</option><option value="12">9000 - 9000</option><option value="13">10000+</option></select></td><td><div class="input select"><label for="price_range">Price Ranges</label></div></td><td><select id="price_range" name="price_range"><option value="">Choose</option><option value="1">$100,000 - $150,000</option><option value="2">$150,000 - $200,000</option><option value="3">$200,000 - $250,000</option><option value="4">$250,000 - $300,000</option><option value="5">$300,000 - $350,000</option><option value="6">$350,000 - $400,000</option><option value="7">$400,000 - $450,000</option><option value="8">$450,000 - $500,000</option><option value="9">$500,000 - $600,000</option><option value="10">$600,000 - $700,000</option><option value="11">$700,000 - $800,000</option><option value="12">$800,000 - $900,000</option><option value="13">$900,000 - $1,000,000</option><option value="14">$1,000,000 +</option></select></td></tr></tbody></table></fieldset></td></tr><tr><td><fieldset class="headerfieldset"><legend class="headerfieldset">Moving details</legend><table width="100%"><tbody><tr><td><div class="input select required"><label for="when_to_move">When do you want to move?</label></div></td><td><select id="when_to_move" name="when_to_move"><option value="">Choose</option><option value="1">less then 30 days</option><option value="2">1 month</option><option value="3">2 months</option><option value="4">3 months</option><option value="5">4 months</option><option value="6">5 months</option><option value="7">6 months</option><option value="8">7 months</option><option value="9">8 months</option><option value="10">9 months</option><option value="11">10 months</option><option value="12">11 months</option><option value="13">12 months +</option></select></td></tr><tr><td><div class="input select required"><label for="how_long_looking">How long have you been looking?</label></div></td><td><select id="how_long_looking" name="how_long_looking"><option value="">Choose</option><option value="1">less then 30 days</option><option value="2">1 month</option><option value="3">2 months</option><option value="4">3 months</option><option value="5">4 months</option></select></td></tr><tr><td><div class="input select required"><label for="is_with_agent">Are you currently working with an agent?</label></div></td><td><select id="is_with_agent" name="is_with_agent"><option value="">Choose</option><option value="Yes">Yes</option><option value="No">No</option></select></td></tr><tr><td><div class="input select required"><label for="prequalified">Have you been prequalified?</label></div></td><td><select id="prequalified" name="prequalified"><option value="">Choose</option><option value="Yes">Yes</option><option value="No">No</option></select></td></tr></tbody></table></fieldset></td></tr><tr><td><fieldset class="headerfieldset"><legend class="headerfieldset">Additional Information</legend><table width="100%"><tbody><tr><td><textarea cols="75" id="UserComments" name="comments" rows="3" style="width:90%;"></textarea></td></tr><tr><td><div class="propertysearch"><div class="submit"><input class="button-golden" type="submit" value="Submit your request" /></div></div>&nbsp;&nbsp;</td></tr></tbody></table></fieldset></td></tr></tbody></table></td></tr></tbody></table></form>';
				//var finalleadformstr='<p>&nbsp;</p><form accept-charset="utf-8" action="/leads/addpagelead" method="post" id="UserViewForm" name="Lead" onsubmit="javaScript:return checkStaticLeadForm();"><div style="display:none;"><input type="hidden" value="POST" name="_method"></div><table width="100%"><tbody><tr><th align="center"><font style="font-size: 14px;">'+txtleadformtitle+' Request More Information</font><br><br></th></tr><tr><th align="center"><font style="font-size: 12px;"><span class="style85">GUARANTEED 15 MINUTE RESPONSE TIME<br>DURING BUSINESS HOURS! <span class="style86">*</span></span></font></th></tr><tr><td><div class="redmessage">(<font style="color:red">*</font> Fields marked with  are required.)</div></td></tr><tr><td><table width="100%"><tbody><tr><td><fieldset><legend>Contact Information</legend><table width="100%" style="font-size: 13px;color:#4D4E4E" class="contact"><tbody><tr><td width="20%" class="contact_r"><label for="txtfirstname">First Name<font style="color:red;">*</font></label></td><td><input type="text" maxlength="250" style="width:172px" id="txtfirstname" name="data[User][first_name]"></td><td width="20%" class="contact_r"><label for="txtlastname">Last Name<font style="color:red;">*</font></label></td><td><input type="text" maxlength="250" style="width:172px" id="txtlastname" name="data[User][last_name]"></td></tr><tr><td width="20%" class="contact_r"><label for="txtaddress">Address</label></td><td><input type="text" style="width:172px" id="txtaddress" name="data[User][address]"></td><td width="20%" class="contact_r"><label for="txtcity">City</label></td><td><input type="text" maxlength="150" style="width:172px" id="txtcity" name="data[User][city]"></td></tr><tr><td width="20%" class="contact_r"><label for="txtstate">State</label></td><td><input type="text" maxlength="150" style="width:172px" id="txtstate" name="data[User][state]"></td><td width="20%" class="contact_r"><label for="txtzipcode">Zip code</label></td><td><input type="text" maxlength="55" style="width:172px" id="txtzipcode" name="data[User][zipcode]"></td></tr><tr><td width="20%" class="contact_r"><label for="txtphone">Phone<font style="color:red;">*</font></label></td><td><input type="text" maxlength="55" style="width:172px" id="txtphone" name="data[User][phone]"></td><td width="20%" class="contact_r"><label for="txtemail">Email<font style="color:red;">*</font></label></td><td><input type="text" maxlength="255" style="width:172px" id="txtemail" name="data[User][email]"></td></tr></tbody></table></fieldset></td></tr><tr><td><fieldset><legend>Property Information</legend><table width="100%" style="font-size: 13px;color:#4D4E4E" class="contact"><tbody><tr><td width="20%" class="contact_r"><div class="input select"><label for="num_of_bedrooms">Number of bedrooms </label></div></td><td><select style="width:168px" id="num_of_bedrooms" name="data[User][num_of_bedrooms]"><option value="0">Choose</option><option value="1">1+</option><option value="2">2+</option><option value="3">3+</option><option value="4">4+</option><option value="5">5+</option><option value="6">6+</option></select></td><td width="20%" class="contact_r"><div class="input select"><label for="num_of_bathrooms">Number of bathrooms</label></div></td><td><select style="width:168px" id="num_of_bathrooms" name="data[User][num_of_bathrooms]"><option value="0">Choose</option><option value="1">1+</option><option value="2">2+</option><option value="3">3+</option><option value="4">4+</option><option value="5">5+</option><option value="6">6+</option></select></td></tr><tr><td width="20%" class="contact_r"><div class="input select"><label for="square_feet">Square Feet</label></div></td><td><select style="width:168px" id="square_feet" name="data[User][square_feet]"><option value="0">Choose</option><option value="1">&lt; 1000</option><option value="2">1000 - 1500</option><option value="3">1500 - 2000</option><option value="4">2000 - 2500</option><option value="5">2500 - 3000</option><option value="6">3000 - 3500</option><option value="7">3500 - 4000</option><option value="8">4000 - 4500</option><option value="9">5000 - 6000</option><option value="10">7000 - 7000</option><option value="11">8000 - 8000</option><option value="12">9000 - 9000</option><option value="13">10000+</option></select></td><td width="20%" class="contact_r"><div class="input select"><label for="price_range">Price Ranges</label></div></td><td><select style="width:168px" id="price_range" name="data[User][price_range]"><option value="0">Choose</option><option value="$100,000 - $150,000">$100,000 - $150,000</option><option value="$150,000 - $200,000">$150,000 - $200,000</option><option value="$200,000 - $250,000">$200,000 - $250,000</option><option value="$250,000 - $300,000">$250,000 - $300,000</option><option value="$300,000 - $350,000">$300,000 - $350,000</option><option value="$350,000 - $400,000">$350,000 - $400,000</option><option value="$400,000 - $450,000">$400,000 - $450,000</option><option value="$450,000 - $500,000">$450,000 - $500,000</option><option value="$500,000 - $600,000">$500,000 - $600,000</option><option value="$600,000 - $700,000">$600,000 - $700,000</option><option value="$700,000 - $800,000">$700,000 - $800,000</option><option value="$800,000 - $900,000">$800,000 - $900,000</option><option value="$900,000 - $1,000,000">$900,000 - $1,000,000</option><option value="$1,000,000 +">$1,000,000 +</option></select></td></tr></tbody></table></fieldset></td></tr><tr><td><fieldset><legend>Moving details</legend><table width="100%" style="font-size: 13px;color:#4D4E4E" class="contact"><tbody><tr><td width="50%" class="contact_r"><label for="when_to_move">When do you want to move?<font style="color:red;">*</font></label></td><td><select style="width:150px" id="when_to_move" name="data[User][when_to_move]"><option value="0">Choose</option><option value="1">less then 30 days</option><option value="2">1 month</option><option value="3">2 months</option><option value="4">3 months</option><option value="5">4 months</option><option value="6">5 months</option><option value="7">6 months</option><option value="8">7 months</option><option value="9">8 months</option><option value="10">9 months</option><option value="11">10 months</option><option value="12">11 months</option><option value="13">12 months +</option></select></td></tr><tr><td width="50%" class="contact_r"><label for="how_long_looking">How long have you been looking?<font style="color:red;">*</font></label></td><td><select style="width:150px" id="how_long_looking" name="data[User][how_long_looking]"><option value="0">Choose</option><option value="1">less then 30 days</option><option value="2">1 month</option><option value="3">2 months</option><option value="4">3 months</option><option value="5">4 months</option></select></td></tr><tr><td width="50%" class="contact_r"><label for="is_with_agent">Are you currently working with an agent?<font style="color:red;">*</font></label></td><td><select style="width:150px" id="is_with_agent" name="data[User][is_with_agent]"><option value="0">Choose</option><option value="Yes">Yes</option><option value="No">No</option></select></td></tr><tr><td width="50%" class="contact_r"><label for="prequalified">Have you been prequalified?<font style="color:red;">*</font></label></td><td><select style="width:150px" id="prequalified" name="data[User][prequalified]"><option value="0">Choose</option><option value="Yes">Yes</option><option value="No">No</option></select></td></tr></tbody></table></fieldset></td></tr><tr><td><fieldset><legend>Additional Information</legend><table width="100%"><tbody><tr><td>Describe your perfect Las Vegas  Real estate home for me!</td></tr><tr><td><textarea id="UserComments" rows="3" cols="75" name="data[User][comments]"></textarea></td></tr><tr><td><input type="hidden" id="UserLeadData" value="Lead Page URL: {strleadformpageurl}" name="data[User][LeadData]">	<input type="hidden" value="1" id="static_page" name="data[User][static_page]">	<div class="submit"><input type="submit" value="Submit"></div></td></tr></tbody></table></fieldset> </td></tr></tbody></table></td></tr><tr><td><p class="style3"><span style="color:#FF0000;">*</span> MS Las Vegas Real Estate business hours are 8am - 9pm PST (Pacific Standard Time)</p></td></tr></tbody></table></form><div class="c"></div><p>&nbsp;</p>';
				//'<p>&nbsp;</p><div class="callnow"><h1><a href="http://www.ringcentral.com" target="Callback_RingMe" onclick=\'var wind = window; var winop = wind.open; winop("http://www.ringcentral.com/ringme/?uc=43546551,0,0&s=no&v=2", "Callback_RingMe", "resizable=no,width=380,height=240"); return false;\'><span>'+strtxttollfree+'</span></a><a href="http://www.ringcentral.com" target="Callback_RingMe" onclick=\'var wind = window; var winop = wind.open; winop("http://www.ringcentral.com/ringme/?uc=43546551,0,0&s=no&v=2", "Callback_RingMe", "resizable=no,width=380,height=240"); return false;\'>'+strtxtdirect+'</a></h1></div><div class="c"></div>';
				// finalstr='<p>&nbsp;</p><p style="text-align: center; " class="text2 hbba"></p><table id="leadformsnippet" sib="1" sib_propertytype="'+strcoPropertytypembo+'" sib_communityname="'+strcoCommunitymbo.replace(/ /g,"+")+'" sib_foreclosure="'+strforeclosure+'" sib_shortsale="'+strshortsale+'" sib_minprice="'+strtxtminprice+'" leadformsnippet='+leadformsnippet+' border="0" width="100%" style="text-align: center"><tbody><tr><td style="font-size:13px;font-weight:bold;"> {communitytitle} HOMES</td></tr><tr><td style="font-size:13px;font-weight:bold;">Please click the pictures below to see full details on these properties.</td></tr><tr><td align="center">{nodataixidxbox}<table cellspacing="0" cellpadding="0" bordercolor="#c0c0c0" border="0" bgcolor="#eeeeee" width="40%" id="table86" class="fullsixidx_'+leadformsnippet+'"><tbody><tr><td align="center" style="border:1px solid #222222;" id="showhidediv1"><a href="{link1}"><img border="0" width="160" height="125" src="/resize_image.php?image=img/nohome.png"></a><table width="100%"><tbody><tr><td style="width:70%;font-size:10px;">{communityname1} Homes</td><td align="right" style="width:30%"></td></tr><tr><td align="left"><a style="font-size:12px;color:#C86914" href="{link1}">{price1}</a></td><td align="right"><a style="font-size:12px;color:#C86914" href="{link1}">MLS#{mls_num1}</a></td></tr></tbody></table></td><td style="" id="showhidedivblank1_'+leadformsnippet+'"></td><td align="center" style="border:1px solid #222222;" id="showhidediv2_'+leadformsnippet+'"><a href="{link2}"><img border="0" width="160" height="125" src="/resize_image.php?image=img/nohome.png"></a><table width="100%"><tbody><tr><td style="width:70%;font-size:10px;">{communityname2} Homes</td><td align="right" style="width:30%"></td></tr><tr><td align="left"><a style="font-size:12px;color:#C86914" href="{link2}">  {price2}</a></td><td align="right"><a style="font-size:12px;color:#C86914" href="{link2}">MLS#{mls_num2}</a></td></tr></tbody></table></td><td style="" id="showhidedivblank2_'+leadformsnippet+'"></td><td align="center" style="border:1px solid #222222;" id="showhidediv3_'+leadformsnippet+'"><a href="{link3}"><img border="0" width="160" height="125" src="/resize_image.php?image=img/nohome.png"></a><table width="100%"><tbody><tr><td style="width:70%;font-size:10px;">{communityname3} Homes</td><td align="right" style="width:30%"></td></tr><tr><td align="left"><a style="font-size:12px;color:#C86914" href="{link3}">{price3}</a></td><td align="right"><a style="font-size:12px;color:#C86914" href="{link3}">MLS#{mls_num3}</a></td></tr></tbody></table></td><td style="" id="showhidedivblank3_'+leadformsnippet+'"></td></tr><tr><td align="center" style="border:1px solid #222222;" id="showhidediv4_'+leadformsnippet+'"><a href="{link4}"><img border="0" width="160" height="125" src="/resize_image.php?image=img/nohome.png"></a><table width="100%"><tbody><tr><td style="width:70%;font-size:10px;">{communityname4} Homes</td><td align="right" style="width:30%"></td></tr><tr><td align="left"><a style="font-size:12px;color:#C86914" href="{link4}">  {price4}</a></td><td align="right"><a style="font-size:12px;color:#C86914" href="{link4}">MLS#{mls_num4}</a></td></tr></tbody></table></td><td style="" id="showhidedivblank4_'+leadformsnippet+'"></td><td align="center" style="border:1px solid #222222;" id="showhidediv5_'+leadformsnippet+'"><a href="{link5}"><img border="0" width="160" height="125" src="/resize_image.php?image=img/nohome.png"></a><table width="100%"><tbody><tr><td style="width:70%;font-size:10px;">{communityname5} Homes</td><td align="right" style="width:30%"></td></tr><tr><td align="left"><a style="font-size:12px;color:#C86914" href="{link5}">  {price5}</a></td><td align="right"><a style="font-size:12px;color:#C86914" href="{link5}">MLS#{mls_num5}</a></td></tr></tbody></table></td><td style="" id="showhidedivblank5_'+leadformsnippet+'"></td><td align="center" style="border:1px solid #222222;" id="showhidediv6_'+leadformsnippet+'"><a href="{link6}"><img border="0" width="160" height="125" src="/resize_image.php?image=img/nohome.png"></a><table width="100%"><tbody><tr><td style="width:70%;font-size:10px;">{communityname6} Homes</td><td align="right" style="width:30%"></td></tr><tr><td align="left"><a style="font-size:12px;color:#C86914" href="{link6}">{price6}</a></td><td align="right"><a style="font-size:12px;color:#C86914" href="{link6}">MLS#{mls_num6}</a></td></tr></tbody></table></td><td style="" id="showhidedivblank6_'+leadformsnippet+'"></td></tr></tbody></table></td></tr></tbody></table><p style="text-align: center; " class="text2 hbba">&nbsp;</p><p>&nbsp;</p>';
				editor.insertHtml(finalleadformstr+'<div class="c">&nbsp;</div><p>&nbsp;</p>');
		}
	};
});