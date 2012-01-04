$(document).ready(function() { 
	//$('.excol').parent('li').addClass('collapsed');
	
	$('.excol').click(function() {
		
		if($(this).parent('li').eq(0).hasClass('collapsed') == true) {
			$(this).parent('li').eq(0).removeClass('collapsed');
		} else {
			$('.excol').parent('li').eq(0).addClass('collapsed');
		}
	});
   
	
	/*
	(function(){
	function toggle(e) {
		if (e.which != 1) return;

		if (e.target.className.indexOf("excol") !== -1) {
			e.target.parentNode.className = e.target.parentNode.className.replace(/\bexpanded\b|\bcollapsed\b/, function(m) {
				return m == "collapsed" ? "expanded" : "collapsed";
			});
		}
	}
	document.addEventListener("click", toggle, false);
})();
*/

});