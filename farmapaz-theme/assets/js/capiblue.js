(function () {
    'use strict';

    var chatData = typeof farmapazData !== 'undefined' ? farmapazData : { ajaxUrl: '/wp-admin/admin-ajax.php' };
    var chatBtn = document.getElementById('capiblue-btn');
    var chatBox = document.getElementById('capiblue-box');
    var chatMessages = document.getElementById('capiblue-msgs');
    var chatInput = document.getElementById('capiblue-input');
    var chatSend = document.getElementById('capiblue-send');
    var chatClose = document.getElementById('capiblue-close');
    var chatQuickBar = document.getElementById('capiblue-quick-bar');
    var isOpen = false;

    if (!chatBtn || !chatBox) return;

    var avatarSrc = '';
    var headerAvatar = document.querySelector('.capiblue-header-avatar img');
    if (headerAvatar) avatarSrc = headerAvatar.src;

    var quickReplies = [
        'Que productos venden',
        'Buscar medicamento',
        'Óptica y monturas',
        'Examen de la vista',
        'Cashea y cuotas',
        'Horarios y sucursales',
        'Formas de pago',
        'Delivery gratis',
    ];

    // ====== CHAT PERSISTENCE (localStorage) ======
    var STORAGE_KEY = 'capiblue_chat';

    function saveMessages() {
        var items = [];
        var msgs = chatMessages.querySelectorAll('.capiblue-msg:not(.capiblue-typing)');
        msgs.forEach(function (el) {
            var isUser = el.classList.contains('capiblue-msg-user');
            var textEl = el.querySelector('.capiblue-msg-text');
            if (textEl) {
                items.push({ text: textEl.innerHTML, isUser: isUser });
            }
        });
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(items)); } catch (e) {}
    }

    function restoreMessages() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return false;
            var items = JSON.parse(raw);
            if (!Array.isArray(items) || items.length === 0) return false;
            chatMessages.innerHTML = '';
            items.forEach(function (item) {
                var msg = document.createElement('div');
                msg.className = item.isUser ? 'capiblue-msg capiblue-msg-user' : 'capiblue-msg capiblue-msg-bot';
                if (!item.isUser) {
                    msg.innerHTML =
                        avatarHTML() +
                        '<div class="capiblue-msg-content"><div class="capiblue-msg-text">' + item.text + '</div></div>';
                } else {
                    msg.innerHTML = '<div class="capiblue-msg-content capiblue-msg-user-content"><div class="capiblue-msg-text">' + item.text + '</div></div>';
                }
                chatMessages.appendChild(msg);
            });
            scrollDown();
            return true;
        } catch (e) {
            return false;
        }
    }

    function clearHistory() {
        try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
    }

    // ====== TOGGLE ======
    function toggleChat(e) {
        e.stopPropagation();
        isOpen = !isOpen;
        chatBox.classList.toggle('open', isOpen);
        chatBtn.classList.toggle('active', isOpen);
        if (isOpen) {
            renderQuickBar();
            var hasHistory = restoreMessages();
            if (!hasHistory && chatMessages.children.length <= 1) {
                showWelcome();
            }
            setTimeout(function () { chatInput.focus(); }, 400);
        }
    }

    function avatarHTML() {
        return avatarSrc
            ? '<div class="capiblue-msg-avatar"><img src="' + avatarSrc + '" alt="CapiBlue"></div>'
            : '<div class="capiblue-msg-avatar"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12h6m-3-3v6m-7 4l2-2m14 2l-2-2M12 21a9 9 0 100-18 9 9 0 000 18z"/></svg></div>';
    }

    function showWelcome() {
        // Don't show welcome if chat has history
        if (chatMessages.children.length > 1) return;
        var msg = document.createElement('div');
        msg.className = 'capiblue-msg capiblue-msg-bot';
        msg.innerHTML =
            avatarHTML() +
            '<div class="capiblue-msg-content"><div class="capiblue-msg-text">Hola! Soy <strong>CapiBlue</strong>, tu asistente de Farmapaz. En que puedo ayudarte?</div><div class="capiblue-quick-replies"></div></div>';
        chatMessages.appendChild(msg);
        var qrContainer = msg.querySelector('.capiblue-quick-replies');
        quickReplies.forEach(function (text) {
            var btn = document.createElement('button');
            btn.className = 'capiblue-quick-btn';
            btn.textContent = text;
            btn.addEventListener('click', function () { sendMessage(text); });
            qrContainer.appendChild(btn);
        });
        scrollDown();
    }

    function addMessage(text, isUser) {
        var msg = document.createElement('div');
        msg.className = isUser ? 'capiblue-msg capiblue-msg-user' : 'capiblue-msg capiblue-msg-bot';
        if (!isUser) {
            msg.innerHTML =
                avatarHTML() +
                '<div class="capiblue-msg-content"><div class="capiblue-msg-text">' + formatText(text) + '</div></div>';
        } else {
            msg.innerHTML = '<div class="capiblue-msg-content capiblue-msg-user-content"><div class="capiblue-msg-text">' + escapeHtml(text) + '</div></div>';
        }
        chatMessages.appendChild(msg);
        scrollDown();
        saveMessages();
    }

    function addTyping() {
        var msg = document.createElement('div');
        msg.className = 'capiblue-msg capiblue-msg-bot capiblue-typing';
        msg.id = 'capiblue-typing';
        msg.innerHTML =
            avatarHTML() +
            '<div class="capiblue-msg-content"><div class="capiblue-typing-dots"><span></span><span></span><span></span></div></div>';
        chatMessages.appendChild(msg);
        scrollDown();
    }

    function removeTyping() {
        var el = document.getElementById('capiblue-typing');
        if (el) el.remove();
    }

    function getHistory() {
        var items = [];
        var msgs = chatMessages.querySelectorAll('.capiblue-msg:not(.capiblue-typing)');
        msgs.forEach(function (el) {
            var isUser = el.classList.contains('capiblue-msg-user');
            var textEl = el.querySelector('.capiblue-msg-text');
            if (textEl) {
                items.push({ role: isUser ? 'user' : 'assistant', text: textEl.textContent.trim() });
            }
        });
        return items.slice(-6); // keep last 6 messages for context
    }

    function sendMessage(text) {
        if (!text || !text.trim()) return;
        text = text.trim();
        // Capture history BEFORE adding the current message (server already receives message separately)
        var history = getHistory();
        addMessage(text, true);
        chatInput.value = '';
        chatInput.style.height = 'auto';
        var t0 = Date.now();
        addTyping();

        var fd = new FormData();
        fd.append('action', 'farmapaz_capiblue');
        fd.append('message', text);
        fd.append('history', JSON.stringify(history));
        fd.append('nonce', chatData.nonce || '');

        fetch(chatData.ajaxUrl, { method: 'POST', body: fd })
            .then(function (r) {
                return r.text().then(function (t) { return { ok: r.ok, status: r.status, text: t }; });
            })
            .then(function (res) {
                var data = null;
                try { data = JSON.parse(res.text); } catch (e) {}
                var elapsed = Date.now() - t0;
                var delay = Math.max(0, 600 - elapsed);
                setTimeout(function () {
                    removeTyping();
                    if (data && typeof data.reply === 'string' && data.reply) {
                        addMessage(data.reply, false);
                        return;
                    }
                    // Error real: solo para el desarrollador (consola + log del servidor)
                    var clean = res.text ? res.text.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim() : '(sin respuesta)';
                    console.error('[Farmito AJAX] HTTP ' + res.status + ':', clean.slice(0, 500));
                    if (chatData.ajaxUrl) {
                        var lf = new FormData();
                        lf.append('action', 'farmapaz_capiblue_log');
                        lf.append('status', res.status || '0');
                        lf.append('error', clean.slice(0, 500));
                        try { fetch(chatData.ajaxUrl, { method: 'POST', body: lf }); } catch (e2) {}
                    }
                    addMessage('Ups! Problema de conexión. ¿Quieres hablar por WhatsApp?', false);
                }, delay);
            })
            .catch(function (err) {
                removeTyping();
                console.error('[Farmito AJAX] fallo de red:', err);
                addMessage('Ups! Problema de conexión. ¿Quieres hablar por WhatsApp?', false);
            });
    }

    // [†] Renderiza el texto del bot:
    //     1. Markdown #encabezados, **bold**, _italic_, *italic*
    //     2. [texto](url) → <a> (productos, categorías, marcas)
    //     3. Viñetas (- / •) y URLs desnudas (WhatsApp, etc.)
    //     El escape (& < >) primero evita inyección de HTML y no rompe los <a> generados.
    function formatText(text) {
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/^#{1,6}\s*(.+)$/gm, '<strong>$1</strong>')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/(^|\s)_([^_\n]+)_(?=$|\s)/g, '$1<em>$2</em>')
            .replace(/(^|\s)\*([^*\n]+)\*(?=$|\s)/g, '$1<em>$2</em>')
            .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener" class="capiblue-link">$1</a>')
            .replace(/^\s*[-•]\s+/gm, '• ')
            .replace(/\n/g, '<br>')
            .replace(/(^|[\s>])(https?:\/\/[^\s<]+)/g, '$1<a href="$2" target="_blank" rel="noopener" class="capiblue-link">$2</a>');
    }

    function escapeHtml(text) {
        var d = document.createElement('div');
        d.textContent = text;
        return d.innerHTML.replace(/\n/g, '<br>');
    }

    function renderQuickBar() {
        if (!chatQuickBar) return;
        chatQuickBar.innerHTML = '';
        var shown = {};
        quickReplies.forEach(function (text) {
            var key = text.toLowerCase().trim();
            if (shown[key]) return;
            shown[key] = true;
            var btn = document.createElement('button');
            btn.className = 'capiblue-quick-bar-btn';
            btn.textContent = text;
            btn.addEventListener('click', function () { sendMessage(text); });
            chatQuickBar.appendChild(btn);
        });
    }

    function scrollDown() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // [†] Mobile: keep the box pinned to the visual viewport so the keyboard
    // never covers the input and the chat doesn't jump when it opens.
    // top = vv.offsetTop + height = vv.height hace que la caja "sube" junto
    // con el teclado en vez de saltar. (fallback: CSS 100dvh)
    if (window.visualViewport) {
        var capiblueVv = window.visualViewport;
        function capiblueSizeBox() {
            if (window.innerWidth > 480) return;
            chatBox.style.top = (capiblueVv.offsetTop || 0) + 'px';
            chatBox.style.height = capiblueVv.height + 'px';
            chatBox.style.maxHeight = capiblueVv.height + 'px';
            chatBox.style.bottom = 'auto';
        }
        capiblueVv.addEventListener('resize', capiblueSizeBox);
        capiblueVv.addEventListener('scroll', capiblueSizeBox);
        capiblueSizeBox();
    }

    // Events
    chatBtn.addEventListener('click', toggleChat);
    if (chatClose) chatClose.addEventListener('click', toggleChat);

    chatSend.addEventListener('click', function () { sendMessage(chatInput.value); });
    chatInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage(chatInput.value);
        }
    });
    chatInput.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    document.addEventListener('click', function (e) {
        if (isOpen && !chatBox.contains(e.target) && !chatBtn.contains(e.target)) {
            toggleChat(e);
        }
    });
})();
