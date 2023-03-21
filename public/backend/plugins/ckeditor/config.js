/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	//config.uiColor = '#AADC6E';
	config.extraPlugins = 'sourcedialog';
	config.height = '500px';
	config.templates_replaceContent = false;
	config.toolbar_Basic =[['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink']];
	config.toolbar_Signature =[['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink'], [ 'TextColor', 'BGColor'],['Image']];
	config.toolbar_Email =[							
							['Bold', 'Italic','Underline','Strike','Subscript','Superscript','RemoveFormat','-', 'Link', 'Unlink'],['NumberedList', 'BulletedList','Outdent','Indent','Blockquote','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'], 
							['Cut','Copy','Paste','PasteText','PasteFromWord','Undo','Redo'],['Find','Replace','SelectAll','Scayt'],['TextColor','BGColor'],['Image'],
							['Styles','Format','Font','FontSize'],['Table','HorizontalRule','Smiley','SpecialChar'],
							['Preview','Print','-','Source']
						];
	config.toolbar_Full =[ ['Source','Save','NewPage','DocProps','Preview','Print','Templates','document'],['Cut','Copy','Paste','PasteText','PasteFromWord','Undo','Redo'],['Find','Replace','SelectAll','Scayt'],['Form','Checkbox','Radio','TextField','Textarea','Select','Button','ImageButton','HiddenField'],['Bold','Italic','Underline','Strike','Subscript','Superscript','RemoveFormat'],['NumberedList','BulletedList','Outdent','Indent','Blockquote','CreateDiv','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','BidiLtr','BidiRtl'],['Link','Unlink','Anchor'],['CreatePlaceholder','Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe','InsertPre'],['Styles','Format','Font','FontSize'],['TextColor','BGColor'],['UIColor','Maximize','ShowBlocks'],['button1','button2','button3','oembed','MediaEmbed']];
	config.editableFileContent = [
								{ name: 'document', groups: [ 'mode', 'document', 'doctools' ], items: [ 'Source', '-', 'Save', '-', 'Templates' ] },
								{ name: 'clipboard', groups: [ 'clipboard', 'undo' ], items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
								{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ], items: [ 'Find', 'Replace', '-', 'SelectAll', '-', 'Scayt' ] },
								{ name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
								{ name: 'paragraph', groups: [ 'list' ], items: [ 'NumberedList', 'BulletedList' ] },
								{ name: 'insert', items: [ 'Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe' ] },
								'/',
								{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },						    							    	
								{ name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
								{ name: 'colors', items: [ 'TextColor', 'BGColor'] },
								{ name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] }
							];
	//CK Finder
	config.filebrowserBrowseUrl = '/js/ckeditor/ckfinder/ckfinder.html';
	config.filebrowserImageBrowseUrl = '/js/ckeditor/ckfinder/ckfinder.html?type=Images';
	config.filebrowserUploadUrl = '/js/ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';
	config.filebrowserImageUploadUrl = '/js/ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images';
	//end

    //config.protectedSource.push(/<i[^>]*><\/i>/g);
    //config.extraAllowedContent = 'i(*)';

    $.each(CKEDITOR.dtd.$removeEmpty, function (i, value) {
        CKEDITOR.dtd.$removeEmpty[i] = false;
    });
};


// var fragment = oEditor.getSelection().getRanges()[0].extractContents()
