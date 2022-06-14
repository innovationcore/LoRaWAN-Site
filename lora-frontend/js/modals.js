function showNotification(msg, title) {
    Toastify({
        text: '<h6 class="text-black">' + title + '</h6><span class="text-black">' + msg + "</span>",
        backgroundColor: '#0dcaf0',
        close: true
    }).showToast();
}

function showSuccess(msg, title = 'Success') {
    Toastify({
        text: '<h6 class="text-white">' + title + '</h6><span class="text-white">' + msg + '</span>',
        backgroundColor: '#28a745',
        close: true
    }).showToast();
}

function showWarning(msg, title = 'Warning') {
    Toastify({
        text: '<h6 class="text-black">' + title + '</h6><span class="text-black">' + msg + '</span>',
        backgroundColor: '#ffc107',
        close: true
    }).showToast();
}

function showError(msg, title = 'Error') {
    Toastify({
        text: '<h6 class="text-white">' + title + '</h6>' + msg,
        backgroundColor: '#dc3545',
        close: true
    }).showToast();
}