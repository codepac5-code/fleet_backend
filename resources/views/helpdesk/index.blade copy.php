<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>قائمة التذاكر - نظام الدعم الفني</title>

<!-- خطوط -->
<link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet" />

<!-- أيقونات فونت أوسم -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

<style>
  /* RESET */
  * {
    box-sizing: border-box;
  }
  body {
    font-family: 'Roboto', sans-serif;
    background: #f5f7fa;
    color: #222;
    margin: 0; padding: 0;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    padding: 1rem;
  }

  /* HEADER BAR */
  header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 0.5rem;
  }
  header h1 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #0a4a71;
  }
  header .actions {
    display: flex;
    gap: 0.75rem;
  }
  button, select, input[type="text"] {
    font-family: inherit;
    font-size: 1rem;
  }
  button {
    cursor: pointer;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    background-color: #007bff;
    color: white;
    transition: background-color 0.3s ease;
  }
  button:hover {
    background-color: #0056b3;
  }
  button i {
    margin-left: 0.3rem;
  }

  /* SEARCH & FILTERS BAR */
  .filters {
    background: white;
    border-radius: 10px;
    padding: 1rem;
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: center;
    margin-bottom: 1rem;
    box-shadow: 0 1px 6px rgb(0 0 0 / 0.1);
  }
  .filters input[type="text"] {
    flex: 1 1 250px;
    padding: 0.5rem 0.75rem;
    border: 1px solid #ccc;
    border-radius: 6px;
  }
  .filters select {
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    border: 1px solid #ccc;
    background-color: white;
  }
  .filters .filter-actions {
    display: flex;
    gap: 0.5rem;
  }
  .filters button.clear {
    background-color: #dc3545;
  }
  .filters button.clear:hover {
    background-color: #a71d2a;
  }

  /* TABLE */
  table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 1px 6px rgb(0 0 0 / 0.1);
  }
  th, td {
    text-align: right;
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    font-size: 0.95rem;
  }
  th {
    background-color: #0a4a71;
    color: white;
    font-weight: 700;
    user-select: none;
  }
  tr:hover {
    background-color: #f1faff;
  }

  /* Ticket ID and Title as links */
  a.ticket-link {
    color: #007bff;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    max-width: 300px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  a.ticket-link:hover {
    text-decoration: underline;
  }

  /* Status badges */
  .status {
    display: inline-block;
    padding: 0.2rem 0.7rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    color: white;
  }
  .status.open { background-color: #28a745; }        /* أخضر */
  .status.processing { background-color: #ffc107; }  /* أصفر */
  .status.closed { background-color: #6c757d; }      /* رمادي */

  /* Assigned Agent */
  .agent {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  .agent img {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
  }

  /* View button */
  .view-btn {
    background-color: #17a2b8;
    padding: 0.3rem 0.8rem;
    border-radius: 6px;
    font-size: 0.85rem;
    color: white;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }
  .view-btn:hover {
    background-color: #117a8b;
  }

  /* PAGINATION */
  .pagination {
    margin-top: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
  }
  .pagination .pages {
    display: flex;
    gap: 0.4rem;
  }
  .pagination button.page-btn {
    padding: 0.4rem 0.8rem;
    border: 1px solid #0a4a71;
    border-radius: 6px;
    background-color: white;
    cursor: pointer;
    font-weight: 600;
  }
  .pagination button.page-btn.active,
  .pagination button.page-btn:hover {
    background-color: #0a4a71;
    color: white;
  }

  /* Items per page select */
  .pagination select {
    padding: 0.3rem 0.6rem;
    border-radius: 6px;
    border: 1px solid #ccc;
  }

  /* Status messages */
  .status-message {
    text-align: center;
    padding: 1rem;
    font-size: 1.1rem;
    font-weight: 600;
    color: #dc3545;
  }

  /* Responsive */
  @media (max-width: 768px) {
    header {
      flex-direction: column;
      align-items: flex-start;
    }
    .filters {
      flex-direction: column;
      gap: 0.75rem;
    }
    table, th, td {
      font-size: 0.85rem;
    }
    a.ticket-link {
      max-width: 150px;
    }
  }
</style>
</head>
<body>

<header>
  <h1>قائمة التذاكر</h1>
  <div class="actions">
    <button id="refreshBtn" title="تحديث القائمة"><i class="fas fa-sync-alt"></i> تحديث</button>
    <button id="createTicketBtn" title="إنشاء تذكرة جديدة"><i class="fas fa-plus"></i> تذكرة جديدة</button>
  </div>
</header>

<section class="filters" aria-label="شريط البحث والفلاتر">
  <input type="text" id="searchInput" placeholder="ابحث حسب البريد، العنوان، أو رقم التذكرة..." aria-label="بحث" />
  <select id="statusFilter" aria-label="فلتر الحالة">
    <option value="">كل الحالات</option>
    <option value="open">جديدة</option>
    <option value="processing">قيد المعالجة</option>
    <option value="closed">مغلقة</option>
  </select>
  <select id="departmentFilter" aria-label="فلتر القسم">
    <option value="">كل الأقسام</option>
    <option value="support">الدعم الفني</option>
    <option value="finance">المالية</option>
    <option value="sales">المبيعات</option>
  </select>
  <select id="agentFilter" aria-label="فلتر الموظف المعالج">
    <option value="">كل الموظفين</option>
    <option value="agent1">أحمد علي</option>
    <option value="agent2">سارة محمد</option>
    <option value="agent3">خالد يوسف</option>
  </select>
  <select id="priorityFilter" aria-label="فلتر الأولوية">
    <option value="">كل الأولويات</option>
    <option value="high">عالية</option>
    <option value="medium">متوسطة</option>
    <option value="low">منخفضة</option>
  </select>

  <div class="filter-actions">
    <button id="applyFiltersBtn" title="تطبيق الفلاتر"><i class="fas fa-filter"></i> تطبيق</button>
    <button id="clearFiltersBtn" class="clear" title="مسح الفلاتر"><i class="fas fa-times"></i> مسح</button>
  </div>
</section>

<main>
  <table aria-label="جدول قائمة التذاكر">
    <thead>
      <tr>
        <th scope="col">رقم التذكرة</th>
        <th scope="col">عنوان التذكرة</th>
        <th scope="col">الحالة</th>
        <th scope="col">القسم</th>
        <th scope="col">الموظف المعالج</th>
        <th scope="col">آخر تحديث</th>
        <th scope="col" style="width: 100px;">العمليات</th>
      </tr>
    </thead>
    <tbody id="ticketsTableBody">
      <!-- سيتم تحميل البيانات هنا ديناميكياً -->
    </tbody>
  </table>

  <div class="status-message" id="statusMessage" style="display:none;"></div>

  <div class="pagination" aria-label="تنقل بين صفحات التذاكر">
    <div class="pages" id="paginationButtons"></div>
    <div>
      <label for="itemsPerPage">عدد التذاكر في الصفحة:</label>
      <select id="itemsPerPage" aria-label="عدد التذاكر في الصفحة">
        <option value="10">10</option>
        <option value="25" selected>25</option>
        <option value="50">50</option>
      </select>
    </div>
  </div>
</main>

<script>
  // بيانات تجريبية للتذاكر
  const ticketsData = [
    {
      id: 'TKT-1001',
      title: 'مشكلة في تسجيل الدخول',
      status: 'open',
      department: 'support',
      agent: 'agent1',
      agentName: 'أحمد علي',
      agentImg: 'https://randomuser.me/api/portraits/men/32.jpg',
      priority: 'high',
      lastUpdated: '2025-07-11T09:30:00'
    },
    {
      id: 'TKT-1002',
      title: 'استفسار عن الفاتورة الشهرية',
      status: 'processing',
      department: 'finance',
      agent: 'agent2',
      agentName: 'سارة محمد',
      agentImg: 'https://randomuser.me/api/portraits/women/44.jpg',
      priority: 'medium',
      lastUpdated: '2025-07-10T15:00:00'
    },
    {
      id: 'TKT-1003',
      title: 'طلب ترقية الحساب',
      status: 'closed',
      department: 'sales',
      agent: 'agent3',
      agentName: 'خالد يوسف',
      agentImg: 'https://randomuser.me/api/portraits/men/52.jpg',
      priority: 'low',
      lastUpdated: '2025-07-08T11:20:00'
    },
    // أضف المزيد حسب الحاجة
  ];

  // متغيرات الحالة
  let currentPage = 1;
  let itemsPerPage = parseInt(document.getElementById('itemsPerPage').value);

  // الدالة لتحويل تاريخ إلى صيغة "قبل X ساعات"
  function timeAgo(dateString) {
    const now = new Date();
    const past = new Date(dateString);
    const diffMs = now - past;
    const diffMinutes = Math.floor(diffMs / 60000);
    if(diffMinutes < 1) return 'قبل أقل من دقيقة';
    if(diffMinutes < 60) return `قبل ${diffMinutes} دقيقة${diffMinutes > 1 ? 'ً' : ''}`;
    const diffHours = Math.floor(diffMinutes / 60);
    if(diffHours < 24) return `قبل ${diffHours} ساعة${diffHours > 1 ? 'ً' : ''}`;
    const diffDays = Math.floor(diffHours / 24);
    return `قبل ${diffDays} يوم${diffDays > 1 ? 'ً' : ''}`;
  }

  // دالة تصفية البيانات حسب الفلاتر والنص
  function filterTickets() {
    const searchText = document.getElementById('searchInput').value.trim().toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const departmentFilter = document.getElementById('departmentFilter').value;
    const agentFilter = document.getElementById('agentFilter').value;
    const priorityFilter = document.getElementById('priorityFilter').value;

    return ticketsData.filter(ticket => {
      const matchesSearch = ticket.id.toLowerCase().includes(searchText) ||
                            ticket.title.toLowerCase().includes(searchText) ||
                            ticket.agentName.toLowerCase().includes(searchText);
      const matchesStatus = statusFilter === '' || ticket.status === statusFilter;
      const matchesDepartment = departmentFilter === '' || ticket.department === departmentFilter;
      const matchesAgent = agentFilter === '' || ticket.agent === agentFilter;
      const matchesPriority = priorityFilter === '' || ticket.priority === priorityFilter;

      return matchesSearch && matchesStatus && matchesDepartment && matchesAgent && matchesPriority;
    });
  }

  // دالة عرض التذاكر في الجدول
  function renderTickets() {
    const tbody = document.getElementById('ticketsTableBody');
    const statusMessage = document.getElementById('statusMessage');
    tbody.innerHTML = '';
    statusMessage.style.display = 'none';

    const filteredTickets = filterTickets();

    if(filteredTickets.length === 0) {
      statusMessage.textContent = 'لا توجد تذاكر مطابقة للمعايير المحددة';
      statusMessage.style.display = 'block';
      renderPagination(0);
      return;
    }

    // حساب الصفوف للصفحة الحالية
    const start = (currentPage -1) * itemsPerPage;
    const end = start + itemsPerPage;
    const pageTickets = filteredTickets.slice(start, end);

    for (const ticket of pageTickets) {
      const tr = document.createElement('tr');

      // رقم التذكرة (رابط)
      const tdId = document.createElement('td');
      const idLink = document.createElement('a');
      idLink.href = '#';
      idLink.className = 'ticket-link';
      idLink.textContent = ticket.id;
      idLink.setAttribute('aria-label', `التفاصيل عن التذكرة رقم ${ticket.id}`);
      idLink.onclick = (e) => {
        e.preventDefault();
        alert(`فتح صفحة تفاصيل التذكرة: ${ticket.id}`);
      };
      tdId.appendChild(idLink);
      tr.appendChild(tdId);

      // عنوان التذكرة (رابط)
      const tdTitle = document.createElement('td');
      const titleLink = document.createElement('a');
      titleLink.href = '#';
      titleLink.className = 'ticket-link';
      titleLink.textContent = ticket.title;
      titleLink.setAttribute('aria-label', `التفاصيل عن التذكرة: ${ticket.title}`);
      titleLink.onclick = (e) => {
        e.preventDefault();
        alert(`فتح صفحة تفاصيل التذكرة: ${ticket.title}`);
      };
      tdTitle.appendChild(titleLink);
      tr.appendChild(tdTitle);

      // الحالة
      const tdStatus = document.createElement('td');
      const spanStatus = document.createElement('span');
      spanStatus.className = `status ${ticket.status}`;
      spanStatus.textContent = {
        open: 'جديدة',
        processing: 'قيد المعالجة',
        closed: 'مغلقة'
      }[ticket.status] || ticket.status;
      tdStatus.appendChild(spanStatus);
      tr.appendChild(tdStatus);

      // القسم
      const tdDept = document.createElement('td');
      tdDept.textContent = {
        support: 'الدعم الفني',
        finance: 'المالية',
        sales: 'المبيعات'
      }[ticket.department] || ticket.department;
      tr.appendChild(tdDept);

      // الموظف المعالج مع صورة
      const tdAgent = document.createElement('td');
      const divAgent = document.createElement('div');
      divAgent.className = 'agent';
      const imgAgent = document.createElement('img');
      imgAgent.src = ticket.agentImg;
      imgAgent.alt = `صورة ${ticket.agentName}`;
      divAgent.appendChild(imgAgent);
      const spanAgentName = document.createElement('span');
      spanAgentName.textContent = ticket.agentName;
      divAgent.appendChild(spanAgentName);
      tdAgent.appendChild(divAgent);
      tr.appendChild(tdAgent);

      const tdUpdated = document.createElement('td');
      tdUpdated.textContent = timeAgo(ticket.lastUpdated);
      tr.appendChild(tdUpdated);

      const tdView = document.createElement('td');
      const btnView = document.createElement('button');
      btnView.className = 'view-btn';
      btnView.textContent = 'عرض';
      btnView.onclick = () => alert(`فتح صفحة تفاصيل التذكرة: ${ticket.id}`);
      btnView.setAttribute('aria-label', `عرض تفاصيل التذكرة ${ticket.id}`);
      tdView.appendChild(btnView);
      tr.appendChild(tdView);

      tbody.appendChild(tr);
    }

    renderPagination(filteredTickets.length);
  }

  function renderPagination(totalItems) {
    const paginationDiv = document.getElementById('paginationButtons');
    paginationDiv.innerHTML = '';

    if (totalItems === 0) return;

    const totalPages = Math.ceil(totalItems / itemsPerPage);

    let startPage = 1;
    let endPage = totalPages;

    if (totalPages > 7) {
      if (currentPage <= 4) {
        endPage = 7;
      } else if (currentPage + 3 >= totalPages) {
        startPage = totalPages - 6;
      } else {
        startPage = currentPage - 3;
        endPage = currentPage + 3;
      }
    }

    const prevBtn = document.createElement('button');
    prevBtn.className = 'page-btn';
    prevBtn.textContent = '«';
    prevBtn.disabled = currentPage === 1;
    prevBtn.onclick = () => {
      if (currentPage > 1) {
        currentPage--;
        renderTickets();
      }
    };
    paginationDiv.appendChild(prevBtn);

    for(let i = startPage; i <= endPage; i++) {
      const btn = document.createElement('button');
      btn.className = 'page-btn' + (i === currentPage ? ' active' : '');
      btn.textContent = i;
      btn.onclick = () => {
        currentPage = i;
        renderTickets();
      };
      paginationDiv.appendChild(btn);
    }

    const nextBtn = document.createElement('button');
    nextBtn.className = 'page-btn';
    nextBtn.textContent = '»';
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.onclick = () => {
      if (currentPage < totalPages) {
        currentPage++;
        renderTickets();
      }
    };
    paginationDiv.appendChild(nextBtn);

    const totalSpan = document.createElement('span');
    totalSpan.textContent = `إجمالي التذاكر: ${totalItems}`;
    totalSpan.style.marginLeft = '16px';
    paginationDiv.appendChild(totalSpan);
  }

  document.getElementById('itemsPerPage').addEventListener('change', (e) => {
    itemsPerPage = parseInt(e.target.value);
    currentPage = 1;
    renderTickets();
  });

  document.getElementById('refreshBtn').addEventListener('click', () => {
    alert('تحديث قائمة التذاكر');
    renderTickets();
  });

  document.getElementById('createTicketBtn').addEventListener('click', () => {
    alert('فتح نموذج إنشاء تذكرة جديدة');
  });

  ['searchInput', 'statusFilter', 'departmentFilter', 'agentFilter', 'priorityFilter'].forEach(id => {
    document.getElementById(id).addEventListener('input', () => {
      currentPage = 1;
      renderTickets();
    });
  });

  document.getElementById('clearFiltersBtn').addEventListener('click', () => {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('departmentFilter').value = '';
    document.getElementById('agentFilter').value = '';
    document.getElementById('priorityFilter').value = '';
    currentPage = 1;
    renderTickets();
  });

  renderTickets();

</script>

</body>
</html>
