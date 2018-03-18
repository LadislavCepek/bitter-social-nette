$(function()
{
	$editor = $('#editor');
	$editorBody = $('#editor-body');

	$('#editor-button').click(function()
	{
	  let value = $editor.trumbowyg('html');
	  $editorBody.val(value);
	});

	$editor.trumbowyg(
	{
    removeformatPasted: true
	});	

	$editor.trumbowyg.btnsDef = 
	{

	};

	$editor.trumbowyg.btns =
  [
    ['viewHTML'],
    ['undo', 'redo'], // Only supported in Blink browsers
    ['formatting'],
    ['strong', 'em', 'del'],
    ['superscript', 'subscript'],
    ['link'],
    ['insertImage'],
    ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
    ['unorderedList', 'orderedList'],
    ['horizontalRule'],
    ['removeformat'],
    
    ['indent']
  ]

	$editor.trumbowyg();

	let html = $editorBody.val();
	$editor.trumbowyg('html', html);
});