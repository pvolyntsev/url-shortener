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

function onUrlFormSubmit() {
    var form = document.getElementById("form-url");
    form.onsubmit = function(e) {
        e = e||window.event;
        e.cancelBubble = true;
        e.preventDefault();

        var xhr = createXHR();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                try {
                    var response = {};
                    eval("response = " + xhr.responseText);

                    var messagesDiv = document.getElementById('url-messages');
                    messagesDiv.innerHTML = '';

                    if (response.content||false && response.content.form||false) {
                        if (response.content.form.messages||false && response.content.form.messages.url) {
                            for(j in response.content.form.messages.url) {
                                var msg = response.content.form.messages.url[j];
                                var msgNode = document.createElement('div');
                                msgNode.setAttribute('class', 'error');
                                msgNode.innerHTML = msg;
                                messagesDiv.appendChild(msgNode);
                            }
                        }
                        if (response.content.form.values||false) {
                            document.getElementById('url').value = response.content.form.values.url||'';
                            document.getElementById('short-url').innerHTML = response.content.form.values.shortUrl||'';
                        }
                    }

                } catch(e) {
                    alert(e);
                    alert(xhr.responseText);
                }
            }
        }
        xhr.open('POST', '/', false)
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.send('url=' + encodeURIComponent(document.getElementById('url').value));

        return false;
    }
}
