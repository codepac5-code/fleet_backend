<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>تفاصيل التذكرة - تصميم عصري</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    /* الخطوط */
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600&display=swap');

    body {
      background: #f0f2f7;
      font-family: 'Cairo', sans-serif;
      color: #2c2c54;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      padding: 2rem 1rem;
    }
    .container {
      max-width: 900px;
      width: 100%;
      background: #fff;
      border-radius: 1rem;
      box-shadow: 0 16px 32px rgba(57, 57, 99, 0.12);
      padding: 2rem 2.5rem;
      display: flex;
      flex-direction: column;
      gap: 1.8rem;
    }

    /* بيانات التذكرة */
    .header-card {
      background: #3b3f72;
      color: #ffca28;
      padding: 1.8rem 2rem;
      border-radius: 1rem;
      box-shadow: 0 8px 24px rgba(57, 57, 99, 0.3);
      transition: background-color 0.3s ease;
    }
    .header-card:hover {
      background: #343969;
    }
    .header-card h2 {
      font-weight: 700;
      font-size: 1.9rem;
      margin-bottom: 0.6rem;
      letter-spacing: 0.02em;
    }
    .ticket-info span {
      display: inline-block;
      margin-left: 1.5rem;
      font-weight: 600;
      font-size: 0.95rem;
      user-select: none;
    }
    .ticket-info span .badge {
      font-size: 0.85rem;
      padding: 0.3rem 0.8rem;
      font-weight: 700;
      border-radius: 12px;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      box-shadow: 0 2px 5px rgb(255 202 40 / 0.45);
      transition: background-color 0.25s;
    }
    .badge-open {
      background-color: #4caf50;
      color: white;
    }
    .badge-closed {
      background-color: #e53935;
      color: white;
    }
    .badge-pending {
      background-color: #fb8c00;
      color: white;
    }
    .ticket-priority.high {
      color: #e53935;
      font-weight: 700;
      text-shadow: 0 0 6px #e53935;
    }
    .ticket-priority.medium {
      color: #ffb300;
      font-weight: 700;
      text-shadow: 0 0 6px #ffb300;
    }
    .ticket-priority.low {
      color: #43a047;
      font-weight: 700;
      text-shadow: 0 0 6px #43a047;
    }

    /* الصورة المرفقة */
    .attachment-label {
      margin-top: 1rem;
      font-weight: 600;
      font-size: 1.1rem;
      user-select: none;
      letter-spacing: 0.03em;
    }
    .file-attachment {
      margin-top: 0.7rem;
      max-width: 160px;
      max-height: 120px;
      border-radius: 1rem;
      object-fit: cover;
      box-shadow: 0 10px 25px rgb(57 57 99 / 0.15);
      cursor: pointer;
      transition: transform 0.25s ease;
      border: 2px solid transparent;
    }
    .file-attachment:hover {
      transform: scale(1.05);
      border-color: #ffca28;
      box-shadow: 0 12px 30px rgb(255 202 40 / 0.45);
    }

    /* محادثة */
    .chat-container {
      background: #fefefe;
      height: 420px;
      border-radius: 1rem;
      box-shadow: 0 12px 36px rgb(57 57 99 / 0.1);
      overflow-y: auto;
      padding: 1.5rem 2rem;
      display: flex;
      flex-direction: column;
      gap: 1rem;
      scrollbar-width: thin;
      scrollbar-color: #ffca28 #f0f2f7;
      scroll-behavior: smooth;
    }
    /* Webkit scrollbar */
    .chat-container::-webkit-scrollbar {
      width: 10px;
    }
    .chat-container::-webkit-scrollbar-track {
      background: #f0f2f7;
      border-radius: 6px;
    }
    .chat-container::-webkit-scrollbar-thumb {
      background-color: #ffca28;
      border-radius: 6px;
      border: 2px solid #f0f2f7;
    }

    .message {
      max-width: 65%;
      padding: 1rem 1.3rem;
      border-radius: 1.5rem;
      font-size: 1rem;
      line-height: 1.4;
      box-shadow: 0 4px 12px rgb(57 57 99 / 0.1);
      word-wrap: break-word;
      position: relative;
      transition: background-color 0.3s ease;
      user-select: text;
    }
    .message.user {
      background: linear-gradient(145deg, #fff176, #ffca28);
      color: #2c2c54;
      margin-left: auto;
      border-bottom-right-radius: 0.3rem;
      box-shadow: 0 8px 16px rgb(255 202 40 / 0.45);
    }
    .message.user:hover {
      background: linear-gradient(145deg, #ffee58, #ffc107);
      box-shadow: 0 10px 22px rgb(255 202 40 / 0.7);
    }
    .message.staff {
      background: linear-gradient(145deg, #3b3f72, #2c2c54);
      color: #ffca28;
      margin-right: auto;
      border-bottom-left-radius: 0.3rem;
      box-shadow: 0 8px 16px rgb(57 57 99 / 0.5);
    }
    .message.staff:hover {
      background: linear-gradient(145deg, #444a94, #393963);
      box-shadow: 0 10px 22px rgb(57 57 99 / 0.7);
    }
    .message .timestamp {
      font-size: 0.75rem;
      color: rgba(255, 202, 40, 0.75);
      margin-top: 0.5rem;
      user-select: none;
      font-weight: 600;
      letter-spacing: 0.04em;
    }

    /* مرفقات داخل الرسالة */
    .message img.file-attachment {
      margin-top: 0.8rem;
      max-width: 140px;
      border-radius: 1rem;
      box-shadow: 0 8px 20px rgb(57 57 99 / 0.15);
      cursor: pointer;
      transition: transform 0.3s ease;
      border: 2px solid transparent;
    }
    .message img.file-attachment:hover {
      transform: scale(1.1);
      border-color: #ffca28;
      box-shadow: 0 12px 28px rgb(255 202 40 / 0.5);
    }
    .message a {
      color: #ffca28;
      font-weight: 700;
      display: inline-block;
      margin-top: 0.6rem;
      text-decoration: none;
      transition: color 0.3s;
    }
    .message a:hover {
      color: #e6b800;
      text-decoration: underline;
    }

    /* مربع الإدخال */
    .input-reply {
      background: #fff;
      box-shadow: 0 12px 30px rgb(57 57 99 / 0.1);
      border-radius: 2rem;
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 0.7rem 1.2rem;
    }
    .input-reply textarea {
      flex-grow: 1;
      border: none;
      resize: none;
      font-family: 'Cairo', sans-serif;
      font-size: 1.1rem;
      padding: 0.6rem 1rem;
      border-radius: 2rem;
      background: #f0f2f7;
      color: #2c2c54;
      box-shadow: inset 4px 4px 6px #d1d5e6, inset -4px -4px 6px #ffffff;
      transition: background-color 0.25s ease;
      outline-offset: 3px;
      outline-color: transparent;
      transition: outline-color 0.25s ease;
    }
    .input-reply textarea:focus {
      outline-color: #ffca28;
      background: #fff8dc;
    }
    .input-reply button {
      background: transparent;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: #ffca28;
      transition: color 0.3s ease;
      user-select: none;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 42px;
      height: 42px;
      border-radius: 50%;
      box-shadow: 0 8px 16px rgb(255 202 40 / 0.3);
      background: linear-gradient(145deg, #fff176, #ffca28);
      font-weight: 700;
    }
    .input-reply button:disabled {
      color: #ccc;
      box-shadow: none;
      cursor: default;
      background: #f0f2f7;
    }
    .input-reply button:hover:not(:disabled) {
      color: #e6b800;
      box-shadow: 0 12px 24px rgb(255 202 40 / 0.6);
      background: linear-gradient(145deg, #ffee58, #ffc107);
    }

    /* لوحة الإجراءات */
    .action-panel {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      justify-content: flex-start;
      align-items: center;
      padding-top: 1rem;
      border-top: 1px solid #e0e4f7;
    }
    .action-panel button {
      background: #ffca28;
      border: none;
      border-radius: 1.2rem;
      padding: 0.7rem 1.5rem;
      font-weight: 600;
      color: #3b3f72;
      cursor: pointer;
      box-shadow: 0 10px 20px rgb(255 202 40 / 0.4);
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
      user-select: none;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .action-panel button:hover {
      background: #e6b800;
      box-shadow: 0 14px 28px rgb(230 184 0 / 0.6);
    }
    .action-panel button:disabled {
      background: #f0f2f7;
      color: #999;
      cursor: not-allowed;
      box-shadow: none;
    }

    /* السجل */
    .activity-log {
      margin-top: 1rem;
      font-size: 0.85rem;
      color: #7a7a9d;
      background: #f7f8fa;
      padding: 1rem 1.5rem;
      border-radius: 1rem;
      box-shadow: inset 4px 4px 6px #d1d5e6, inset -4px -4px 6px #ffffff;
      max-height: 120px;
      overflow-y: auto;
      user-select: text;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .chat-container {
        height: 320px;
        padding: 1rem 1.2rem;
      }
      .header-card h2 {
        font-size: 1.5rem;
      }
      .input-reply textarea {
        font-size: 1rem;
      }
      .action-panel {
        justify-content: center;
      }
    }
  </style>
</head>
<body>
  <div class="container">

    <!-- بيانات التذكرة -->
    <div class="header-card">
      <h2>عنوان التذكرة: مشكلة في تسجيل الدخول</h2>
      <div class="ticket-info mt-2">
        <span>الحالة: <span class="badge badge-open">مفتوحة</span></span>
        <span>القسم: دعم فني</span>
        <span>الأولوية: <span class="ticket-priority high">عالية</span></span>
        <span>من أنشأها: محمد أحمد</span>
      </div>
      <div class="ticket-info mt-1" style="font-size: 0.9rem; color:#ddd;">
        <span>تاريخ الإنشاء: 2025-07-10 14:23</span>
        <span>آخر تحديث: 2025-07-11 09:10</span>
      </div>
      <div class="attachment-label">الصورة المرفقة:</div>
      <img src="https://via.placeholder.com/160x120.png?text=Attachment" alt="Attachment" class="file-attachment" />
    </div>

    <!-- محادثة التذكرة -->
    <div class="chat-container" id="chat-container">
      <div class="message user">
        مرحبًا، لا أستطيع تسجيل الدخول إلى حسابي.
        <div class="timestamp">2025-07-10 14:25</div>
      </div>
      <div class="message staff">
        مرحبًا محمد، هل جربت إعادة تعيين كلمة المرور؟
        <div class="timestamp">2025-07-10 14:27</div>
      </div>
      <div class="message user">
        نعم، لكن لم يصلني البريد الإلكتروني لإعادة التعيين.
        <div class="timestamp">2025-07-10 14:30</div>
        <img src="https://via.placeholder.com/140x100.png?text=Error+Screenshot" alt="مرفق" class="file-attachment" />
      </div>
    </div>

    <!-- مربع الإدخال -->
    <div class="input-reply">
      <textarea id="reply-text" rows="2" placeholder="اكتب ردك هنا..."></textarea>
      <input type="file" id="file-input" style="display:none" />
      <button id="attach-btn" title="إرفاق ملف" aria-label="Attach file">
        📎
      </button>
      <button id="send-btn" disabled title="إرسال الرد" aria-label="Send reply">
        ➤
      </button>
    </div>

    <!-- أزرار الإجراءات -->
    <div class="action-panel">
      <button id="transfer-btn" title="نقل للقسم">نقل للقسم</button>
      <button id="assign-btn" title="تعيين لموظف آخر">تعيين لموظف</button>
      <button id="close-btn" title="إغلاق التذكرة">إغلاق التذكرة</button>
      <button id="reopen-btn" title="إعادة فتح التذكرة" class="d-none">إعادة فتح التذكرة</button>
    </div>

    <!-- سجل النشاط -->
    <div class="activity-log">
      <strong>سجل النشاط:</strong><br />
      - 2025-07-10 14:23: تم إنشاء التذكرة بواسطة محمد أحمد.<br />
      - 2025-07-10 14:27: تم الرد بواسطة موظف الدعم.<br />
      - 2025-07-10 14:35: تم رفع ملف مرفق.<br />
      - 2025-07-11 09:10: تم تحديث حالة التذكرة.
    </div>
  </div>

  <script>
    // التفاعل: تمكين زر الإرسال عند وجود نص أو ملف مرفق
    const replyText = document.getElementById('reply-text');
    const fileInput = document.getElementById('file-input');
    const sendBtn = document.getElementById('send-btn');
    const attachBtn = document.getElementById('attach-btn');
    const chatContainer = document.getElementById('chat-container');

    function toggleSendButton() {
      sendBtn.disabled = replyText.value.trim() === '' && !fileInput.files.length;
    }

    replyText.addEventListener('input', toggleSendButton);
    fileInput.addEventListener('change', toggleSendButton);
    attachBtn.addEventListener('click', () => fileInput.click());

    sendBtn.addEventListener('click', () => {
      if (sendBtn.disabled) return;
      addMessage(replyText.value, fileInput.files[0]);
    });

    replyText.addEventListener('keydown', e => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        if (!sendBtn.disabled) addMessage(replyText.value, fileInput.files[0]);
      }
    });

    function addMessage(text, file) {
      if (!text.trim() && !file) return;

      const messageDiv = document.createElement('div');
      messageDiv.classList.add('message', 'staff');
      messageDiv.textContent = text;

      if (file) {
        if (file.type.startsWith('image/')) {
          const img = document.createElement('img');
          img.src = URL.createObjectURL(file);
          img.className = 'file-attachment';
          img.style.marginTop = '0.5rem';
          img.onclick = () => window.open(img.src, '_blank');
          messageDiv.appendChild(img);
        } else {
          const link = document.createElement('a');
          link.href = '#';
          link.textContent = file.name;
          link.style.display = 'block';
          link.style.marginTop = '0.5rem';
          messageDiv.appendChild(link);
        }
      }

      const timestampDiv = document.createElement('div');
      timestampDiv.classList.add('timestamp');
      const now = new Date();
      timestampDiv.textContent = now.toLocaleString('ar-EG', {
        hour12: true,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
      });
      messageDiv.appendChild(timestampDiv);

      chatContainer.appendChild(messageDiv);
      chatContainer.scrollTop = chatContainer.scrollHeight;

      // إعادة تعيين الحقول
      replyText.value = '';
      fileInput.value = '';
      toggleSendButton();
    }

    function closeTicket() {
      if (confirm('هل أنت متأكد من إغلاق التذكرة؟')) {
        alert('تم إغلاق التذكرة.');
        document.querySelector('.header-card .badge').className = 'badge badge-closed';
        document.querySelector('.header-card .badge').textContent = 'مغلقة';

        document.getElementById('reopen-btn').classList.remove('d-none');
      }
    }

    function reopenTicket() {
      if (confirm('هل تريد إعادة فتح التذكرة؟')) {
        alert('تم إعادة فتح التذكرة.');
        document.querySelector('.header-card .badge').className = 'badge badge-open';
        document.querySelector('.header-card .badge').textContent = 'مفتوحة';

        document.getElementById('reopen-btn').classList.add('d-none');
      }
    }

    document.getElementById('close-btn').addEventListener('click', closeTicket);
    document.getElementById('reopen-btn').addEventListener('click', reopenTicket);
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
