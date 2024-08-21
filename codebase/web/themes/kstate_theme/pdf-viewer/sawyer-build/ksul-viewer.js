pdfjsLib.GlobalWorkerOptions.workerSrc = '/libraries/pdf.js/build/pdf.worker.js';

let scale = 1.0; // Default scale for PDF rendering

const urlParams = new URLSearchParams(window.location.search);
const pdfUrl = urlParams.get('file');
let pdfDoc = null;

function renderPage(num) {
    return pdfDoc.getPage(num).then(function(page) {
        const viewport = page.getViewport({ scale });
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');

        canvas.height = viewport.height;
        canvas.width = viewport.width;

        const renderContext = {
            canvasContext: context,
            viewport: viewport
        };

        return page.render(renderContext).promise.then(() => {
            const pageDiv = document.createElement('div');
            pageDiv.className = 'page';
            pageDiv.setAttribute('data-page-number', num); // Track the page number
            pageDiv.appendChild(canvas);
            return pageDiv;
        });
    });
}

function loadPdf(pdfUrl) {
    document.getElementById('flipbook').style.display = 'none';

    let loadingTask = pdfjsLib.getDocument({ url: pdfUrl });

    loadingTask.onProgress = function(progressData) {
        let percent = (progressData.loaded / progressData.total) * 100;
        document.getElementById('loading-percentage').textContent = Math.round(percent) + '%';
        document.getElementById('pdf-load-progress').value = percent;
    };

    loadingTask.promise.then(function(pdfDoc_) {
        pdfDoc = pdfDoc_;
        document.getElementById('page-count').textContent = pdfDoc.numPages;

        let flipbook = document.getElementById('flipbook');

        // Loop through each page and add to the flipbook in the correct order
        let pagePromises = [];
        for (let i = 1; i <= pdfDoc.numPages; i++) {
            pagePromises.push(renderPage(i).then(pageDiv => {
                flipbook.appendChild(pageDiv);
            }));
        }

        // Ensure all pages are rendered before sorting and initializing Turn.js
        Promise.all(pagePromises).then(() => {
            // Reorder pages based on data-page-number
            const pagesArray = Array.from(flipbook.querySelectorAll('.page'));
            pagesArray.sort((a, b) => parseInt(a.getAttribute('data-page-number')) - parseInt(b.getAttribute('data-page-number')));

            // Clear the flipbook container and append sorted pages
            flipbook.innerHTML = '';
            pagesArray.forEach(pageDiv => {
                flipbook.appendChild(pageDiv);
            });

            // Initialize Turn.js after all pages are sorted and added
            $('#flipbook').turn({
                width: '100%',
                height: 850,
                autoCenter: true,
                display: 'double', // Display as a double-page book
                acceleration: true,
                gradients: true,
                duration: 1000,
                when: {
                    turned: function(event, page, view) {
                        console.log(`Page turned to ${page}.`);
                        document.getElementById('page-num').innerText = "Page " + page;
                        // Assign the appropriate classes for shadow effects
                        $('.page').removeClass('left right');
                        $('.page:nth-child(even)').addClass('right');
                        $('.page:nth-child(odd)').addClass('left');
                    },
                    missing: function(event, pages) {
                        console.warn("Missing pages detected:", pages);
                    }
                }
            });
            // Assign initial classes
            $('.page:nth-child(even)').addClass('right');
            $('.page:nth-child(odd)').addClass('left');

            hideLoadingIndicator();
            document.getElementById('flipbook').style.display = 'block';
        });

    }).catch(function(error) {
        hideLoadingIndicator();
        console.error("Error loading PDF: ", error);
        alert("Failed to load the PDF document.");
    });
}

function showLoadingIndicator() {
    document.getElementById('loading-indicator').style.display = 'block';
    document.getElementById('pdf-load-progress').value = 0; // Reset progress
    document.getElementById('loading-percentage').textContent = '0%'; // Reset text
}

function hideLoadingIndicator() {
    document.getElementById('loading-indicator').style.display = 'none';
}

document.getElementById('prev-page').addEventListener('click', function() {
    $('#flipbook').turn('previous');
});

document.getElementById('next-page').addEventListener('click', function() {
    $('#flipbook').turn('next');
});

document.getElementById('first-page').addEventListener('click', function() {
    $('#flipbook').turn('page', 1);
});

document.getElementById('last-page').addEventListener('click', function() {
    $('#flipbook').turn('page', Math.ceil(pdfDoc.numPages / 2));
});

showLoadingIndicator();
loadPdf(pdfUrl);
