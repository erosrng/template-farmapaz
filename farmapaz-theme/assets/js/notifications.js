(function() {
    'use strict';

    var ONESIGNAL_APP_ID = typeof farmapazData !== 'undefined' ? farmapazData.onesignalAppId : '';
    if (!ONESIGNAL_APP_ID) return;

    var ASKED_KEY = 'farmapaz_notif_asked';
    var ACCEPTED_KEY = 'farmapaz_notif_accepted';

    function getCookie(name) {
        var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? decodeURIComponent(match[2]) : null;
    }

    function setCookie(name, value, days) {
        var d = new Date();
        d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + d.toUTCString() + '; path=/';
    }

    if (getCookie(ASKED_KEY) === 'no') return;

    var modalHTML = ''
        + '<div id="farmapaz-notif-overlay" class="fixed inset-0 z-[9999] flex items-center justify-center"'
        + ' style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">'
        + ' <div id="farmapaz-notif-modal" class="relative w-full max-w-sm mx-4 rounded-3xl shadow-2xl overflow-hidden"'
        + ' style="background: #fff; animation: farmapazNotifIn 0.5s cubic-bezier(0.16,1,0.3,1);">'
        + '  <button id="farmapaz-notif-close" class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all z-10" aria-label="Cerrar">'
        + '   <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>'
        + '  </button>'
        + '  <div class="pt-10 pb-6 px-8 text-center">'
        + '   <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center" style="background: linear-gradient(135deg, #5A7D43, #4a6a36);">'
        + '    <svg width="32" height="32" fill="none" stroke="#fff" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 11-6 0m6 0H9"/></svg>'
        + '   </div>'
        + '   <h2 class="text-xl font-bold text-gray-900 mb-2">¡No te pierdas nada!</h2>'
        + '   <p class="text-sm text-gray-500 leading-relaxed mb-6 px-2">'
        + '    Activa las notificaciones y recibe <strong class="text-brand-green">ofertas exclusivas</strong>, <strong class="text-brand-blue">promociones</strong> basadas en tus compras y las últimas <strong class="text-brand-orange">novedades</strong> de Farmapaz.'
        + '   </p>'
        + '   <div class="space-y-3">'
        + '    <button id="farmapaz-notif-accept" class="w-full py-3.5 px-6 rounded-2xl text-white font-semibold text-sm shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]"'
        + ' style="background: linear-gradient(135deg, #5A7D43, #4a6a36);">'
        + '     Sí, quiero recibir ofertas'
        + '    </button>'
        + '    <button id="farmapaz-notif-later" class="w-full py-3 px-6 rounded-2xl text-gray-500 hover:text-gray-700 font-medium text-sm hover:bg-gray-50 transition-all duration-200">'
        + '     Ahora no'
        + '    </button>'
        + '    <div class="pt-2">'
        + '     <label class="inline-flex items-center gap-2 cursor-pointer">'
        + '      <input type="checkbox" id="farmapaz-notif-dontask" class="w-4 h-4 rounded border-gray-300 text-brand-green focus:ring-brand-green">'
        + '      <span class="text-xs text-gray-400">No volver a preguntar</span>'
        + '     </label>'
        + '    </div>'
        + '   </div>'
        + '  </div>'
        + '  <div class="px-8 pb-6 flex items-center justify-center gap-4 text-xs text-gray-300">'
        + '   <span>Recibiras notificaciones importantes</span>'
        + '  </div>'
        + ' </div>'
        + '</div>';

    var style = document.createElement('style');
    style.textContent = ''
        + '@keyframes farmapazNotifIn {'
        + '  from { opacity: 0; transform: scale(0.92) translateY(20px); }'
        + '  to { opacity: 1; transform: scale(1) translateY(0); }'
        + '}'
        + '#farmapaz-notif-overlay { animation: farmapazNotifFadeIn 0.3s ease; }'
        + '@keyframes farmapazNotifFadeIn {'
        + '  from { opacity: 0; }'
        + '  to { opacity: 1; }'
        + '}';
    document.head.appendChild(style);

    function showModal() {
        var wrapper = document.createElement('div');
        wrapper.innerHTML = modalHTML;
        document.body.appendChild(wrapper.firstElementChild);

        var acceptBtn = document.getElementById('farmapaz-notif-accept');
        var laterBtn = document.getElementById('farmapaz-notif-later');
        var closeBtn = document.getElementById('farmapaz-notif-close');
        var dontAsk = document.getElementById('farmapaz-notif-dontask');
        var overlay = document.getElementById('farmapaz-notif-overlay');

        function dismiss() {
            if (dontAsk && dontAsk.checked) {
                setCookie(ASKED_KEY, 'no', 365);
            } else {
                setCookie(ASKED_KEY, 'later', 7);
            }
            if (overlay) overlay.remove();
        }

        function accept() {
            setCookie(ASKED_KEY, 'yes', 365);
            setCookie(ACCEPTED_KEY, 'yes', 365);
            if (overlay) overlay.remove();
            subscribeOneSignal();
        }

        if (acceptBtn) acceptBtn.addEventListener('click', accept);
        if (laterBtn) laterBtn.addEventListener('click', dismiss);
        if (closeBtn) closeBtn.addEventListener('click', dismiss);
        if (overlay) overlay.addEventListener('click', function(e) {
            if (e.target === overlay) dismiss();
        });
    }

    function subscribeOneSignal() {
        if (typeof window.OneSignalDeferred === 'undefined') return;
        window.OneSignalDeferred.push(function(OneSignal) {
            OneSignal.Notifications.requestPermission().then(function(permission) {
                if (permission === 'granted') {
                    OneSignal.User.getOnesignalId().then(function(onesignalId) {
                        if (onesignalId) {
                            setTags(onesignalId);
                        }
                    });
                }
            });
        });
    }

    function setTags(onesignalId) {
        var fd = new FormData();
        fd.append('action', 'farmapaz_notif_set_tags');
        fd.append('onesignal_id', onesignalId);
        fd.append('nonce', farmapazData.nonce);
        fetch(farmapazData.ajaxUrl, { method: 'POST', body: fd });
    }

    function initOneSignal() {
        if (getCookie(ASKED_KEY) === 'no') return;

        window.OneSignalDeferred = window.OneSignalDeferred || [];
        OneSignalDeferred.push(function(OneSignal) {
            OneSignal.init({
                appId: ONESIGNAL_APP_ID,
                serviceWorkerPath: '/OneSignalSDKWorker.js',
                serviceWorkerUpdaterPath: '/OneSignalSDKUpdaterWorker.js',
                notifyButton: { enable: false },
                allowLocalhostAsSecureOrigin: true,
                autoRegister: false,
            });

            OneSignal.Notifications.addEventListener('permissionChange', function(change) {
                if (change.to === 'granted') {
                    OneSignal.User.getOnesignalId().then(function(onesignalId) {
                        if (onesignalId) setTags(onesignalId);
                    });
                }
            });
        });

        var delay = parseInt(farmapazData.notifDelay) || 8000;
        var shown = getCookie(ASKED_KEY) === 'yes';
        if (shown) return;

        setTimeout(function() {
            if (getCookie(ASKED_KEY) === 'no') return;
            if (document.getElementById('farmapaz-notif-overlay')) return;
            if (typeof Notification !== 'undefined' && Notification.permission === 'granted') {
                setCookie(ASKED_KEY, 'yes', 365);
                setCookie(ACCEPTED_KEY, 'yes', 365);
                return;
            }
            showModal();
        }, delay);
    }

    function initOnUserInteraction() {
        var events = ['scroll', 'mousemove', 'touchstart', 'click'];
        var fired = false;
        function handler() {
            if (fired) return;
            fired = true;
            events.forEach(function(ev) {
                document.removeEventListener(ev, handler);
            });
            initOneSignal();
        }
        events.forEach(function(ev) {
            document.addEventListener(ev, handler, { once: true, passive: true });
        });
        setTimeout(handler, 10000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initOnUserInteraction);
    } else {
        initOnUserInteraction();
    }
})();
