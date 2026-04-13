<meta name="csrf-token" content="{{ csrf_token() }}">
<div id="rh-chatbot" class="chatbot-container">

  <!-- Toggle Button -->
  <button class="chatbot-toggle" onclick="toggleChatbot()" title="Assistant RH IA">
    <svg viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" width="24" height="24">
      <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
    </svg>
    <span>Assistant RH</span>
  </button>

  <!-- Popup -->
  <div class="chatbot-popup" id="chatbotPopup">

    <!-- Header -->
    <div class="chatbot-header">
      <div class="chatbot-avatar">🤖</div>
      <div class="chatbot-info">
        <h3>Assistant RH</h3>
        <span class="status-dot"></span> <span>En ligne</span>
      </div>
      <button class="chatbot-close" onclick="toggleChatbot()">×</button>
    </div>

    <!-- Messages -->
    <div class="chatbot-messages" id="chatbotMessages">
      <div class="message bot">
        <div class="avatar-small">🤖</div>
        <div class="bubble">
          Bonjour ! Je peux vous aider avec les <strong>salaires</strong>, <strong>plannings</strong>,
          <strong>absences</strong> et générer des <strong>PDF</strong>. Comment puis-je vous aider ?
        </div>
        <small class="time">Maintenant</small>
      </div>
    </div>

    <!-- Input -->
    <div class="chatbot-input">
      <div class="input-wrapper">
        <textarea id="chatInput" placeholder="Posez votre question..." rows="1" onkeydown="handleKey(event)" oninput="autoResize(this)"></textarea>
        <button id="chatSend" onclick="sendMessage()">➤</button>
      </div>
    </div>

  </div>
</div>

