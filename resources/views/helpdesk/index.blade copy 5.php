<x-master-layout>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>تفاصيل التذكرة - تصميم عصري</title>
  <style>

body {
  background: linear-gradient(135deg, #f0f4ff, #d9e4ff);
  font-family: 'Cairo', sans-serif;
  padding: 2rem 1rem;
  color: #1e1e40;
}

.container {
  max-width: 1400px;
  width: 100%;
  background: #fff;
  border-radius: 1.8rem;
  box-shadow: 0 24px 48px rgba(60, 60, 110, 0.1);
  padding: 2.2rem 2.8rem;
  display: flex;
  flex-direction: column;
  gap: 2.3rem;
  transition: box-shadow 0.4s ease;
}
.container:hover {
  box-shadow: 0 32px 64px rgba(60, 60, 110, 0.18);
}

.header-card {
  background: #38395a;
  color: #ffd93b;
  padding: 2rem 2.8rem;
  border-radius: 1.8rem;
  /* box-shadow: 0 14px 36px rgba(60, 60, 110, 0.3); */
  cursor: default;
  transition: background-color 0.35s ease;
}
.header-card:hover {
  background: #272b5a;
}
.header-card h2 {
  font-weight: 900;
  font-size: 2.2rem;
  margin-bottom: 0.6rem;
  letter-spacing: 0.03em;
  /* text-shadow: 0 2px 6px rgba(255, 217, 59, 0.7); */
}

.ticket-info span {
  display: inline-block;
  margin-left: 1.6rem;
  font-weight: 700;
  font-size: 1.05rem;
  user-select: none;
  color: #ffdd6e;
}
.ticket-info span:first-child {
  margin-left: 0;
}
.ticket-info span .badge {
  font-size: 0.95rem;
  padding: 0.36rem 1rem;
  font-weight: 700;
  border-radius: 18px;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  box-shadow: 0 4px 12px rgba(255 217 59 / 0.6);
  transition: background-color 0.3s ease;
}

/* البادجات */
.badge-open {
  background-color: #4caf50;
  color: white;
}
.badge-closed {
  background-color: #e53935;
  color: white;
}
.badge-pending {
  background-color: #ff9800;
  color: white;
}

.ticket-priority.high {
  color: #e53935;
  font-weight: 900;
  text-shadow: 0 0 10px #e53935;
}
.ticket-priority.medium {
  color: #fb8c00;
  font-weight: 800;
  text-shadow: 0 0 9px #fb8c00;
}
.ticket-priority.low {
  color: #43a047;
  font-weight: 800;
  text-shadow: 0 0 8px #43a047;
}

.ticket-info.mt-1 {
  font-size: 0.9rem;
  color: #bbb;
  letter-spacing: 0.02em;
  margin-top: 0.5rem;
}


.attachment-label {
  margin-top: 1.2rem;
  font-weight: 700;
  font-size: 1.25rem;
  user-select: none;
  letter-spacing: 0.05em;
  color: #444;
}
.file-attachment {
  margin-top: 1rem;
  max-width: 300px;
  max-height: 400px;
  border-radius: 1.7rem;
  object-fit: cover;
  box-shadow: 0 18px 38px rgba(60, 60, 110, 0.22);
  cursor: pointer;
  transition: transform 0.35s ease, border-color 0.35s ease, box-shadow 0.35s ease;
  border: 3px solid transparent;
}
.file-attachment:hover {
  transform: scale(1.1);
  border-color: #ffd93b;
  box-shadow: 0 24px 50px rgba(255, 217, 59, 0.65);
}


.chat-container {
  background: #fafaff;
  height: 460px;
  border-radius: 2.2rem;
  box-shadow: 0 16px 44px rgba(60, 60, 110, 0.12);
  overflow-y: auto;
  padding: 2rem 2.7rem;
  display: flex;
  flex-direction: column;
  gap: 1.4rem;
  scrollbar-width: thin;
  scrollbar-color: #ffd93b #e8eaf6;
  scroll-behavior: smooth;
}
.chat-container::-webkit-scrollbar {
  width: 14px;
}
.chat-container::-webkit-scrollbar-track {
  background: #e8eaf6;
  border-radius: 10px;
}
.chat-container::-webkit-scrollbar-thumb {
  background-color: #ffd93b;
  border-radius: 10px;
  border: 4px solid #e8eaf6;
}


.message {
  max-width: 70%;
  padding: 1.3rem 1.9rem;
  border-radius: 2.2rem;
  font-size: 1.15rem;
  line-height: 1.7;
  box-shadow: 0 6px 20px rgba(60, 60, 110, 0.1);
  word-wrap: break-word;
  position: relative;
  transition: background-color 0.35s ease;
  user-select: text;
}
.message.user {
  background: linear-gradient(145deg, #fffde7, #ffef4d);
  color: #3a3f6c;
  margin-left: auto;
  border-bottom-right-radius: 0.7rem;
  box-shadow: 0 12px 26px rgba(255, 239, 77, 0.5);
}
.message.user:hover {
  background: linear-gradient(145deg, #d1c569, #c5c05d);
  box-shadow: 0 16px 32px rgba(255, 239, 77, 0.7);
}
.message.staff {
  background: linear-gradient(145deg, #3a3f6c, #2f3354);
  color: #ffcd03;
  margin-right: auto;
  border-bottom-left-radius: 0.7rem;
  box-shadow: 0 16px 38px rgba(60, 60, 110, 0.55);
}
.message.staff:hover {
  background: linear-gradient(145deg, #454b79, #3b4065);
  box-shadow: 0 20px 40px rgba(60, 60, 110, 0.8);
}

.message .timestamp {
  font-size: 0.82rem;
  color: rgba(124, 124, 123, 0.85);
  margin-top: 0.7rem;
  user-select: none;
  font-weight: 700;
  letter-spacing: 0.05em;
}

.message .timestampSupport {
  font-size: 0.82rem;
  color: rgba(231, 231, 177, 0.85);
  margin-top: 0.7rem;
  user-select: none;
  font-weight: 700;
  letter-spacing: 0.05em;
}

.message img.file-attachment {
  margin-top: 1.1rem;
  max-width: 165px;
  border-radius: 1.7rem;
  box-shadow: 0 12px 30px rgba(60, 60, 110, 0.18);
  cursor: pointer;
  transition: transform 0.35s ease;
  border: 3px solid transparent;
}
.message img.file-attachment:hover {
  transform: scale(1.15);
  border-color: #ffd93b;
  box-shadow: 0 18px 40px rgba(255, 217, 59, 0.68);
}
.message a {
  color: #c4a21d;
  font-weight: 800;
  display: inline-block;
  margin-top: 0.9rem;
  text-decoration: none;
  transition: color 0.3s ease;
}
.message a:hover {
  color: #e6b800;
  text-decoration: underline;
}

.input-reply {
  background: #fff;
  box-shadow: 0 18px 42px rgba(60, 60, 110, 0.12);
  border-radius: 3.2rem;
  display: flex;
  align-items: center;
  gap: 1.4rem;
  padding: 1.2rem 1.8rem;
}
.input-reply textarea {
  flex-grow: 1;
  border: none;
  resize: none;
  font-family: 'Cairo', sans-serif;
  font-size: 1.25rem;
  padding: 1rem 1.4rem;
  border-radius: 3.2rem;
  background: #f9fbff;
  color: #3a3f6cb6;
  box-shadow: inset 6px 6px 12px #d9dff0, inset -6px -6px 12px #ffffff;
  transition: background-color 0.35s ease, outline-color 0.35s ease;
  outline-offset: 5px;
  outline-color: transparent;
  font-weight: 700;
}
.input-reply textarea:focus {
  outline-color: #ffd93b;
  background: #fffde7;
}
.input-reply button {
  background: linear-gradient(145deg, #fffde7, #ffef4d);
  border: none;
  font-size: 1.9rem;
  cursor: pointer;
  color: #3a3f6c;
  transition: color 0.35s ease, box-shadow 0.35s ease;
  user-select: none;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 52px;
  height: 52px;
  border-radius: 50%;
  box-shadow: 0 14px 32px rgba(255, 239, 77, 0.5);
  font-weight: 900;
}
.input-reply button:disabled {
  color: #bbb;
  box-shadow: none;
  cursor: not-allowed;
  background: #f3f5fa;
}
.input-reply button:hover:not(:disabled) {
  color: #e6b800;
  box-shadow: 0 20px 40px rgba(230, 184, 0, 0.8);
  background: linear-gradient(145deg, hsl(54, 63%, 47%), #f5eb359c);
}

/* لوحة الإجراءات */
.action-panel {
  display: flex;
  gap: 1.6rem;
  flex-wrap: wrap;
  justify-content: flex-start;
  align-items: center;
  padding-top: 1.8rem;
  border-top: 1.8px solid #e1e4f2;
}
.action-panel button {
  background: #ff3b3b;
  border: none;
  border-radius: 1.7rem;
  padding: 1rem 2rem;
  font-weight: 800;
  color: #2d3270;
  cursor: pointer;
  box-shadow: 0 14px 36px rgba(255, 217, 59, 0.6);
  transition: background-color 0.35s ease, box-shadow 0.35s ease;
  user-select: none;
  display: flex;
  align-items: center;
  gap: 0.7rem;
  font-size: 1.15rem;
}
.action-panel button:hover {
  background: #e6b800;
  box-shadow: 0 20px 44px rgba(230, 184, 0, 0.8);
}
.action-panel button:disabled {
  background: #f3f5fa;
  color: #bbb;
  cursor: not-allowed;
  box-shadow: none;
}

/* سجل النشاط */
.activity-log {
  margin-top: 1.6rem;
  font-size: 0.95rem;
  color: #6e6e8e;
  background: #f7f8fc;
  padding: 1.4rem 2rem;
  border-radius: 1.7rem;
  box-shadow: inset 6px 6px 14px #d9dff0, inset -6px -6px 14px #ffffff;
  max-height: 145px;
  overflow-y: auto;
  line-height: 1.55;
  user-select: none;
}

/* استجابة للأجهزة الصغيرة */
@media (max-width: 720px) {
  .container {
    padding: 1.6rem 1.8rem;
  }
  .header-card {
    padding: 1.4rem 2rem;
  }
  .chat-container {
    height: 380px;
    padding: 1.6rem 2rem;
  }
  .input-reply {
    padding: 1rem 1.3rem;
  }
  .input-reply textarea {
    font-size: 1.15rem;
  }
  .action-panel {
    flex-direction: column;
    gap: 1rem;
  }
}


  </style>
</head>
<body>
  <div class="container">

    <div class="header-card">
      <h2 style="color: rgba(185, 153, 7, 0.897);">عنوان التذكرة: مشكلة في تسجيل الدخول</h2>
      <div class="ticket-info mt-2">
        <span>الحالة: <span class="badge badge-open">مفتوحة</span></span>
        <span>القسم: دعم فني</span>
        <span>الأولوية: <span class="ticket-priority high">عالية</span></span>
        <span>من أنشأها: بسام نكز</span>
      </div>

      <div class="ticket-info mt-1" style="font-size: 0.9rem; color:#ddd;">
        <span>تاريخ الإنشاء: 2025-07-10 14:23</span>
        <span>آخر تحديث: 2025-07-11 09:10</span>
      </div>

      <div>
        <h3 style="color: white;">يبات يبا تنسابش سبتنس ينتشسلا رن شنتشسن </h3>
        </div>
      <!-- {{-- <div class="attachment-label">الصورة المرفقة:</div> --}} -->
      {{-- <img src="{{asset('storage-r/user/profile/1730798867_.jpg')}}" alt="Attachment" class="file-attachment" /> --}}
    </div>

    <div class="chat-container" id="chat-container">
      <div class="message user">
        مرحبًا، لا أستطيع تسجيل الدخول إلى حسابي.
        <div class="timestamp">2025-07-10 14:25</div>
      </div>
      <div class="message staff">
        مرحبًا بسام، هل جربت إعادة تعيين كلمة المرور؟
        <div class="timestampSupport">2025-07-10 14:27</div>
      </div>
      <div class="message user">
        نعم، لكن لم يصلني البريد الإلكتروني لإعادة التعيين.
        <div class="timestamp">2025-07-10 14:30</div>
        {{-- <img src="https://via.placeholder.com/140x100.png?text=Error+Screenshot" alt="مرفق" class="file-attachment" /> --}}
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

    <div class="action-panel">
        <div class="form-group col-md-4">
            <label for="department" class="form-control-label">{{ __('messages.department') }}</label>
            <select name="department" id="department" class="select2js form-control">
                <option value="support" {{ old('department', $ticket->department ?? '') == 'support' ? 'selected' : '' }}>{{ __('messages.support_department') }}</option>
                <option value="sales" {{ old('department', $ticket->department ?? '') == 'sales' ? 'selected' : '' }}>{{ __('messages.sales_department') }}</option>
                <option value="billing" {{ old('department', $ticket->department ?? '') == 'billing' ? 'selected' : '' }}>{{ __('messages.billing_department') }}</option>
            </select>
        </div>
        <div class="form-group col-md-4">
            <label for="department" class="form-control-label">{{ __('messages.department') }}</label>
            <select name="department" id="department" class="select2js form-control">
                <option value="support" {{ old('department', $ticket->department ?? '') == 'support' ? 'selected' : '' }}>{{ __('messages.support_department') }}</option>
                <option value="sales" {{ old('department', $ticket->department ?? '') == 'sales' ? 'selected' : '' }}>{{ __('messages.sales_department') }}</option>
                <option value="billing" {{ old('department', $ticket->department ?? '') == 'billing' ? 'selected' : '' }}>{{ __('messages.billing_department') }}</option>
            </select>
        </div>

      {{-- <button id="assign-btn" title="تعيين لموظف آخر">تعيين لموظف</button> --}}
      <button id="close-btn" title="إغلاق التذكرة" >
        <p style="color:white;">إغلاق التذكرة</p></button>
      <button id="reopen-btn" title="إعادة فتح التذكرة" class="d-none">إعادة فتح التذكرة</button>
    </div>

    <!-- سجل النشاط -->
    <div class="activity-log">
      <strong>سجل النشاط:</strong><br />
      - 2025-07-10 14:23: تم إنشاء التذكرة بواسطة بسام نكز.<br />
      - 2025-07-10 14:27: تم الرد بواسطة موظف الدعم.<br />
      - 2025-07-10 14:35: تم رفع ملف مرفق.<br />
      - 2025-07-11 09:10: تم تحديث حالة التذكرة.
    </div>
  </div>


  

  <script>
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
      timestampDiv.classList.add('timestampSupport');
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
</x-master-layout>

