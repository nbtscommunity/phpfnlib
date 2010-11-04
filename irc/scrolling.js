<script language="javascript1.2"> var jscroll_new = 1; </script>
<script>
function fb(what) {
	var height = 100000;
	if (what.document.height)
		height = what.document.height - what.innerHeight + 35;
	if (jscroll_new) {
		what.scrollTo(0, height);
	} else {
		what.scroll(0, height);
	}
	window.setTimeout('timeout=0; fb(self);', 1000); 
}

fb(window);

</script>
