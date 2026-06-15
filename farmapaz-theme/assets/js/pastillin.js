(function () {
    'use strict';

    var chatData = typeof farmapazData !== 'undefined' ? farmapazData : { ajaxUrl: '/wp-admin/admin-ajax.php' };
    var chatBtn = document.getElementById('pastillin-btn');
    var chatBox = document.getElementById('pastillin-box');
    var chatMessages = document.getElementById('pastillin-msgs');
    var chatInput = document.getElementById('pastillin-input');
    var chatSend = document.getElementById('pastillin-send');
    var chatClose = document.getElementById('pastillin-close');
    var chatQuickBar = document.getElementById('pastillin-quick-bar');
    var isOpen = false;

    if (!chatBtn || !chatBox) return;

    var avatarSrc = '';
    var headerAvatar = document.querySelector('.past-header-avatar img');
    if (headerAvatar) avatarSrc = headerAvatar.src;

    var quickReplies = [
        'Que productos venden',
        'Buscar medicamento',
        'Horarios y sucursales',
        'Formas de pago',
        'Delivery gratis',
    ];

    // ====== CHAT PERSISTENCE (localStorage) ======
    var STORAGE_KEY = 'pastillin_chat';

    function saveMessages() {
        var items = [];
        var msgs = chatMessages.querySelectorAll('.past-msg:not(.past-typing)');
        msgs.forEach(function (el) {
            var isUser = el.classList.contains('past-msg-user');
            var textEl = el.querySelector('.past-msg-text');
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
                msg.className = item.isUser ? 'past-msg past-msg-user' : 'past-msg past-msg-bot';
                if (!item.isUser) {
                    msg.innerHTML =
                        avatarHTML() +
                        '<div class="past-msg-content"><div class="past-msg-text">' + item.text + '</div></div>';
                } else {
                    msg.innerHTML = '<div class="past-msg-content past-msg-user-content"><div class="past-msg-text">' + item.text + '</div></div>';
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
            ? '<div class="past-msg-avatar"><img src="' + avatarSrc + '" alt="Pastillín"></div>'
            : '<div class="past-msg-avatar"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12h6m-3-3v6m-7 4l2-2m14 2l-2-2M12 21a9 9 0 100-18 9 9 0 000 18z"/></svg></div>';
    }

    function showWelcome() {
        // Don't show welcome if chat has history
        if (chatMessages.children.length > 1) return;
        var msg = document.createElement('div');
        msg.className = 'past-msg past-msg-bot';
        msg.innerHTML =
            avatarHTML() +
            '<div class="past-msg-content"><div class="past-msg-text">Hola! Soy <strong>Pastillín</strong>, tu asistente de Farmapaz. En que puedo ayudarte?</div><div class="past-quick-replies"></div></div>';
        chatMessages.appendChild(msg);
        var qrContainer = msg.querySelector('.past-quick-replies');
        quickReplies.forEach(function (text) {
            var btn = document.createElement('button');
            btn.className = 'past-quick-btn';
            btn.textContent = text;
            btn.addEventListener('click', function () { sendMessage(text); });
            qrContainer.appendChild(btn);
        });
        scrollDown();
    }

    function addMessage(text, isUser) {
        var msg = document.createElement('div');
        msg.className = isUser ? 'past-msg past-msg-user' : 'past-msg past-msg-bot';
        if (!isUser) {
            msg.innerHTML =
                avatarHTML() +
                '<div class="past-msg-content"><div class="past-msg-text">' + formatText(text) + '</div></div>';
        } else {
            msg.innerHTML = '<div class="past-msg-content past-msg-user-content"><div class="past-msg-text">' + escapeHtml(text) + '</div></div>';
        }
        chatMessages.appendChild(msg);
        scrollDown();
        saveMessages();
    }

    function addTyping() {
        var msg = document.createElement('div');
        msg.className = 'past-msg past-msg-bot past-typing';
        msg.id = 'past-typing';
        msg.innerHTML =
            avatarHTML() +
            '<div class="past-msg-content"><div class="past-typing-dots"><span></span><span></span><span></span></div></div>';
        chatMessages.appendChild(msg);
        scrollDown();
    }

    function removeTyping() {
        var el = document.getElementById('past-typing');
        if (el) el.remove();
    }

    function getHistory() {
        var items = [];
        var msgs = chatMessages.querySelectorAll('.past-msg:not(.past-typing)');
        msgs.forEach(function (el) {
            var isUser = el.classList.contains('past-msg-user');
            var textEl = el.querySelector('.past-msg-text');
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
        fd.append('action', 'farmapaz_pastillin');
        fd.append('message', text);
        fd.append('history', JSON.stringify(history));
        fd.append('nonce', chatData.nonce || '');

        fetch(chatData.ajaxUrl, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var elapsed = Date.now() - t0;
                var delay = Math.max(0, 600 - elapsed);
                setTimeout(function () {
                    removeTyping();
                    addMessage(data.reply || 'Ups! Algo salio mal. Intenta de nuevo.', false);
                }, delay);
            })
            .catch(function () {
                removeTyping();
                addMessage('Ups! Problema de conexion. Quieres hablar por WhatsApp?', false);
            });
    }

    // [†] Renderiza el texto del bot:
    //     1. Markdown **bold** → <strong>
    //     2. [texto](url) → <a> (productos, categorías, marcas)
    //     3. URLs desnudas (WhatsApp, etc.) → <a> (regex con (^|[\s>]) evita duplicar href=)
    function formatText(text) {
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener" class="past-link">$1</a>')
            .replace(/\n/g, '<br>')
            .replace(/(^|[\s>])(https?:\/\/[^\s<]+)/g, '$1<a href="$2" target="_blank" rel="noopener" class="past-link">$2</a>');
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
            btn.className = 'past-quick-bar-btn';
            btn.textContent = text;
            btn.addEventListener('click', function () { sendMessage(text); });
            chatQuickBar.appendChild(btn);
        });
    }

    function scrollDown() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
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
