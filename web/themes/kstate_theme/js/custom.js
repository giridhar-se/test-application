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

	// General Search From JS
	var viewHeader = document.querySelector('.view-header');
    var formElements = document.querySelectorAll('.form-type-select');
    // Check if the viewHeader contains the word "Displaying"
    if (viewHeader.textContent.indexOf('Displaying') !== -1) {
        // If condition met, show the form elements
        formElements.forEach(function(element) {
            element.style.display = 'flex'; // Reset to default or use 'block', 'flex', etc., as appropriate
        });
        console.log('Search JS Triggered');
		// We may have search results, lets filter out the duplicate titles
		// FGSC Unique Titles Filter
		var seenTitles = new Set(); // To keep track of titles already encountered
		var rows = document.querySelectorAll('tr.item-list-fgsc'); // Select all rows with the class
		
		rows.forEach(function(row) {
			// Assuming the title is the text content of the first &amp;amp;amp;lt;td&amp;amp;amp;gt; in each row
			var title = row.querySelector('td').textContent.trim();
			if (seenTitles.has(title)) {
			// If this title has already been encountered, hide this row
			row.style.display = 'none';
			} else {
			// Otherwise, remember this title as encountered
			seenTitles.add(title);
			}
		});
		console.log('FGSC Filter Titles Search Results JS Triggered');
    }

	// ---FGSC Species Dropdown---
	const container = document.querySelector('.item-list-species') || document.querySelector('.item-list-strains');
	if (!container) return; // Exit if container is not found
	const list = container.querySelector('ul');
	const dropdown = container.querySelector('.species-select');
	list.style.display = 'none'; // Optionally hide the &amp;amp;amp;lt;ul&amp;amp;amp;gt; list
	dropdown.add(new Option("Select", "", true, true)); // Placeholder option
	const seenTexts = new Set(); // To track unique text values
	let linksData = [];
	
	// Collecting links data
	container.querySelectorAll('ul li a').forEach(function(link) {
	  const text = link.textContent.trim().replace('FGSC', '');
	  const href = link.getAttribute('href');
	  if (!seenTexts.has(text)) {
		seenTexts.add(text);
		linksData.push({ text, href });
	  }
	});
	// Sorting links data based on the numeric part of the text
	linksData.sort(function(a, b) {
	  const numA = parseInt(a.text.match(/\d+/), 10);
	  const numB = parseInt(b.text.match(/\d+/), 10);
	  return numA - numB;
	});
	// Adding sorted options to the dropdown
	linksData.forEach(function(item) {
	  const option = new Option(item.text, item.href);
	  dropdown.add(option);
	});
	container.style.display = 'block';
	// Initialize Select2 on the dropdown
	$(dropdown).select2({
	  placeholder: "Select",
	  allowClear: true,
	  width: '75%',
	  selectionCssClass: 'fgsc-dropdown'
	});
	dropdown.onchange = function() {
	  if (this.value) {
		window.location.href = this.value;
	  }
	};
	// ---End FGSC Species Dropdown---

	
 });