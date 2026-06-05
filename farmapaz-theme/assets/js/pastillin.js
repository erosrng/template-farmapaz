(function () {
    'use strict';

    var chatData = typeof farmapazData !== 'undefined' ? farmapazData : { ajaxUrl: '/wp-admin/admin-ajax.php' };
    var chatBtn = document.getElementById('pastillin-btn');
    var chatBox = document.getElementById('pastillin-box');
    var chatMessages = document.getElementById('pastillin-msgs');
    var chatInput = document.getElementById('pastillin-input');
    var chatSend = document.getElementById('pastillin-send');
    var chatClose = document.getElementById('pastillin-close');
    var isOpen = false;

    if (!chatBtn || !chatBox) return;

    // Quick replies
    var quickReplies = [
        '¿Qué productos venden?',
        'Buscar medicamento',
        'Horarios y sucursales',
        'Formas de pago',
        'Delivery gratis',
    ];

    function toggleChat(e) {
        e.stopPropagation();
        isOpen = !isOpen;
        chatBox.classList.toggle('open', isOpen);
        chatBtn.classList.toggle('active', isOpen);
        if (isOpen && chatMessages.children.length <= 1) {
            showWelcome();
        }
        setTimeout(function () {
            if (isOpen) chatInput.focus();
        }, 400);
    }

    function showWelcome() {
        var msg = document.createElement('div');
        msg.className = 'past-msg past-msg-bot';
        msg.innerHTML =
            '<div class="past-msg-avatar"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12h6m-3-3v6m-7 4l2-2m14 2l-2-2M12 21a9 9 0 100-18 9 9 0 000 18z"/></svg></div>' +
            '<div class="past-msg-content"><div class="past-msg-text">¡Hola! Soy <strong>Pastillín</strong> 💊<br>Tu asistente de Farmapaz. ¿En qué puedo ayudarte?</div><div class="past-quick-replies"></div></div>';
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
                '<div class="past-msg-avatar"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12h6m-3-3v6m-7 4l2-2m14 2l-2-2M12 21a9 9 0 100-18 9 9 0 000 18z"/></svg></div>' +
                '<div class="past-msg-content"><div class="past-msg-text">' + formatText(text) + '</div></div>';
        } else {
            msg.innerHTML = '<div class="past-msg-content past-msg-user-content"><div class="past-msg-text">' + escapeHtml(text) + '</div></div>';
        }
        chatMessages.appendChild(msg);
        scrollDown();
    }

    function addTyping() {
        var msg = document.createElement('div');
        msg.className = 'past-msg past-msg-bot past-typing';
        msg.id = 'past-typing';
        msg.innerHTML =
            '<div class="past-msg-avatar"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12h6m-3-3v6m-7 4l2-2m14 2l-2-2M12 21a9 9 0 100-18 9 9 0 000 18z"/></svg></div>' +
            '<div class="past-msg-content"><div class="past-typing-dots"><span></span><span></span><span></span></div></div>';
        chatMessages.appendChild(msg);
        scrollDown();
    }

    function removeTyping() {
        var el = document.getElementById('past-typing');
        if (el) el.remove();
    }

    function sendMessage(text) {
        if (!text || !text.trim()) return;
        text = text.trim();
        addMessage(text, true);
        chatInput.value = '';
        chatInput.style.height = 'auto';
        addTyping();

        var fd = new FormData();
        fd.append('action', 'farmapaz_pastillin');
        fd.append('message', text);
        fd.append('nonce', chatData.nonce || '');

        fetch(chatData.ajaxUrl, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                removeTyping();
                addMessage(data.reply || '¡Ups! Algo salió mal. Intenta de nuevo 🙏', false);
            })
            .catch(function () {
                removeTyping();
                addMessage('¡Ups! Problema de conexión. ¿Hablamos por WhatsApp? 🙏', false);
            });
    }

    function formatText(text) {
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\n/g, '<br>')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    }

    function escapeHtml(text) {
        var d = document.createElement('div');
        d.textContent = text;
        return d.innerHTML.replace(/\n/g, '<br>');
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

    // Close on overlay click
    document.addEventListener('click', function (e) {
        if (isOpen && !chatBox.contains(e.target) && !chatBtn.contains(e.target)) {
            toggleChat(e);
        }
    });
})();
