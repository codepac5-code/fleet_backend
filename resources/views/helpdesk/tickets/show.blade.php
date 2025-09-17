

<x-master-layout>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>تفاصيل التذكرة - تصميم محسن</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />


        <style>
            @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600&display=swap');

        



:root.dark-mode {
  --primary: #e3e3ff;
  --danger: #ff6b6b;
  --success: #51cf66;
  --pending: #ffe066;
  --light: #1e1e2f;
  --gray: #aaa;

  --bg-main: #12121c;
  --bg-chat: #1a1a2f;
  --bg-message-user: #2c2c44;
  --bg-message-staff: #3a3a58;
  --text-main: #eaeaea;
  --text-muted: #999;
}

body {
  background-color: var(--bg-main);
  color: var(--text-main);
}
.chat-container {
  background-color: var(--bg-chat);
}
.message.user {
  background-color: var(--bg-message-user);
  color: var(--text-main);
}
.message.staff {
  background-color: var(--bg-message-staff);
  color: var(--primary);
}


.badge-pending {
    background-color: var(--pending);
    color: #212529;
}

.btn-primary {
    background-color: var(--primary);
    border: none;
    border-radius: 10px;
    font-weight: 600;
    padding: 10px 20px;
}

.btn-primary:hover {
    background-color: #2e2e46; /* درجة أغمق قليلاً للأزرق الداكن */
}


            body {
                font-family: 'Cairo', sans-serif;
                background-color: var(--light);
                color: #2c3e50;
                padding: 30px 15px;
            }

            h2, h5 {
                font-weight: 700;
            }

            .header-card {
                background: linear-gradient(135deg, #fbfcfda4, #2e335a2f);
                padding: 30px;
                border-radius: 18px;
                border: 1px solid #dee2e6;
                box-shadow: 0 8px 24px rgba(0,0,0,0.05);
                transition: 0.3s ease-in-out;
                position: relative;
            }

            .header-card:hover {
                transform: scale(1.01);
            }

            .ticket-info {
                font-size: 0.95rem;
                color: #34495e;
            }

            .badge {
                padding: 6px 14px;
                border-radius: 20px;
                font-weight: 600;
                font-size: 0.85rem;
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }

            .badge-open { background-color: var(--success); color: #fff; }
            .badge-closed { background-color: var(--danger); color: #fff; }
            .badge-pending { background-color: var(--pending); color: #212529; }

            .file-attachment {
                max-width: 220px;
                border-radius: 12px;
                border: 1px solid #e0e0e0;
                margin-top: 10px;
                transition: 0.3s;
            }

            .file-attachment:hover {
                box-shadow: 0 4px 12px rgba(0,0,0,0.08);
                transform: scale(1.03);
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
    display: inline-flex;
    flex-wrap: wrap;
    max-width: 70%;
    padding: 1rem 1rem;
    border-radius: 17rem;
    font-size: 1rem;
    word-wrap: break-word;
    overflow-wrap: break-word;
    white-space: pre-wrap;
    position: relative;
    box-shadow: 0 6px 7px rgb(57 57 99 / 0.08);
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
    line-height: 0.7;
    user-select: text;
    vertical-align: top;
}


.message .timestamp.user {
    font-size: 0.78rem;
    color:#3b3f72;
    margin-top: 0.7rem;
    user-select: none;
    /* font-weight: 700; */
    letter-spacing: 0.07em;
    align-self: flex-end;
}

.message .timestamp {
    font-size: 0.78rem;
    color: rgba(255, 202, 40, 0.85);
    margin-top: 0.7rem;
    user-select: none;
    /* font-weight: 700; */
    letter-spacing: 0.07em;
    align-self: flex-end;
}


        .message.user {
          background: linear-gradient(145deg, #f0e269, #ffeb3b);
          color: #3b3f72;
          margin-left: auto;
          border-bottom-right-radius: 0.4rem;
          box-shadow: 0 9px 20px rgb(255 202 40 / 0.4);
        }
        .message.user:hover {
          background: linear-gradient(145deg, #eede4e, #ffc517);
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
        /* .message .timestamp {
          font-size: 0.78rem;
          color: rgba(255, 202, 40, 0.85);
          margin-top: 0.45rem;
          user-select: none;
          font-weight: 700;
          letter-spacing: 0.04em;
        } */
    
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

          


            .form-group label {
                font-weight: 600;
                margin-bottom: 6px;
            }

            .form-control {
                border-radius: 8px;
                font-size: 0.95rem;
                padding: 10px;
            }

            .activity-log {
                background-color: #ffffff;
                padding: 20px;
                border-radius: 16px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.04);
                font-size: 0.9rem;
                color: #555;
            }


            @media (max-width: 768px) {
                .message {
                    font-size: 0.85rem;
                }
            }

.chat-input-box {
    background: #ffffff;
    border-radius: 16px;
    padding: 16px 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    display: flex;
    align-items: flex-end;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 20px;
}

.chat-input-box textarea {
    flex: 1 1 auto;
    resize: none;
    border: 1px solid #dcdcdc;
    border-radius: 12px;
    padding: 10px 14px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    min-height: 70px;
}

.chat-input-box textarea:focus {
    border-color: var(--primary);
    outline: none;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.15);
}

.chat-input-box input[type="file"] {
    display: none;
}

.chat-input-box label.upload-label {
    background: #f1f3f5;
    color: #495057;
    padding: 10px 14px;
    border-radius: 12px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background-color 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.chat-input-box label.upload-label:hover {
    background: #e2e6ea;
}

.chat-input-box .btn-send {
    background-color: var(--primary);
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 12px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: background-color 0.3s ease;
}

.chat-input-box .btn-send:hover {
    background-color: #0069d9;
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
  font-size: 1.2rem;
  padding: 1rem 1rem;
  border-radius: 2.2rem;
  background: #f9fbff;
  color: #3a3f6cb6;
  box-shadow: inset 6px 6px 12px #d9dff0, inset -6px -6px 12px #ffffff;
  transition: background-color 0.35s ease, outline-color 0.35s ease;
  outline-offset: 3px;
  outline-color: transparent;
  font-weight: 600;
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



#chat-box-wrapper {
    display: flex;
    flex-direction: column;
    height: 100%;
    max-height: 600px;
    border: 1px solid #ccc;
    border-radius: 8px;
    overflow: hidden;
}

#chat-container {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
    background-color: #ffcc001c;
}

.input-reply {
    display: flex;
    gap: 8px;
    padding: 10px;
    background-color: #fff;
    border-top: 1px solid #ddd;
}


        </style>

        <script>
          
          
            $(document).ready(function () {
              const userType = @json(get_class(auth()->user()));
                $('#refreshReplies').on('click', function () {
                    $.ajax({
                        url: "{{ route('tickets.replies', $ticket->id) }}",
                        method: 'GET',
                        success: function (response) {
                            let container = '';
                            response.replies.forEach(function (reply) {
                                container += `
                                    <div class="message ${reply.sender_type.includes(userType) ? 'staff' : 'user'}">
                                        ${reply.content.replace(/\n/g, '<br>')}
                                        ${reply.imageUrl ? `<div class="mt-2"><img src="/storage/${reply.imageUrl}" style="max-width: 200px; border-radius: 8px;"></div>` : ''}
                                        <div class="timestamp ${reply.sender_type.includes(userType) ? '' : 'user' }"><i class="far fa-clock"></i> ${reply.created_at}</div>
                                    </div>`;
                            });
                            $('#chat-container').html(container);
                            $('#last-updated').text(new Date().toLocaleTimeString());
                        },
                        error: function () {
                            alert('فشل التحديث، حاول مرة أخرى.');
                        }
                    });
                });
            });



document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('ajax-reply-form');
    const textarea = document.getElementById('reply-text');
    const fileInput = document.getElementById('file-input');
    const attachBtn = document.getElementById('attach-btn');
    const sendBtn = document.getElementById('send-btn');
    const chatContainer = document.getElementById('chat-container');

    const userClass = @json(get_class(auth()->user()));


    textarea.addEventListener('input', function () {
        sendBtn.disabled = textarea.value.trim() === '';
    });

    attachBtn.addEventListener('click', function (e) {
        e.preventDefault();
        fileInput.click();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const text = textarea.value.trim();
        if (!text) return;

        const formData = new FormData(form);

        fetch("{{ route('tickets.reply.ajax', $ticket->id) }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const reply = data.reply;
                const div = document.createElement('div');
                div.classList.add('message', reply.sender_type.includes(userClass) ? 'staff' : 'user');

                let html = reply.content;
                if (reply.imageUrl) {
                    html += `<div class="mt-2"><img src="${reply.imageUrl}" style="max-width: 200px; border-radius: 8px;"></div>`;
                }
                html += `<div class="timestamp"><i class="far fa-clock"></i> ${reply.timestamp}</div>`;
                div.innerHTML = html;

                chatContainer.appendChild(div);
                chatContainer.scrollTop = chatContainer.scrollHeight;

                form.reset();
                sendBtn.disabled = true;
            } else {
                alert('فشل إرسال الرد.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('فشل الاتصال بالخادم.');
        });
    });
});

        </script>



    </head>

    <body>
        <div class="container">

            <div class="header-card" >
                <h2><i class="fas fa-ticket-alt me-2" ></i> {{ $ticket->subject }}</h2>

                <div class="ticket-info d-grid gap-3 mt-4" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); padding-top:20px;">
                    <div><i class="far fa-calendar-alt me-1"></i> <strong>التاريخ:</strong> {{ \Carbon\Carbon::parse($ticket->created_at)->format('d F Y') }}</div>
                    <div><i class="fas fa-info-circle me-1"></i> <strong>الحالة:</strong>
                        @if ($ticket->status == 'open')
                            <span class="badge badge-open"><i class="fas fa-check-circle"></i> مفتوحة</span>
                        @elseif ($ticket->status == 'closed')
                            <span class="badge badge-closed"><i class="fas fa-times-circle"></i> مغلقة</span>
                        @else
                            <span class="badge badge-pending"><i class="fas fa-spinner"></i> قيد المعالجة</span>
                        @endif
                    </div>
                    <div><i class="fas fa-flag me-1"></i> <strong>الأولوية:</strong>
                        <span class="badge bg-secondary text-white">
                            <i class="fas fa-exclamation-triangle me-1"></i> {{ __('messages.' . $ticket->priority) }}
                        </span>
                    </div>
                    <div><i class="fas fa-user me-1"></i> <strong>المستخدم:</strong> {{ $ticket->owner->name ?? 'غير معروف' }}</div>
                </div>


                <div class="mt-4" style="padding-top:20px;">
                    <h5><i class="fas fa-align-right me-1" ></i> وصف المشكلة:</h5>
                    <div class="bg-light border rounded p-3" style="white-space: pre-wrap; padding-top:20px;">
                        {{ $ticket->description }}
                    </div>
                </div>


                @if(!empty($ticket->photo))
                    <div class="attachment-box mt-4">
                        <strong><i class="fas fa-paperclip"></i> مرفق:</strong><br>
                        <img src="{{ asset('storage/' . $ticket->photo) }}" class="file-attachment mt-2" />
                    </div>
                @endif
            </div>

       
            {{-- Ticket Edit --}}
            <form method="POST" action="{{ route('tickets.update', $ticket->id) }}" style="padding-top: 50px;">
                @csrf @method('PUT')
                <div class="row">
                    <div class="form-group col-md-4">
                        <label for="department">القسم</label>
                        <select name="department_id" class="form-control">
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ $ticket->department_id == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="assigned_to">الموظف</label>
                        <select name="assigned_to" class="form-control">
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ optional($ticket->assignedTo)->id == $emp->id ? 'selected' : '' }}>
                                {{ $emp->firstName . ' ' . $emp->lastName }}
                            </option>
                        @endforeach
                        
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="priority">الأولوية</label>
                        <select name="priority" class="form-control">
                            <option value="low" {{ $ticket->priority == 'low' ? 'selected' : '' }}>منخفضة</option>
                            <option value="medium" {{ $ticket->priority == 'medium' ? 'selected' : '' }}>متوسطة</option>
                            <option value="high" {{ $ticket->priority == 'high' ? 'selected' : '' }}>مرتفعة</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-save"></i> حفظ التعديلات</button>
            </form>

            <div class="col-12">
                <div class="horizontal-separator"></div>
            </div>

            {{-- Replies --}}
            <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
                <h5><i class="fas fa-comments"></i> الردود</h5>
                <div>
                    <button id="refreshReplies" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-sync-alt"></i> تحديث الردود
                    </button>
                    <small class="text-muted ms-3">آخر تحديث: <span id="last-updated">{{ now()->format('H:i:s') }}</span></small>
                </div>
            </div>





              
           
        <div>
            <div id="chat-box-wrapper" class="chat-box">
                <div id="replies-wrapper">
                    <div class="chat-container" id="chat-container">
                  


                      @foreach ($ticket->replies as $reply)

                      @php
                          $currentUser = auth()->user();
                          $isStaff = !($reply->sender_type == get_class($currentUser)) && ($reply->sender_id == $currentUser->id);
                      @endphp
                  
                      <div class="message {{ $isStaff ? 'staff' : 'user' }}">
                          <div class="content">
                              {!! nl2br(e($reply->content)) !!}
                  
                              @if($reply->imageUrl)
                                  <div class="mt-2">
                                      <img src="{{ asset('storage/' . $reply->imageUrl) }}" style="max-width: 200px; border-radius: 8px;">
                                  </div>
                              @endif
                          </div>
                  
                          <div class="timestamp {{ $isStaff ? '' : 'user' }}">
                              <i class="far fa-clock"></i> {{ $reply->created_at->format('h:i A - Y/m/d') }}
                          </div>
                      </div>
                  @endforeach
                  

                     
                    </div>

                    <form id="ajax-reply-form" enctype="multipart/form-data" class="input-reply">
                        @csrf
                        <textarea name="reply" id="reply-text" rows="2" placeholder="اكتب ردك هنا..."></textarea>
            
                        <input type="file" id="file-input" name="image" style="display: none;" />
            
                        <button type="button" id="attach-btn" title="إرفاق ملف" aria-label="Attach file"><i class="fas fa-paperclip"></i>
                        </button>
            
                        <button type="submit" id="send-btn" title="إرسال الرد" aria-label="Send reply" disabled><i class="fas fa-location-arrow"></i></button>
                    </form>
                </div>
            </div>
            
    
            
            <div class="col-12">
                <div class="horizontal-separator"></div>
            </div>

            {{-- Close Ticket --}}
            @if (!$ticket->isClosed)
            <button type="button" class="btn-cancel" data-bs-toggle="modal" data-bs-target="#closeTicketModal">
                <i class="fas fa-times-circle"></i> إغلاق التذكرة
              </button>     
            @else
            {{$ticket->closedAt}} تم اغلاقها في:
            @endif

  


            <!-- Modal -->
<div class="modal fade" id="closeTicketModal" tabindex="-1" aria-labelledby="closeTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 shadow">
        <div class="modal-header">
          <h5 class="modal-title" id="closeTicketModalLabel">تأكيد إغلاق التذكرة</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
        </div>
        <div class="modal-body text-center">
          هل أنت متأكد أنك تريد إغلاق هذه التذكرة؟ لا يمكنك التراجع بعد ذلك.
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
          <form method="POST" action="{{ route('tickets.close', $ticket->id) }}">
            @csrf
            <button type="submit" class="btn btn-danger">نعم، إغلاق</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  

            {{-- Logs --}}
            <div class="activity-log mt-4">
                <strong><i class="fas fa-history"></i> سجل النشاط:</strong><br />
                @foreach ($ticket->logs as $log)
                    - {{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i') }}:
                    {{ $log->action }} - {{ $log->employee->firstName ?? 'غير معروف' }}
                    @if($log->note) <br>ملاحظة: {{ $log->note }} @endif
                    <br>
                @endforeach
            </div>

   
        </div>
    </body>
</x-master-layout>
