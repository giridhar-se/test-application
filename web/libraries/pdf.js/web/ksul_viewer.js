
pdfjsLib.GlobalWorkerOptions.workerSrc = '/libraries/pdf.js/build/pdf.worker.js';

let scale = 1.0; // Default scale for PDF rendering

const urlParams = new URLSearchParams(window.location.search);
const pdfUrl = urlParams.get('file');
let pdfDoc = null,
    pageNum = 1,
    canvasLeft = document.getElementById('pdf-canvas-left'),
    ctxLeft = canvasLeft.getContext('2d'),
    canvasRight = document.getElementById('pdf-canvas-right'),
    ctxRight = canvasRight.getContext('2d');

function renderPages(num) {
    showLoadingIndicator();
    
    pdfDoc.getPage(num).then(function(page) {
        // Render left page
        var viewport = page.getViewport({scale: scale});
        canvasLeft.height = viewport.height;
        canvasLeft.width = viewport.width;
        page.render({canvasContext: ctxLeft, viewport: viewport});
        hideLoadingIndicator();
        
        // Render right page if exists
        if (num + 1 <= pdfDoc.numPages) {
            pdfDoc.getPage(num + 1).then(function(page) {
                var viewport = page.getViewport({scale: scale});
                canvasRight.height = viewport.height;
                canvasRight.width = viewport.width;
                page.render({canvasContext: ctxRight, viewport: viewport});
                hideLoadingIndicator();
            });
        } else {
            hideLoadingIndicator();
        }
    });
}

function loadPdf(pdfUrl) {
    let loadingTask = pdfjsLib.getDocument({
        url: pdfUrl
    });

    loadingTask.onProgress  = function(progressData) {
        let percent = (progressData.loaded / progressData.total) * 100;
        document.getElementById('loading-percentage').textContent = Math.round(percent) + '%';
        document.getElementById('pdf-load-progress').value = percent;
    };

    loadingTask.promise.then(function(pdfDoc_) {
        pdfDoc = pdfDoc_;
        renderPages(1); // Start rendering from the first page
        hideLoadingIndicator();
        updatePageCount();
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
    if (pageNum <= 1) {
        return; // Already at the beginning
    }
    pageNum -= 2; // Go back two pages
    renderPages(pageNum);
    updatePageCount();
});

document.getElementById('next-page').addEventListener('click', function() {
    if (pageNum >= pdfDoc.numPages - 1) {
        return; // No more pages to display
    }
    pageNum += 2; // Advance two pages
    renderPages(pageNum);
    updatePageCount();
});

document.getElementById('first-page').addEventListener('click', function() {
    pageNum = 1;
    renderPages(pageNum);
    updatePageCount();
});

document.getElementById('last-page').addEventListener('click', function() {
    pageNum = pdfDoc.numPages - 1;
    renderPages(pageNum);
    updatePageCount();
});

document.getElementById('zoom-in').addEventListener('click', function() {
    scale *= 1.25; // Increase scale by 25%
    console.log("Scale: ", scale);
    renderPages(pageNum);
});

document.getElementById('zoom-out').addEventListener('click', function() {
    scale *= 0.8; // Decrease scale by 20%
    console.log("Scale: ", scale);
    renderPages(pageNum);
});

document.getElementById('current-page').addEventListener('change', function() {
    let requestedPage = parseInt(this.value);
    if (requestedPage >= 1 && requestedPage <= pdfDoc.numPages) {
        pageNum = requestedPage;
        renderPages(pageNum);
        updatePageCount();
    } else {
        this.value = pageNum; // Reset to current page if out of bounds
    }
});

function updatePageCount() {
    document.getElementById('page-num').textContent = "Pages: " + pageNum + " - " + (pageNum + 1);
    document.getElementById('page-count').textContent = pdfDoc.numPages;
    document.getElementById('current-page').value = pageNum; // Update the input field
}

showLoadingIndicator();
loadPdf(pdfUrl);