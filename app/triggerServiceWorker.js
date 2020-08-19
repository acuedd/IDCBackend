if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('service-worker.js').then(function(registration) {
            console.log('ServiceWorker registration successful with scope: ', registration.scope);
        }, function(err) {
            console.log('ServiceWorker registration failed: ', err);
        });
    });
}

let deferredPrompt;
let div = document.querySelector('.add-to-app');
let button = document.querySelector('.add-to-screen');
div.style.display = 'none';

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    div.style.display = 'block';

    button.addEventListener('click', (e) => {
        div.style.display = 'none';
        deferredPrompt.prompt();
        deferredPrompt.userChoice
            .then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('User accepted the A2HS prompt');
                }
                else {
                    console.log('User dismissed the A2HS prompt');
                }
                deferredPrompt = null;
            });
    });
});