$(function()
{
	$articleEditor = $('#article-editor');
	$articleValue = $('#article-editor-value');

	$postEditor = $('#post-editor');
	$postPreview = $('#post-preview');

	$imageLink = $('#image-link');
	$postImage = $('#post-editor-image');

	init();

	$postEditor.on('keyup keydown', updatePreview);

	$imageLink.on('keyup keydown', updateImage);

	$('#editor-button').click(function()
	{
	  setData($articleEditor, formBody);
	});


	function init()
	{

		$articleEditor.trumbowyg(
		{
	    removeformatPasted: true
		});	

		$articleEditor.trumbowyg.btnsDef = 
		{

		};

		$articleEditor.trumbowyg.btns =
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

		$articleEditor.trumbowyg();

		getData($articleEditor, $articleValue);

		updatePreview();

		updateImage();
	}

	function getData(editor, formControl)
	{
		let html = formControl.val();
		editor.trumbowyg('html', html);
	}

	function setData(editor, formControl)
	{
		let value = editor.trumbowyg('html');
	  formControl.val(value);
	}

	function updatePreview()
	{
		let html = $postEditor.val();
		console.log(html);
		$postPreview.html(html);
	}

	function updateImage()
	{
		let href = $imageLink.val();
		$postImage.attr('src', href);
	}
});