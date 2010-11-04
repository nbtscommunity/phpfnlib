function updatetrigger() {
//	alert("setting timer")
	if(window.frames && document.getElementsByTagName) {
		setTimeout("stage1()", 5000)
	}
}

function stage1() {
	iframe = document.createElement('iframe')
	iframe.src = document.URL + '/one'
	iframe.style.height = 0
	iframe.style.width = 0
	iframe.style.borderWidth = 0
	iframe.id = 'updateframe'
	document.documentElement.appendChild(iframe)

	window.frames[window.frames.length - 1].onload = stage2

}

function stage2() {
	source = window.frames[window.frames.length - 1].document.getElementsByTagName('ul')[0]

	if(source) {
		ul = document.getElementsByTagName('ul')[0]
		ul.innerHTML = source.innerHTML
		
	}
	
	iframes = document.getElementsByTagName('iframe')
//	alert(iframes.length)
	for(i = 0; i < iframes.length; i++) {
		iframes[i].parentNode.removeChild(iframes[i])
	}

	updatetrigger()
}

window.onload = updatetrigger
