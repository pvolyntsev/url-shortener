/**
 * The main idea is to realize AJAX requests and do not use of frameworks at all
 * So there is AJAX and no jQuery
 */
function createXHR() {
    var xhr;
    if (window.ActiveXObject) {
        try {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        } catch(e) {
            alert(e.message);
            xhr = null;
        }
    } else {
        xhr = new XMLHttpRequest();
    }
    return xhr;
}
