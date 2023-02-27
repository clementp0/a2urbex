if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
    navigator.serviceWorker.register('assets/js/sw.js').then(function (registration) {
    console.log('ServiceWorker registration successful with scope: ', registration.scope);
    }, function (err) {
    console.log('ServiceWorker registration failed: ', err);
    });
        });
}