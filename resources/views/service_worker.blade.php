@if(config('const.SERVICE_WORKER_ENABLED'))
    <script>
        let initServiceWorker = function(){
            let worker;

            if ('serviceWorker' in navigator) {

                navigator.serviceWorker.register('/service-worker.js', {scope: '/'})
                    .then(function(registration) {

                        window.workerReg = registration
                        if (registration.waiting) {
                            worker = registration.waiting;
                        }
                        console.log('[Service worker] Registered', { reg: registration });

                        registration.onupdatefound = () => {
                            worker = registration.installing;
                            console.log('[Service worker] Update found', { reg: registration });

                            worker.onstatechange = () => {
                                console.log('[Service worker] State changed', { reg: registration });
                                if (worker.state === 'activated') {
                                    console.log('[Service worker] State is "activated". Reloading page.');
                                    window.location.reload(true)
                                }
                            };
                        };
                    });
            }
        }
        if (window.location.pathname === '/') {
            setTimeout(initServiceWorker, 1000 * 90);
        } else {
            initServiceWorker();
        }
    </script>
@endif
