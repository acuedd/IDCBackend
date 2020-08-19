let deferredPrompt;
let dom_add = document.querySelector(`.add-screen`);
dom_add.style.display = 'none';

window.addEventListener('beforeinstallprompt', (e)=>{
    e.preventDefault();
    deferredPrompt = e;
    dom_add.style.display = 'block';

    deferredPrompt.prompt();
    deferredPrompt.userChoice().then((choiceResult)=>{
        if(choiceResult.outcome === 'accepted'){
            console.log('accepted');
        }
        else{
            console.log('not accepted');
        }
        deferredPrompt = null;
    })
})