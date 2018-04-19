$(document).ready(function()
{
	let isSelected = false;

	$('#image-upload').change(function()
	{
		if (this.files && this.files[0]) 
		{
		  var reader = new FileReader();
	    reader.onload = function (e) 
	    {
	      $('#image-preview')
	        .attr('src', e.target.result)
	    };
	    reader.readAsDataURL(this.files[0]);
	  }
	});

	$('img#image-preview').on('load', function()
	{
		let size = 300;
		let x = ($(this).width()) / 2 - (size / 2);
		let y = ($(this).height()) / 2 - (size / 2);
		 
		$(this).imgAreaSelect(
		{
			x1: x, y1: y, x2: x + size, y2: y + size,
			maxWidth: 300,
			maxHeight: 300,
			minWidth: 300,
			minHeight: 300,
			persistent: true,
			show: true,
			resizable: false,
	    onSelectEnd: function(img, selection)
	    {
	    	$('input[name="rectX"]').val(selection.x1);
	    	$('input[name="rectY"]').val(selection.y1);
	    	isSelected = true;
	    }
	  });
	});
});