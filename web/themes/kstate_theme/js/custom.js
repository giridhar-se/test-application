function triggerEvent( elem, event ) {
	var simulateClick = new MouseEvent(event, {
		bubbles: false,
		cancelable: true,
		view: window,
  });
	elem.dispatchEvent( simulateClick );    // Dispatch the event.
 }

 function delay(time) {
	return new Promise(resolve => setTimeout(resolve, time));
 }

function horizontalScrollingModePDF() {
	let iframe = document.querySelector('.pdf');
	if(iframe) {
		iframe.addEventListener("load", function() {
			let elmt = this.contentWindow.document.getElementById('scrollVertical');
			if(elmt) {
				delay(2000).then(() => triggerEvent(elmt, 'click'));
			}
		});
	}
}

document.addEventListener('DOMContentLoaded', (event) => {
	console.log('DOM fully loaded and parsed');
	horizontalScrollingModePDF();
 });