function paragraphDblClick(e) {
	els = e.target.getElementsByTagName("a")
	for(i = 0; i < els.length; i++) {
		if(els[i].className == "reply") {
			if(els[i].style.display == 'inline') {
				els[i].style.display = 'none';
			} else {
				els[i].style.display = 'inline'
			}
		}
	}
}

function paragraphDblClickIE() {
	els = window.event.srcElement.getElementsByTagName("a")
	for(i = 0; i < els.length; i++) {
		if(els[i].className == "reply") {
			if(els[i].style.display == 'inline') {
				els[i].style.display = 'none';
			} else {
				els[i].style.display = 'inline'
			}
		}
	}
}

function start() {
	addReplyLinksToElements(document.getElementsByTagName("p"))
	addReplyLinksToElements(document.getElementsByTagName("ul"))
}

function addReplyLinksToElements(elements) {
	/* Add event handler to all top-level nodes */
	if(!document.all) {
		for(i = 0; i < elements.length; i++) {
			elements[i].addEventListener("dblclick", paragraphDblClick, false);
		}
	} else {
		document.ondblclick = paragraphDblClickIE;
	}
}

window.onload = start
