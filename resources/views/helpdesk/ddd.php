<x-master-layout>
    <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1" />
      <title>تفاصيل التذكرة - تصميم عصري</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
      <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600&display=swap');
    
        body {
          background: #f4f6fb;
          font-family: 'Cairo', sans-serif;
          color: #3b3f72;
          -webkit-font-smoothing: antialiased;
          -moz-osx-font-smoothing: grayscale;
        }
    
        .container {
          max-width: 920px;
          width: 95%;
          background: #fff;
          border-radius: 1.5rem;
          box-shadow: 0 18px 40px rgba(57, 57, 99, 0.1);
          padding: 2.5rem 3rem;
          margin: 2rem auto 3rem;
          display: flex;
          flex-direction: column;
          gap: 2.5rem;
          transition: box-shadow 0.3s ease;
        }
        .container:hover {
          box-shadow: 0 24px 60px rgba(57, 57, 99, 0.15);
        }
    
        /* بيانات التذكرة */
        .header-card {
          background: linear-gradient(135deg, #404574, #2e3153);
          color: #ffca28;
          padding: 2.2rem 2.5rem;
          border-radius: 1.5rem;
          box-shadow: 0 10px 28px rgba(57, 57, 99, 0.35);
          transition: background 0.4s ease;
          user-select: none;
        }
        .header-card:hover {
          background: linear-gradient(135deg, #343969, #252a48);
        }
        .header-card h2 {
          font-weight: 700;
          font-size: 2.1rem;
          margin-bottom: 0.8rem;
          letter-spacing: 0.03em;
          text-shadow: 0 0 6px rgba(255, 202, 40, 0.7);
        }
        .ticket-info {
          display: flex;
          flex-wrap: wrap;
          gap: 1.8rem 3rem;
          font-weight: 600;
          font-size: 1rem;
        }
        .ticket-info span {
          user-select: none;
          color: #ffd54fcc;
          white-space: nowrap;
          display: flex;
          align-items: center;
          gap: 0.5rem;
        }
        .ticket-info span:last-child {
          color: #ddd;
          font-weight: 500;
          white-space: normal;
        }
        .ticket-info .badge {
          font-size: 0.9rem;
          padding: 0.4rem 1rem;
          font-weight: 700;
          border-radius: 15px;
          text-transform: uppercase;
          letter-spacing: 0.05em;
          box-shadow: 0 3px 8px rgb(255 202 40 / 0.6);
          transition: background-color 0.3s ease;
          user-select: none;
        }
        .badge-open {
          background-color: #4caf50;
          color: #fff;
        }
        .badge-closed {
          background-color: #e53935;
          color: #fff;
        }
        .badge-pending {
          background-color: #fb8c00;
          color: #fff;
        }
        .ticket-priority.high {
          color: #ff5252;
          font-weight: 700;
          text-shadow: 0 0 8px #ff5252;
        }
        .ticket-priority.medium {
          color: #ffb300;
          font-weight: 700;
          text-shadow: 0 0 8px #ffb300;
        }
        .ticket-priority.low {
          color: #43a047;
          font-weight: 700;
          text-shadow: 0 0 8px #43a047;
        }
    
        /* الصورة المرفقة */
        .attachment-label {
          margin-top: 1.2rem;
          font-weight: 700;
          font-size: 1.2rem;
          user-select: none;
          letter-spacing: 0.04em;
          color: #ffca28dd;
        }
        .file-attachment {
          margin-top: 0.8rem;
          max-width: 180px;
          max-height: 140px;
          border-radius: 1.3rem;
          object-fit: cover;
          box-shadow: 0 12px 32px rgb(57 57 99 / 0.18);
          cursor: pointer;
          transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
          border: 3px solid transparent;
          user-select: none;
        }
        .file-attachment:hover {
          transform: scale(1.08);
          border-color: #ffca28;
          box-shadow: 0 14px 36px rgb(255 202 40 / 0.55);
        }
    
        /* محادثة */
        .chat-container {
          background: #fefefe;
          height: 460px;
          border-radius: 1.5rem;
          box-shadow: 0 14px 38px rgb(57 57 99 / 0.12);
          overflow-y: auto;
          padding: 1.8rem 2.3rem;
          display: flex;
          flex-direction: column;
          gap: 1.3rem;
          scrollbar-width: thin;
          scrollbar-color: #ffca28 #f0f2f7;
          scroll-behavior: smooth;
          font-size: 1.1rem;
          line-height: 1.5;
          user-select: text;
        }
        .chat-container::-webkit-scrollbar {
          width: 12px;
        }
        .chat-container::-webkit-scrollbar-track {
          background: #f0f2f7;
          border-radius: 8px;
        }
        .chat-container::-webkit-scrollbar-thumb {
          background-color: #ffca28;
          border-radius: 8px;
          border: 3px solid #f0f2f7;
        }
    
        .message {
          max-width: 70%;
          padding: 1.2rem 1.6rem;
          border-radius: 1.7rem;
          font-size: 1rem;
          word-wrap: break-word;
          position: relative;
          box-shadow: 0 6px 14px rgb(57 57 99 / 0.08);
          transition: background-color 0.3s ease, box-shadow 0.3s ease;
          line-height: 1.4;
          user-select: text;
          white-space: pre-wrap;
        }
        .message.user {
          background: linear-gradient(145deg, #fff9c4, #ffeb3b);
          color: #3b3f72;
          margin-left: auto;
          border-bottom-right-radius: 0.4rem;
          box-shadow: 0 9px 20px rgb(255 202 40 / 0.4);
        }
        .message.user:hover {
          background: linear-gradient(145deg, #fff59d, #ffca28);
          box-shadow: 0 12px 26px rgb(255 202 40 / 0.6);
        }
        .message.staff {
          background: linear-gradient(145deg, #414773, #303656);
          color: #ffca28;
          margin-right: auto;
          border-bottom-left-radius: 0.4rem;
          box-shadow: 0 9px 20px rgb(57 57 99 / 0.45);
        }
        .message.staff:hover {
          background: linear-gradient(145deg, #4b5185, #3a3e6a);
          box-shadow: 0 12px 26px rgb(57 57 99 / 0.65);
        }
        .message .timestamp {
          font-size: 0.78rem;
          color: rgba(255, 202, 40, 0.85);
          margin-top: 0.45rem;
          user-select: none;
          font-weight: 700;
          letter-spacing: 0.04em;
        }
    
        /* مرفقات داخل الرسالة */
        .message img.file-attachment {
          margin-top: 0.7rem;
          max-width: 160px;
          border-radius: 1.3rem;
          box-shadow: 0 9px 25px rgb(57 57 99 / 0.18);
          cursor: pointer;
          transition: transform 0.3s ease, border-color 0.3s ease;
          border: 3px solid transparent;
          user-select: none;
        }
        .message img.file-attachment:hover {
          transform: scale(1.12);
          border-color: #ffca28;
          box-shadow: 0 15px 35px rgb(255 202 40 / 0.55);
        }
        .message a {
          color: #ffca28;
          font-weight: 700;
          display: inline-block;
          margin-top: 0.6rem;
          text-decoration: none;
          transition: color 0.3s ease;
        }
        .message a:hover {
          color: #e6b800;
          text-decoration: underline;
        }
    
        /* الحقول النصية */
        .form-control {
          font-family: 'Cairo', sans-serif;
          border-radius: 0.9rem;
          font-weight: 600;
          font-size: 1rem;
          padding: 0.9rem 1.2rem;
          border: 2.5px solid #ffca28;
          background-color: #fff;
          transition: border-color 0.3s ease, box-shadow 0.3s ease;
          color: #3b3f72;
        }
        .form-control:focus {
          outline: none;
          border-color: #ffca28cc;
          box-shadow: 0 0 8px #ffca28aa;
          background-color: #fff8e1;
        }
    
        /* أزرار */
        .btn-custom {
          background: linear-gradient(45deg, #ffca28, #fbc02d);
          border-radius: 1.1rem;
          border: none;
          font-weight: 700;
          color: #3b3f72;
          font-size: 1.1rem;
          padding: 0.6rem 1.8rem;
          cursor: pointer;
          transition: background 0.3s ease, box-shadow 0.3s ease;
          box-shadow: 0 6px 14px rgb(255 202 40 / 0.5);
          user-select: none;
        }
        .btn-custom:hover {
          background: linear-gradient(45deg, #fbc02d, #ffca28);
          box-shadow: 0 10px 25px rgb(255 202 40 / 0.75);
        }
        .btn-cancel {
          background: #d32f2f;
          color: #fff;
          border-radius: 1rem;
          font-weight: 600;
          padding: 0.55rem 1.6rem;
          box-shadow: 0 6px 14px rgb(211 47 47 / 0.6);
          transition: background-color 0.3s ease;
          user-select: none;
        }
        .btn-cancel:hover {
          background: #b71c1c;
          box-shadow: 0 10px 22px rgb(183 28 28 / 0.8);
        }
    
        /* صندوق النص مع زر الإرسال */
        .input-group {
          margin-top: 0.7rem;
          gap: 0.6rem;
          display: flex;
          flex-wrap: nowrap;
        }
        .input-group textarea {
          flex-grow: 1;
          resize: vertical;
          min-height: 70px;
          max-height: 160px;
          border-radius: 1rem;
          font-family: 'Cairo', sans-serif;
          font-size: 1.05rem;
          padding: 1.1rem 1.3rem;
          border: 2.5px solid #ffca28;
          transition: border-color 0.3s ease, box-shadow 0.3s ease;
          color: #3b3f72;
        }
        .input-group textarea:focus {
          outline: none;
          border-color: #ffca28cc;
          box-shadow: 0 0 10px #ffca28aa;
          background-color: #fff8e1;
        }
        .input-group button {
          background: #ffca28;
          border-radius: 1.2rem;
          border: none;
          padding: 0 1.5rem;
          font-weight: 700;
          font-size: 1.25rem;
          cursor: pointer;
          box-shadow: 0 8px 20px rgb(255 202 40 / 0.6);
          transition: background-color 0.3s ease, box-shadow 0.3s ease;
          user-select: none;
          color: #3b3f72;
        }
        .input-group button:hover {
          background: #fbc02d;
          box-shadow: 0 10px 28px rgb(255 202 40 / 0.85);
        }
    
        /* التوافق مع الشاشات الصغيرة */
        @media (max-width: 760px) {
          .container {
            padding: 1.6rem 1.8rem;
            margin: 1.5rem auto 2rem;
          }
          .header-card h2 {
            font-size: 1.75rem;
          }
          .ticket-info {
            gap: 1.2rem 1.8rem;
            font-size: 0.9rem;
          }
          .chat-container {
            height: 340px;
            padding: 1.3rem 1.8rem;
            font-size: 1rem;
          }
          .message {
            max-width: 85%;
            font-size: 0.95rem;
          }
          .input-group textarea {
            font-size: 1rem;
            min-height: 60px;
          }
          .input-group button {
            font-size: 1.1rem;
            padding: 0 1.2rem;
          }
        }
      </style>
    </head>
    <body>
      <div class="container">
        <div class="header-card">
          <h2>تفاصيل التذكرة #12345</h2>
          <div class="ticket-info">
            <span>التاريخ: 11 يوليو 2025</span>
            <span>الحالة: <span class="badge badge-open">مفتوحة</span></span>
            <span>الأولوية: <span class="ticket-priority high">عالية</span></span>
            <span>المستخدم: أحمد محمد</span>
          </div>
          <div class="attachment-label">مرفق صورة:</div>
          <img src="https://via.placeholder.com/180x140.png?text=الصورة" alt="مرفق" class="file-attachment" />
        </div>


        <div class="form-group col-md-4">
            <label for="department" class="form-control-label">{{ __('messages.department') }}</label>
            <select name="department" id="department" class="select2js form-control">
                <option value="support" {{ old('department', $ticket->department ?? '') == 'support' ? 'selected' : '' }}>{{ __('messages.support_department') }}</option>
                <option value="sales" {{ old('department', $ticket->department ?? '') == 'sales' ? 'selected' : '' }}>{{ __('messages.sales_department') }}</option>
                <option value="billing" {{ old('department', $ticket->department ?? '') == 'billing' ? 'selected' : '' }}>{{ __('messages.billing_department') }}</option>
            </select>
        </div>
        
        <div class="form-group col-md-4">
            <label for="employee" class="form-control-label">{{ __('messages.employee') }}</label>
            <select name="employee" id="employee" class="select2js form-control">
                <option value="ahmed" {{ old('employee', $ticket->employee ?? '') == 'ahmed' ? 'selected' : '' }}>{{ __('messages.ahmed_ali') }}</option>
                <option value="mona" {{ old('employee', $ticket->employee ?? '') == 'mona' ? 'selected' : '' }}>{{ __('messages.mona_saeed') }}</option>
                <option value="fadi" {{ old('employee', $ticket->employee ?? '') == 'fadi' ? 'selected' : '' }}>{{ __('messages.fadi_khaled') }}</option>
            </select>
        </div>
        
        <div class="form-group col-md-4">
            <label for="priority" class="form-control-label">{{ __('messages.priority') }}</label>
            <select name="priority" id="priority" class="select2js form-control">
                <option value="low" {{ old('priority', $ticket->priority ?? '') == 'low' ? 'selected' : '' }}>{{ __('messages.low') }}</option>
                <option value="medium" {{ old('priority', $ticket->priority ?? '') == 'medium' ? 'selected' : '' }}>{{ __('messages.medium') }}</option>
                <option value="high" {{ old('priority', $ticket->priority ?? '') == 'high' ? 'selected' : '' }}>{{ __('messages.high') }}</option>
            </select>
        </div>
        
    
        <div class="chat-container" role="log" aria-live="polite" aria-relevant="additions">
          <div class="message staff">
            مرحبًا، كيف يمكنني مساعدتك اليوم؟
            <div class="timestamp">10:15 صباحًا</div>
          </div>
          <div class="message user">
            لدي مشكلة في الدخول إلى حسابي.
            <div class="timestamp">10:17 صباحًا</div>
          </div>
          <div class="message staff">
            شكرًا لإبلاغنا. هل يمكنك تزويدنا بتفاصيل الخطأ الذي يظهر لك؟
            <div class="timestamp">10:20 صباحًا</div>
          </div>
          <div class="message user">
            تظهر لي رسالة "اسم المستخدم أو كلمة المرور غير صحيحة".
            <div class="timestamp">10:22 صباحًا</div>
          </div>
        </div>
    
        <form>
          <div class="input-group">
            <textarea placeholder="اكتب ردك هنا..." rows="3" aria-label="رسالة الرد"></textarea>
            <button type="submit" aria-label="إرسال الرد">إرسال</button>
          </div>
          <button type="button" class="btn-cancel mt-3">إغلاق التذكرة</button>
        </form>

            <!-- سجل النشاط -->
    <div class="activity-log">
        <strong>سجل النشاط:</strong><br />
        - 2025-07-10 14:23: تم إنشاء التذكرة بواسطة محمد أحمد.<br />
        - 2025-07-10 14:27: تم الرد بواسطة موظف الدعم.<br />
        - 2025-07-10 14:35: تم رفع ملف مرفق.<br />
        - 2025-07-11 09:10: تم تحديث حالة التذكرة.
      </div>
    </div>
      </div>
      
    </body>
    </x-master-layout>
    