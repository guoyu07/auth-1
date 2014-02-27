function setssss(db) {
	document.getElementById('snnav').innerHTML = db.nav;
	$(".upl").hover(function() {
		$("ul", this).show();
		$(this).addClass('ck');
	},
	function() {
		$("ul", this).hide();
		$(this).removeClass('ck');
	});
}
function showcategory(db) { 
	document.getElementById('side').innerHTML = db.side;
}
$.post(SYSTEMBUILDURL + '/category/ajaxgetcategory', {},
function(data) {
	showcategory(data);
	setssss(data);
});
