

<x-master-layout>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>تفاصيل التذكرة - تصميم محسن</title>

        <!-- مكتبات خارجية -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600&display=swap');

        

            :root {
    --primary: #3a3a58; /* الأزرق الداكن */
    --danger: #e74c3c;
    --success: #2ecc71;
    --pending: #ffcc00; /* الأصفر */
    --light: #f9f9fb;
    --gray: #7f8c8d;
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
                background: linear-gradient(135deg, #ffffff, #f5f7fa);
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

         .chat-container {
    background-color: #ffffff;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    padding: 20px;
    max-height: 460px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.message {
    position: relative;
    padding: 14px 18px;
    max-width: 75%;
    border-radius: 18px;
    font-size: 0.95rem;
    line-height: 1.6;
    word-wrap: break-word;
    word-break: break-word;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    animation: fadeIn 0.3s ease-in-out;
}

.message.staff {
    background: linear-gradient(135deg, #eaf3ff, #d4e6ff);
    margin-right: auto;
    border-left: 4px solid #3498db;
    color: #2c3e50;
}

.message.user {
    background: linear-gradient(135deg, #fff7e0, #fff0c2);
    margin-left: auto;
    border-right: 4px solid #f39c12;
    color: #2c3e50;
}

/* السهم */
.message::before {
    content: '';
    position: absolute;
    top: 14px;
    width: 0;
    height: 0;
    border: 8px solid transparent;
}

.message.staff::before {
    left: -16px;
    border-right-color: #eaf3ff;
}

.message.user::before {
    right: -16px;
    border-left-color: #fff7e0;
}

.timestamp {
    margin-top: 8px;
    font-size: 0.75rem;
    color: #7f8c8d;
    display: flex;
    align-items: center;
    gap: 6px;
}

.message img {
    margin-top: 12px;
    max-width: 200px;
    border-radius: 10px;
    border: 1px solid #eee;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    transition: transform 0.2s ease;
}

.message img:hover {
    transform: scale(1.05);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}


            .timestamp {
                font-size: 0.75rem;
                color: var(--gray);
                margin-top: 8px;
            }

            .btn-primary {
                background-color: var(--primary);
                border: none;
                border-radius: 10px;
                font-weight: 600;
                padding: 10px 20px;
            }

            .btn-primary:hover {
                background-color: #357ab8;
            }

            .btn-cancel {
                background-color: var(--danger);
                color: #fff;
                padding: 10px 20px;
                border-radius: 10px;
                font-weight: 600;
                border: none;
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

            .btn-copy {
                background-color: #fff;
                border: 1px solid #ccc;
                border-radius: 50%;
                width: 36px;
                height: 36px;
                display: flex;
                align-items: center;
                justify-content: center;
                position: absolute;
                top: 15px;
                right: 15px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.05);
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




        </style>

        <script>
            function copyTicketId(id) {
                navigator.clipboard.writeText(`#${id}`);
                alert("تم نسخ رقم التذكرة إلى الحافظة");
            }

            $(document).ready(function () {
                $('#refreshReplies').on('click', function () {
                    $.ajax({
                        url: "{{ route('tickets.replies', $ticket->id) }}",
                        method: 'GET',
                        success: function (response) {
                            let container = '';
                            response.replies.forEach(function (reply) {
                                container += `
                                    <div class="message ${reply.sender_type.includes('Employee') ? 'staff' : 'user'}">
                                        ${reply.content.replace(/\n/g, '<br>')}
                                        ${reply.imageUrl ? `<div class="mt-2"><img src="/storage/${reply.imageUrl}" style="max-width: 200px; border-radius: 8px;"></div>` : ''}
                                        <div class="timestamp"><i class="far fa-clock"></i> ${reply.created_at}</div>
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
        </script>
    </head>

    <body>
        <div class="container">

            {{-- 🧾 Header --}}
            <div class="header-card">
                <button class="btn-copy" onclick="copyTicketId({{ $ticket->id }})" title="نسخ رقم التذكرة">
                    <i class="fas fa-copy"></i>
                </button>

                <h2><i class="fas fa-ticket-alt me-2"></i> تفاصيل التذكرة #{{ $ticket->id }}</h2>

                <div class="ticket-info d-grid gap-3 mt-4" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
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

                @if(!empty($ticket->photo))
                    <div class="attachment-box mt-4">
                        <strong><i class="fas fa-paperclip"></i> مرفق:</strong><br>
                        <img src="{{ asset('storage/' . $ticket->photo) }}" class="file-attachment mt-2" />
                    </div>
                @endif
            </div>

       
            {{-- Ticket Edit --}}
            <form method="POST" action="{{ route('tickets.update', $ticket->id) }}">
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
                                <option value="{{ $emp->id }}" {{ $ticket->assigned_to == $emp->id ? 'selected' : '' }}>
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

            

            <div id="replies-wrapper">
                <div class="chat-container" id="chat-container">
                    @foreach ($ticket->replies as $reply)
                        <div class="message {{ $reply->sender_type == 'App\\Models\\Employee' ? 'staff' : 'user' }}">
                            {!! nl2br(e($reply->content)) !!}
                            @if($reply->imageUrl)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $reply->imageUrl) }}" style="max-width: 200px; border-radius: 8px;">
                                </div>
                            @endif
                            <div class="timestamp"><i class="far fa-clock"></i> {{ $reply->created_at->format('h:i A - Y/m/d') }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Reply Form --}}
            <form id="ajax-reply-form" enctype="multipart/form-data" class="chat-input-box">
                @csrf
            
                <textarea name="reply" id="reply-text" class="form-control" placeholder="اكتب ردك هنا..."></textarea>
            
                <input type="file" id="image-upload" name="image" />
                <label for="image-upload" class="upload-label">
                    <i class="fas fa-image"></i> مرفق
                </label>
            
                <button type="submit" class="btn-send">
                    <i class="fas fa-paper-plane"></i> إرسال
                </button>
            </form>
            

            {{-- Close Ticket --}}
            <form method="POST" action="{{ route('tickets.close', $ticket->id) }}" class="mt-3">
                @csrf
                <button type="submit" class="btn-cancel"><i class="fas fa-times-circle"></i> إغلاق التذكرة</button>
            </form>

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

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('ajax-reply-form');
                const chatContainer = document.getElementById('chat-container');

                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const replyText = document.getElementById('reply-text').value.trim();
                    if (!replyText) {
                        alert('الرجاء كتابة رد.');
                        return;
                    }

                    const formData = new FormData(form);

                    fetch("{{ route('tickets.reply.ajax', $ticket->id) }}", {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const reply = data.reply;

                            const div = document.createElement('div');
                            div.classList.add('message', reply.sender_type.includes('Employee') ? 'staff' : 'user');

                            let html = reply.content;
                            if (reply.imageUrl) {
                                html += `<div class="mt-2"><img src="${reply.imageUrl}" style="max-width: 200px; border-radius: 8px;"></div>`;
                            }
                            html += `<div class="timestamp"><i class="far fa-clock"></i> ${reply.timestamp}</div>`;
                            div.innerHTML = html;

                            chatContainer.appendChild(div);
                            chatContainer.scrollTop = chatContainer.scrollHeight;
                            form.reset();
                        } else {
                            alert('حدث خطأ أثناء إرسال الرد.');
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        alert('فشل الاتصال بالخادم.');
                    });
                });
            });
        </script>
        </div>
    </body>
</x-master-layout>