<style>
.chatbot-container {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* ── Toggle ── */
.chatbot-toggle {
  position: fixed;
  bottom: 24px; right: 24px;
  background: linear-gradient(135deg, var(--primary-dark, #0f6b7c), var(--primary, #1a8a74));
  color: white;
  border: none;
  border-radius: 50px;
  padding: 12px 18px;
  font-weight: 600; font-size: 14px;
  box-shadow: 0 8px 25px rgba(15,107,124,0.3);
  cursor: pointer;
  z-index: 10000;
  transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
  display: flex; align-items: center; gap: 8px;
}
.chatbot-toggle:hover { transform: translateY(-2px); box-shadow: 0 12px 35px rgba(26,26,46,0.4); }

/* ── Popup ── */
.chatbot-popup {
  position: fixed;
  bottom: 90px; right: 24px;
  width: 360px; max-height: 520px;
  background: white;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.25);
  border: 1px solid #e5e7eb;
  display: none; flex-direction: column;
  z-index: 10001; overflow: hidden;
  animation: slideUp 0.3s cubic-bezier(0.4,0,0.2,1);
}
@keyframes slideUp {
  from { opacity:0; transform: translateY(20px) scale(0.95); }
  to   { opacity:1; transform: translateY(0)    scale(1);    }
}

/* ── Header ── */
.chatbot-header {
  background: var(--primary-dark, #0f2132);
  color: white; padding: 16px 20px;
  display: flex; align-items: center; gap: 12px;
}
.chatbot-avatar {
  width: 44px; height: 44px;
  background: var(--accent, #1a8a74);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; flex-shrink: 0;
}
.chatbot-info h3 { margin:0; font-size:16px; font-weight:600; }
.chatbot-info { display:flex; align-items:center; gap:6px; flex:1; flex-direction:row; flex-wrap:wrap; }
.chatbot-info h3 { width:100%; }
.status-dot { width:8px; height:8px; background:#4ade80; border-radius:50%; animation: pulse 2s infinite; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
.chatbot-close {
  margin-left: auto;
  background: rgba(255,255,255,.2); border: none; color: white;
  width: 32px; height: 32px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; font-size: 18px; transition: all .2s;
}
.chatbot-close:hover { background: rgba(255,255,255,.35); transform: rotate(90deg); }

/* ── Messages ── */
.chatbot-messages {
  flex: 1; padding: 16px;
  overflow-y: auto;
  background: #f8fafc;
  max-height: 370px;
  display: flex; flex-direction: column; gap: 12px;
}
.message { display: flex; gap: 8px; max-width: 90%; }
.message.user { align-self: flex-end; flex-direction: row-reverse; }
.bubble {
  padding: 10px 14px; border-radius: 18px;
  font-size: 14px; line-height: 1.5;
  word-wrap: break-word; max-width: 100%;
}
.message.bot .bubble {
  background: white;
  border: 1px solid #e2e8f0;
  border-bottom-left-radius: 6px;
  color: #1e293b;
}
.message.user .bubble {
  background: #b0e0e6;
  color: #0f172a;
  border-bottom-right-radius: 6px;
}
.avatar-small {
  width:28px; height:28px; border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  font-size:14px; flex-shrink:0;
}
.message.bot  .avatar-small { background: var(--primary, #1a8a74); color:white; }
.message.user .avatar-small { background: var(--accent, #16c9aa); color:white; }
.time { align-self:flex-end; font-size:11px; color:#94a3b8; margin-top:2px; }

/* ── Bouton PDF ── */
.pdf-download-btn {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  margin-top: 10px;
  padding: 9px 16px;
  background: linear-gradient(135deg, #0f2132, #1a8a74);
  color: white !important;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none !important;
  transition: all .2s;
  border: none;
  cursor: pointer;
  width: 100%;
  justify-content: center;
  position: relative;
  overflow: hidden;
}
.pdf-download-btn::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.08), transparent);
  pointer-events: none;
}
.pdf-download-btn:hover:not(:disabled) { 
  opacity: .88; 
  transform: translateY(-1px); 
  box-shadow: 0 6px 20px rgba(15,33,50,0.35);
}
.pdf-download-btn:active:not(:disabled) { transform: translateY(0); }
.pdf-download-btn:disabled { opacity: .55; cursor: not-allowed; transform: none; }
.pdf-download-btn svg { flex-shrink: 0; }
.pdf-download-btn.success { background: linear-gradient(135deg, #059669, #10b981); }

/* ── Typing ── */
.typing { display:flex; gap:4px; padding:12px 16px; }
.typing-dot { width:6px; height:6px; background:#94a3b8; border-radius:50%; animation: bounce 1.2s infinite; }
.typing-dot:nth-child(2) { animation-delay:.2s; }
.typing-dot:nth-child(3) { animation-delay:.4s; }
@keyframes bounce { 0%,60%,100%{transform:translateY(0)} 30%{transform:translateY(-4px)} }

/* ── Input ── */
.chatbot-input {
  padding: 12px 16px;
  background: white;
  border-top: 1px solid #f1f5f9;
}
.input-wrapper { display:flex; gap:8px; align-items:flex-end; }
#chatInput {
  flex:1; border:1px solid #e2e8f0; border-radius:24px;
  padding:11px 16px; font-size:14px; font-family:inherit;
  resize:none; max-height:100px; outline:none;
  transition:border-color .2s; line-height:1.4;
}
#chatInput:focus { border-color: var(--primary,#1a8a74); box-shadow: 0 0 0 3px rgba(26,138,116,.12); }
#chatSend {
  width:44px; height:44px;
  background: var(--navy, #0f2132);
  color:white; border:none; border-radius:50%;
  cursor:pointer; font-size:16px;
  display:flex; align-items:center; justify-content:center;
  transition:all .2s; flex-shrink:0;
}
#chatSend:hover:not(:disabled) { background: var(--accent, #1a8a74); transform:scale(1.05); }
#chatSend:disabled { opacity:.5; cursor:not-allowed; }

/* ── Scrollbar ── */
.chatbot-messages::-webkit-scrollbar { width:4px; }
.chatbot-messages::-webkit-scrollbar-track { background:transparent; }
.chatbot-messages::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:2px; }

/* ── Responsive ── */
@media (max-width:480px) {
  .chatbot-popup { width:calc(100vw - 48px); right:24px; left:24px; bottom:24px; max-height:calc(100vh - 120px); }
  .chatbot-toggle { bottom:20px; right:20px; }
}
</style>

<script>
window.threadId = window.threadId || localStorage.getItem('rhChatThread') || null;

// ── Ouvrir / fermer ──────────────────────────────────────────────────────────
function toggleChatbot() {
  const popup  = document.getElementById('chatbotPopup');
  const toggle = document.querySelector('.chatbot-toggle');
  const isOpen = popup.style.display === 'flex';
  popup.style.display = isOpen ? 'none' : 'flex';
  toggle.classList.toggle('active', !isOpen);
}

// ── Resize textarea ──────────────────────────────────────────────────────────
function autoResize(el) {
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 100) + 'px';
}

// ── Touche Entrée ────────────────────────────────────────────────────────────
function handleKey(e) {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    sendMessage();
  }
}

// ── Ajouter un message texte ─────────────────────────────────────────────────
function addMessage(role, html, time) {
  time = time || new Date().toLocaleTimeString('fr-FR', { hour:'2-digit', minute:'2-digit' });
  const messages = document.getElementById('chatbotMessages');

  const div = document.createElement('div');
  div.className = 'message ' + role;

  const avatar = document.createElement('div');
  avatar.className = 'avatar-small';
  avatar.textContent = role === 'bot' ? '🤖' : '👤';

  const bubble = document.createElement('div');
  bubble.className = 'bubble';
  bubble.innerHTML = html;

  const timestamp = document.createElement('small');
  timestamp.className = 'time';
  timestamp.textContent = time;

  div.appendChild(avatar);
  div.appendChild(bubble);
  div.appendChild(timestamp);
  messages.appendChild(div);
  messages.scrollTop = messages.scrollHeight;
}

// ── CORRECTION PRINCIPALE : Parser la réponse et détecter PDF_DOWNLOAD:: ────
// Format retourné par PdfTool : PDF_DOWNLOAD::{url}::{filename}::{label}
// FIX : regex robuste qui gère les URLs contenant "://" sans casser le split
function parseReply(rawText) {
  const lines      = rawText.split('\n');
  const textLines  = [];
  const pdfButtons = [];

  // Regex corrigée :
  // - Capture l'URL complète (y compris http://) en s'arrêtant au pattern "::<non-slash>"
  // - Utilise un lookahead pour délimiter sans casser les URLs
  const pdfRegex = /^PDF_DOWNLOAD::(https?:\/\/\S+?)::([^:\n]+(?::[^:\n]+)*)::(.+)$/;

  for (const line of lines) {
    const trimmed = line.trim();

    // Tester d'abord si la ligne contient un tag PDF
    const m = trimmed.match(pdfRegex);
    if (m) {
      pdfButtons.push({
        url:      m[1].trim(),
        filename: m[2].trim(),
        label:    m[3].trim(),
      });
      // Ne jamais ajouter cette ligne au texte visible
      continue;
    }

    // Ignorer les lignes vides multiples consécutives (garder max 1 ligne vide)
    if (trimmed === '' && textLines.length > 0 && textLines[textLines.length - 1] === '') {
      continue;
    }

    textLines.push(trimmed);
  }

  // Construire le HTML du texte (sans les tags PDF)
  let html = textLines
    .join('\n')
    .trim()
    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')   // **gras**
    .replace(/\*(.+?)\*/g, '<em>$1</em>')               // *italique*
    .replace(/\n/g, '<br>');

  // Ajouter les boutons PDF animés
  for (const btn of pdfButtons) {
    const escapedUrl      = escapeHtml(btn.url);
    const escapedFilename = escapeHtml(btn.filename);
    const escapedLabel    = escapeHtml(btn.label);

    html += `
      <button
        onclick="downloadPdf('${escapedUrl}', '${escapedFilename}', this)"
        class="pdf-download-btn"
        data-url="${escapedUrl}"
        data-filename="${escapedFilename}"
      >
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a1 1 0 001 1h16a1 1 0 001-1v-3"/>
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M7 7H4a1 1 0 00-1 1v5M17 7h3a1 1 0 011 1v5"/>
          <rect x="7" y="3" width="10" height="10" rx="2"
            stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Télécharger — ${escapedLabel}
      </button>`;
  }

  return html || '…';
}

// ── Escape HTML ──────────────────────────────────────────────────────────────
function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g,  '&amp;')
    .replace(/</g,  '&lt;')
    .replace(/>/g,  '&gt;')
    .replace(/"/g,  '&quot;')
    .replace(/'/g,  '&#39;');
}

// ── Téléchargement PDF (CORRIGÉ : pas de nouvel onglet, feedback visuel) ─────
function downloadPdf(url) {
  window.open(url, '_blank');
}

// ── Typing indicator ─────────────────────────────────────────────────────────
function showTyping() {
  const messages = document.getElementById('chatbotMessages');
  const el = document.createElement('div');
  el.className = 'message bot';
  el.id = 'typingIndicator';
  el.innerHTML = `
    <div class="avatar-small">⭐</div>
    <div class="bubble">
      <div class="typing">
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
      </div>
    </div>`;
  messages.appendChild(el);
  messages.scrollTop = messages.scrollHeight;
}

function hideTyping() {
  const el = document.getElementById('typingIndicator');
  if (el) el.remove();
}

// ── Envoyer un message ───────────────────────────────────────────────────────
async function sendMessage() {
  const input   = document.getElementById('chatInput');
  const sendBtn = document.getElementById('chatSend');
  const text    = input.value.trim();
  if (!text) return;

  addMessage('user', escapeHtml(text));
  input.value = '';
  input.style.height = 'auto';
  sendBtn.disabled = true;
  showTyping();

  try {
    const res = await fetch('/ask-ai', {
      method: 'POST',
      headers: {
        'Content-Type':  'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
      },

      body: JSON.stringify({ message: text }),
    });

    if (!res.ok) {
      throw new Error(`Serveur : HTTP ${res.status}`);
    }

    const data = await res.json();
    hideTyping();

    if (data.reply) {
      window.threadId = data.thread ?? null;
      if (window.threadId) localStorage.setItem('rhChatThread', window.threadId);
      addMessage('bot', parseReply(data.reply));
    } else {
      addMessage('bot', '⚠️ Réponse vide du serveur.');
    }

  } catch (err) {
    console.error('[AssistantRH]', err);
    hideTyping();
    addMessage('bot', `⚠️ Impossible de contacter le serveur.<br><small style="color:#94a3b8">${escapeHtml(err.message)}</small>`);
  } finally {
    sendBtn.disabled = false;
    input.focus();
  }
}

// ── Fermer sur clic extérieur / Echap ────────────────────────────────────────
document.addEventListener('click', (e) => {
  if (!e.target.closest('#rh-chatbot')) {
    document.getElementById('chatbotPopup').style.display = 'none';
    document.querySelector('.chatbot-toggle')?.classList.remove('active');
  }
});

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    document.getElementById('chatbotPopup').style.display = 'none';
    document.querySelector('.chatbot-toggle')?.classList.remove('active');
  }
});
</script>