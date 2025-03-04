function ajax(url, callback) {
    var xhr = new window.XMLHttpRequest();
    xhr.open("GET", url + "?rel=page", true);

    // Show loading indicator
    document.querySelector("#load").innerHTML = "<p>Loading...</p>";

    xhr.onload = function () {
        if (xhr.readyState === xhr.DONE && (xhr.status >= 200 && xhr.status < 300)) {
            if (this.response) {
                callback.call(this, this.response);
            }
        }
    };

    xhr.send();
}

// Attach event listeners to links with rel="page"
var anchors = document.querySelectorAll("a[rel=page]");
[].slice.call(anchors).forEach(function (trigger) {
    trigger.addEventListener("click", function (e) {
        e.preventDefault();

        var pageUrl = this.getAttribute("href");

        ajax(pageUrl, function (data) {
            document.querySelector("#load").innerHTML = data;

            // Reinitialize JavaScript for new content (e.g., event listeners)
            initNewContent();

            // Push new state to the browser history
            if (pageUrl !== window.location.pathname) {
                window.history.pushState({ url: pageUrl }, '', pageUrl);
            }
        });

        return false;
    });
});

// Function to initialize JavaScript for newly loaded content
function initNewContent() {
    // Example: Re-initialize any event listeners or functionality specific to newly loaded content
    // This could include things like form validation, carousel initialization, etc.
    console.log("New content initialized!");
}

// Handle back/forward browser navigation
window.addEventListener("popstate", function () {
    ajax(window.location.pathname, function (data) {
        document.querySelector("#load").innerHTML = data;

        // Reinitialize JavaScript for new content
        initNewContent();
    });
});
