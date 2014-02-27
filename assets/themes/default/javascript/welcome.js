$(document).ready( 
	function () { 
	var w=$('#mainbox').width();
	var bh=$(window).height();
	var pcharttask=$('#statistics').attr('lang')+'?w='+w+'&h='+(bh-95-225); 
	$('#statistics').attr('src',pcharttask); 
});