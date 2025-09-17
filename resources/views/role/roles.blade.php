<x-master-layout>
    <style>
        * {
          box-sizing: border-box;
        }
      
        body, input, button {
          font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }
      
        .container {
          max-width: 1100px;
          margin: 2rem auto;
          padding: 0 1rem;
          direction: inherit;
        }
      
        .tabs {
          display: flex;
          border-bottom: 2px solid #ddd;
          margin-bottom: 1.5rem;
          user-select: none;
        }
      
        .tabs button {
          flex: 1;
          padding: 0.75rem 0;
          background: none;
          border: none;
          border-bottom: 3px solid transparent;
          font-size: 1.1rem;
          color: #444;
          cursor: pointer;
          transition: all 0.3s ease;
        }
      
        .tabs button.active {
          border-color: #ffcc00;
          color: #ffcc00;
          font-weight: 600;
        }
      
        .tabs button:hover:not(.active) {
          color: #ffcc00;
        }
      
        .top-bar {
          display: flex;
          gap: 1rem;
          margin-bottom: 1rem;
          align-items: center;
          flex-wrap: wrap;
        }
      
        .search-input {
          flex: 1;
          padding: 0.5rem 0.75rem;
          font-size: 1rem;
          border: 1px solid #ccc;
          border-radius: 5px;
          transition: border-color 0.3s ease;
        }
      
        .search-input:focus {
          outline: none;
          border-color: #ffcc00;
          box-shadow: 0 0 8px rgba(74,144,226,0.4);
        }
      
        .btn-primary {
          background-color: #ffcc00;
          color: white;
          padding: 0.55rem 1.25rem;
          font-size: 1rem;
          border: none;
          border-radius: 6px;
          cursor: pointer;
          display: flex;
          align-items: center;
          gap: 0.5rem;
          box-shadow: 0 4px 8px rgba(74,144,226,0.3);
          transition: background-color 0.3s ease;
        }
      
        .btn-primary:hover:not(:disabled) {
          background-color: #ffcc00;
        }
      
        .btn-primary:disabled {
          background-color: #a4c2f4;
          cursor: not-allowed;
        }
      
        table {
          width: 100%;
          border-collapse: separate;
          border-spacing: 0 10px;
          font-size: 0.95rem;
          color: #333;
        }
      
        thead tr th {
          text-align: start;
          font-weight: 600;
          padding: 10px 12px;
          background: #f7f9fc;
          color: #555;
          user-select: none;
        }
      
        tbody tr {
          background: white;
          box-shadow: 0 2px 8px rgb(0 0 0 / 0.05);
          border-radius: 8px;
          transition: box-shadow 0.3s ease;
        }
      
        tbody tr:hover {
          box-shadow: 0 4px 16px rgb(74 144 226 / 0.15);
        }
      
        tbody tr td {
          padding: 12px;
          vertical-align: middle;
        }
      
        .edit-input {
          width: 100%;
          padding: 6px 8px;
          font-size: 1rem;
          border-radius: 6px;
          border: 1px solid #ccc;
          transition: border-color 0.3s ease;
        }
      
        .edit-input:focus {
          outline: none;
          border-color: #ffcc00;
          box-shadow: 0 0 8px rgba(74,144,226,0.4);
        }
      
        .action-btn {
          background: none;
          border: none;
          cursor: pointer;
          margin-inline: 4px;
          font-size: 1.15rem;
          color: #666;
          transition: color 0.3s ease;
        }
      
        .action-btn:hover {
          color: #ffcc00;
        }
      
        .action-btn.delete:hover {
          color: #d9534f;
        }
      
        .action-btn.cancel:hover {
          color: #777;
        }
      
        .modal-backdrop {
          position: fixed;
          inset: 0;
          background: rgba(0,0,0,0.3);
          display: flex;
          justify-content: center;
          align-items: center;
          z-index: 9999;
        }
      
        .modal {
          background: white;
          border-radius: 12px;
          padding: 1.8rem 2rem;
          max-width: 380px;
          width: 100%;
          box-shadow: 0 12px 24px rgba(0,0,0,0.2);
          position: relative;
        }
      
        .modal h3 {
          margin-bottom: 1rem;
          font-weight: 700;
          font-size: 1.3rem;
          text-align: center;
          color: #333;
        }
      
        .modal input[type="text"] {
          width: 100%;
          padding: 10px 14px;
          font-size: 1rem;
          border: 1.5px solid #ccc;
          border-radius: 8px;
          transition: border-color 0.3s ease;
        }
      
        .modal input[type="text"]:focus {
          outline: none;
          border-color: #ffcc00;
          box-shadow: 0 0 10px rgba(74,144,226,0.4);
        }
      
        .modal .buttons {
          margin-top: 1.5rem;
          display: flex;
          justify-content: flex-end;
          gap: 1rem;
        }
      
        .btn-secondary {
          background: #ddd;
          color: #444;
          padding: 0.5rem 1.2rem;
          border-radius: 8px;
          border: none;
          cursor: pointer;
          font-weight: 600;
          transition: background-color 0.3s ease;
        }
      
        .btn-secondary:hover {
          background: #c0c0c0;
        }
      
        .toast {
          position: fixed;
          bottom: 2rem;
          left: 50%;
          transform: translateX(-50%) translateY(100%);
          background: #ffcc00;
          color: white;
          padding: 0.75rem 1.5rem;
          border-radius: 30px;
          font-weight: 600;
          box-shadow: 0 6px 12px rgba(74,144,226,0.6);
          opacity: 0;
          transition: all 0.4s ease;
          pointer-events: none;
          z-index: 10000;
        }
      
        .toast.show {
          opacity: 1;
          transform: translateX(-50%) translateY(0);
          pointer-events: auto;
        }
      
        .permissions-container {
          display: flex;
          gap: 1rem;
          min-height: 360px;
        }
      
        .permission-categories {
          flex: 0 0 220px;
          background: #f9faff;
          border-radius: 10px;
          box-shadow: 0 6px 15px rgba(74,144,226,0.1);
          display: flex;
          flex-direction: column;
          padding: 0.5rem;
        }
      
        .permission-categories button {
          background: none;
          border: none;
          padding: 0.9rem 1.2rem;
          font-weight: 600;
          font-size: 1rem;
          color: #555;
          text-align: start;
          cursor: pointer;
          border-radius: 8px;
          margin-block: 0.25rem;
          transition: background-color 0.3s ease, color 0.3s ease;
        }
      
        .permission-categories button:hover,
        .permission-categories button.active {
          background-color: #ffcc00;
          color: white;
          box-shadow: 0 3px 10px rgba(74,144,226,0.3);
        }
      
        .permissions-table {
          flex: 1;
          overflow-x: auto;
          background: white;
          border-radius: 10px;
          box-shadow: 0 6px 15px rgba(0,0,0,0.05);
        }
      
        .permissions-table table {
          width: 100%;
          border-collapse: collapse;
          min-width: 600px;
        }
      
        .permissions-table thead th {
          background: #f1f7ff;
          font-weight: 700;
          color: #bb9600;
          padding: 12px 14px;
          border-bottom: 2px solid #dbe9ff;
          white-space: nowrap;
          text-align: center;
        }
      
        .permissions-table tbody td {
          padding: 12px 14px;
          text-align: center;
          vertical-align: middle;
          border-bottom: 1px solid #eee;
        }
      
        .permissions-table tbody td:first-child {
          text-align: start;
          font-weight: 600;
          color: #444;
          white-space: nowrap;
        }
      
        .permissions-table tbody tr:hover {
          background-color: #f9faff;
        }
      
        .toggle-switch {
          position: relative;
          display: inline-block;
          width: 38px;
          height: 22px;
        }
      
        .toggle-switch input {
          opacity: 0;
          width: 0;
          height: 0;
        }
      
        .slider {
          position: absolute;
          cursor: pointer;
          top: 0; left: 0; right: 0; bottom: 0;
          background-color: #ccc;
          border-radius: 34px;
          transition: 0.3s;
        }
      
        .slider:before {
          position: absolute;
          content: "";
          height: 16px;
          width: 16px;
          left: 3px;
          bottom: 3px;
          background-color: white;
          border-radius: 50%;
          transition: 0.3s;
        }
      
        input:checked + .slider {
          background-color: #ffcc00;
        }
      
        input:checked + .slider:before {
          transform: translateX(16px);
        }
      
        @media (max-width: 800px) {
          .permissions-container {
            flex-direction: column;
          }
          .permission-categories {
            flex: unset;
            width: 100%;
            display: flex;
            overflow-x: auto;
            padding: 0.5rem 0;
          }
          .permission-categories button {
            flex: none;
            margin-inline: 0.3rem;
          }
          .permissions-table {
            overflow-x: auto;
            max-width: 100%;
          }
        }




      </style>
      



      <div class="container" id="app">

        <!-- Tabs -->
        <div class="tabs" role="tablist" >
          <button id="tabRolesBtn" class="active" role="tab" aria-selected="true" aria-controls="rolesTab">الرولات</button>
          <button id="tabPermissionsBtn" role="tab" aria-selected="false" aria-controls="permissionsTab">الصلاحيات</button>
        </div>
      
        <!-- Roles Tab -->
        <section id="rolesTab" role="tabpanel" tabindex="0">
          <div class="top-bar">
            <input
              type="text"
              placeholder="ابحث عن رول..."
              class="search-input"
              id="searchRoleInput"
              aria-label="بحث عن رول"
            />
      
            <button class="btn-primary" id="openAddRoleBtn" aria-haspopup="dialog" aria-controls="addRoleModal">
              <i class="fas fa-plus"></i> إضافة رول جديد
            </button>
          </div>
      
          <table aria-label="قائمة الرولات" id="rolesTable">
            <thead>
              <tr>
                <th>#</th>
                <th>اسم الرول</th>
                <th style="width: 140px;">الإجراءات</th>
              </tr>
            </thead>
            <tbody id="rolesTableBody">
              <!-- Roles rows injected by JS -->
            </tbody>
          </table>
      
          <!-- Add Role Modal -->
          <div
            id="addRoleModal"
            class="modal-backdrop"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modalTitle"
            style="display: none;"
          >
            <div class="modal" tabindex="0">
              <h3 id="modalTitle">إضافة رول جديد</h3>
              <input
                type="text"
                placeholder="اسم الرول"
                id="newRoleNameInput"
                aria-label="اسم الرول الجديد"
                autofocus
              />
              <div class="buttons">
                <button class="btn-secondary" id="cancelAddRoleBtn">إلغاء</button>
                <button class="btn-primary" id="saveAddRoleBtn" disabled>حفظ</button>
              </div>
            </div>
          </div>
      
        </section>
      
        <!-- Permissions Tab -->
        <section id="permissionsTab" style="display:none;" role="tabpanel" tabindex="0">
          <div class="permissions-container" style="display:flex; gap: 1rem;">
      
            <!-- Categories Sidebar -->
            <nav class="permission-categories" role="navigation" aria-label="تصنيفات الصلاحيات" id="categoriesNav" style="min-width: 180px; border: 1px solid #ddd; padding: 0.5rem; border-radius: 5px;">
              <!-- categories buttons injected here -->
            </nav>
      
            <!-- Permissions Table -->
            <div class="permissions-table" style="flex-grow: 1; overflow-x:auto;">
              <table aria-label="جدول الصلاحيات">
                <thead>
                  <tr>
                    <th>الصلاحية</th>
                    <!-- Role columns injected dynamically -->
                  </tr>
                </thead>
                <tbody id="permissionsTableBody">
                  <!-- Permissions rows injected here -->
                </tbody>
              </table>
            </div>
      
          </div>
        </section>
      
        <!-- Toast Notification -->
        <div
          class="toast"
          id="toast"
          role="alert"
          aria-live="assertive"
          aria-atomic="true"
          style="display:none; position: fixed; bottom: 1rem; right: 1rem; background:#333; color:#fff; padding: 0.5rem 1rem; border-radius: 4px;"
        ></div>
      
      </div>
      
      <script>
        const apiBase = '/api';
      
        // State
        let roles = [];
        let permissions = [];
        let categories = [];
        let selectedCategoryId = null;
      
        // Cached elements
        const tabRolesBtn = document.getElementById('tabRolesBtn');
        const tabPermissionsBtn = document.getElementById('tabPermissionsBtn');
        const rolesTab = document.getElementById('rolesTab');
        const permissionsTab = document.getElementById('permissionsTab');
        const categoriesNav = document.getElementById('categoriesNav');
        const permissionsTableBody = document.getElementById('permissionsTableBody');
        const rolesTableBody = document.getElementById('rolesTableBody');
      
        // Toast helper
        function showToast(message) {
          const toast = document.getElementById('toast');
          toast.textContent = message;
          toast.style.display = 'block';
          setTimeout(() => {
            toast.style.display = 'none';
          }, 3000);
        }
      
        // Switch tabs
        tabRolesBtn.addEventListener('click', () => {
          tabRolesBtn.classList.add('active');
          tabRolesBtn.setAttribute('aria-selected', 'true');
          tabPermissionsBtn.classList.remove('active');
          tabPermissionsBtn.setAttribute('aria-selected', 'false');
          rolesTab.style.display = 'block';
          permissionsTab.style.display = 'none';
        });
      
        tabPermissionsBtn.addEventListener('click', () => {
          tabPermissionsBtn.classList.add('active');
          tabPermissionsBtn.setAttribute('aria-selected', 'true');
          tabRolesBtn.classList.remove('active');
          tabRolesBtn.setAttribute('aria-selected', 'false');
          rolesTab.style.display = 'none';
          permissionsTab.style.display = 'flex';
        });
      
        // Fetch roles
        async function fetchRoles() {
          try {
            const res = await fetch(apiBase + '/roles');
            if (!res.ok) throw new Error('خطأ في جلب الرولات');
            roles = await res.json();
            renderRoles();
          } catch (error) {
            showToast(error.message);
          }
        }
      
        // Render roles table
        function renderRoles() {
          rolesTableBody.innerHTML = '';
          roles.forEach((role, i) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td>${i+1}</td>
              <td>${role.name}</td>
              <td>
                <!-- أزرار يمكن تضيفها لاحقاً -->
                <button data-id="${role.id}" class="btn-edit" disabled>تعديل</button>
                <button data-id="${role.id}" class="btn-delete" disabled>حذف</button>
              </td>
            `;
            rolesTableBody.appendChild(tr);
          });
        }
      
        // Fetch categories (parent permissions)
        async function fetchCategories() {
          try {
            // نفترض عندك API يرجع parent_permissions مثلاً /api/parent-permissions
            const res = await fetch(apiBase + '/permissions'); 
            if (!res.ok) throw new Error('خطأ في جلب تصنيفات الصلاحيات');
            const perms = await res.json();
      
            // تجميع parent permissions
            // لأن الباك يرجع الصلاحيات مع parent مرفقة
            const parentsMap = {};
            perms.forEach(p => {
              if(p.parent) parentsMap[p.parent.id] = p.parent;
            });
            categories = Object.values(parentsMap);
      
            // إذا مافي تصنيفات (ممكن يكون الكل بنفس الفئة)
            if(categories.length === 0 && perms.length > 0){
              // ممكن نعتمد فئة واحدة عامة
              categories = [{id: null, name: 'عام'}];
            }
      
            renderCategories();
          } catch (error) {
            showToast(error.message);
          }
        }
      
        // Render categories sidebar
        function renderCategories() {
          categoriesNav.innerHTML = '';
          categories.forEach((cat, i) => {
            const btn = document.createElement('button');
            btn.textContent = cat.name || 'بدون تصنيف';
            btn.classList.add('category-btn');
            btn.style.display = 'block';
            btn.style.width = '100%';
            btn.style.marginBottom = '0.3rem';
            btn.style.padding = '0.3rem';
            btn.style.border = 'none';
            btn.style.background = i === 0 ? '#007BFF' : '#eee';
            btn.style.color = i === 0 ? '#fff' : '#000';
            btn.style.borderRadius = '4px';
            btn.style.cursor = 'pointer';
            btn.dataset.id = cat.id;
      
            if(i === 0) selectedCategoryId = cat.id;
      
            btn.addEventListener('click', () => {
              selectedCategoryId = cat.id;
              document.querySelectorAll('.category-btn').forEach(b => {
                b.style.background = '#eee';
                b.style.color = '#000';
              });
              btn.style.background = '#007BFF';
              btn.style.color = '#fff';
      
              renderPermissions();
            });
      
            categoriesNav.appendChild(btn);
          });
        }
      
        // Fetch permissions with parent data
        async function fetchPermissions() {
          try {
            const res = await fetch(apiBase + '/permissions');
            if (!res.ok) throw new Error('خطأ في جلب الصلاحيات');
            permissions = await res.json();
            renderPermissions();
          } catch (error) {
            showToast(error.message);
          }
        }
      
        // Render permissions table filtered by selected category
        function renderPermissions() {
          permissionsTableBody.innerHTML = '';
          if (!selectedCategoryId && categories.length > 0 && categories[0].id !== null) {
            // No selected category but we have categories? pick first by default
            selectedCategoryId = categories[0].id;
          }
          // Filter permissions by parent id
          const filtered = permissions.filter(p => {
            // If category id null means general
            if(selectedCategoryId === null) return !p.parent;
            return p.parent && p.parent.id == selectedCategoryId;
          });
      
          // Table header - dynamic roles columns
          const thead = document.querySelector('#permissionsTab table thead tr');
          // Reset except first th (permission name)
          thead.innerHTML = `<th>الصلاحية</th>`;
          roles.forEach(role => {
            const th = document.createElement('th');
            th.textContent = role.name;
            thead.appendChild(th);
          });
      
          filtered.forEach(perm => {
            const tr = document.createElement('tr');
            tr.dataset.permissionId = perm.id;
      
            // Permission name
            const tdName = document.createElement('td');
            tdName.textContent = perm.name;
            tr.appendChild(tdName);
      
            // For each role, add a checkbox if that role has the permission
            roles.forEach(role => {
              const td = document.createElement('td');
              const checkbox = document.createElement('input');
              checkbox.type = 'checkbox';
      
              // To check if role has this permission:
              // We call /api/roles/{id} to get role permissions or cache them
              // To optimize: let's cache role permissions in memory first
      
              td.appendChild(checkbox);
              tr.appendChild(td);
      
              // Load role permissions (once) and set checkbox
              loadRolePermissions(role.id).then(rolePerms => {
                checkbox.checked = rolePerms.includes(perm.name);
              });
      
              // On change, assign/remove permission via API
              checkbox.addEventListener('change', async () => {
                try {
                  if(checkbox.checked){
                    // assign permission to role
                    const res = await fetch(`${apiBase}/roles/${role.id}/permissions/assign`, {
                      method: 'POST',
                      headers: {'Content-Type': 'application/json'},
                      body: JSON.stringify({ permission_name: perm.name })
                    });
                    if(!res.ok) throw new Error('خطأ في تعيين الصلاحية');
                    showToast(`تم تعيين صلاحية "${perm.name}" للرول "${role.name}"`);
                    // update cache
                    rolePermissionsCache[role.id].push(perm.name);
                  } else {
                    // remove permission
                    const res = await fetch(`${apiBase}/roles/${role.id}/permissions/remove`, {
                      method: 'POST',
                      headers: {'Content-Type': 'application/json'},
                      body: JSON.stringify({ permission_name: perm.name })
                    });
                    if(!res.ok) throw new Error('خطأ في إزالة الصلاحية');
                    showToast(`تم إزالة صلاحية "${perm.name}" من الرول "${role.name}"`);
                    // update cache
                    rolePermissionsCache[role.id] = rolePermissionsCache[role.id].filter(p => p !== perm.name);
                  }
                } catch (e) {
                  showToast(e.message);
                  checkbox.checked = !checkbox.checked; // rollback
                }
              });
            });
      
            permissionsTableBody.appendChild(tr);
          });
        }
      
        // Cache for role permissions to avoid multiple requests
        const rolePermissionsCache = {};
      
        // Load permissions for a role once and cache
        async function loadRolePermissions(roleId) {
          if(rolePermissionsCache[roleId]){
            return rolePermissionsCache[roleId];
          }
          try {
            const res = await fetch(`${apiBase}/roles/${roleId}/permissions`);
            if(!res.ok) throw new Error('خطأ في جلب صلاحيات الرول');
            const data = await res.json();
            // data format assumed: array of permissions with "name" prop
            const permsNames = data.map(p => p.name);
            rolePermissionsCache[roleId] = permsNames;
            return permsNames;
          } catch (e) {
            showToast(e.message);
            return [];
          }
        }
      
        // Initial load
        async function init(){
          await fetchRoles();
          await fetchCategories();
          await fetchPermissions();
        }
    
        init();
      </script>

      
  
    
    <style>
      /* Reset and base styles */
      * {
        box-sizing: border-box;
      }
      body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f9f9f9;
        margin: 0; padding: 0;
        color: #333;
      }
      .app-container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 15px 20px;
        background: #fff;
        box-shadow: 0 3px 15px rgba(0,0,0,0.05);
        border-radius: 8px;
      }
    
      /* Tabs */
      .tabs {
        display: flex;
        border-bottom: 2px solid #ddd;
      }
      .tabs button {
        flex: 1;
        padding: 12px 18px;
        background: #eee;
        border: none;
        cursor: pointer;
        font-size: 18px;
        color: #555;
        transition: background 0.3s ease, color 0.3s ease;
      }
      .tabs button[aria-selected="true"] {
        background: #bd9805;
        color: white;
        font-weight: 600;
        border-bottom: 3px solid #ffcc00;
      }
      .tabs button:hover:not([aria-selected="true"]) {
        background: #ddd;
      }
    
      /* Roles Tab */
      .top-bar {
        display: flex;
        justify-content: space-between;
        margin: 20px 0;
        gap: 10px;
      }
      .search-input {
        flex: 1;
        padding: 10px 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 16px;
      }
      .btn-primary {
        background-color: #b89404;
        color: white;
        border: none;
        padding: 10px 18px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        transition: background-color 0.3s ease;
      }
      .btn-primary:hover:not(:disabled) {
        background-color: #ffcc00;
      }
      .btn-primary:disabled {
        background-color: #80aaff;
        cursor: not-allowed;
      }
      .action-btn {
        background: none;
        border: none;
        cursor: pointer;
        color: #967802;
        font-size: 18px;
        margin-right: 8px;
        transition: color 0.3s ease;
      }
      .action-btn.delete {
        color: #dc3545;
      }
      .action-btn:hover {
        opacity: 0.8;
      }
      .edit-input {
        width: 100%;
        padding: 6px 8px;
        font-size: 16px;
        border: 1px solid #ffcc00;
        border-radius: 5px;
      }
    
      /* Modal */
      .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.4);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
      }
      .modal {
        background: #fff;
        padding: 25px 30px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        width: 320px;
        max-width: 90vw;
      }
      .modal h3 {
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 22px;
        color: #ffcc00;
      }
      .modal input[type="text"] {
        width: 100%;
        padding: 10px 12px;
        font-size: 16px;
        border-radius: 6px;
        border: 1px solid #ccc;
        margin-bottom: 18px;
      }
      .buttons {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
      }
      .btn-secondary {
        background: #6c757d;
        color: white;
        border: none;
        padding: 8px 18px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 15px;
        transition: background-color 0.3s ease;
      }
      .btn-secondary:hover {
        background: #565e64;
      }
    
      /* Permissions Tab */
      .permissions-container {
        display: flex;
        gap: 15px;
        margin-top: 20px;
      }
      .permission-categories {
        width: 220px;
        background: #f1f5f9;
        border-radius: 8px;
        padding: 12px 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
      }
      .permission-categories button {
        background: transparent;
        border: none;
        padding: 10px 15px;
        text-align: right;
        cursor: pointer;
        font-size: 17px;
        color: #555;
        border-radius: 6px;
        margin-bottom: 8px;
        transition: background-color 0.25s ease, color 0.25s ease;
      }
      .permission-categories button.active,
      .permission-categories button:hover {
        background-color: #ffcc00;
        color: white;
        font-weight: 600;
      }
      .permissions-table {
        flex: 1;
        overflow-x: auto;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        padding: 12px 15px;
      }
      table {
        width: 100%;
        border-collapse: collapse;
      }
      th, td {
        text-align: center;
        padding: 10px 8px;
        border-bottom: 1px solid #eee;
        font-size: 16px;
      }
      thead th {
        background-color: #ffcc00;
        color: white;
        font-weight: 600;
        position: sticky;
        top: 0;
        z-index: 2;
      }
    
      /* Toggle Switch */
      .toggle-switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
      }
      .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
      }
      .slider {
        position: absolute;
        cursor: pointer;
        background-color: #ccc;
        border-radius: 24px;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        transition: 0.4s;
      }
      .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        border-radius: 50%;
        transition: 0.4s;
      }
      input:checked + .slider {
        background-color: #ffcc00;
      }
      input:checked + .slider:before {
        transform: translateX(22px);
      }
    
      /* Toast Notification */
      .toast {
        position: fixed;
        bottom: 15px;
        right: 15px;
        background-color: #333;
        color: white;
        padding: 12px 22px;
        border-radius: 8px;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
        font-size: 15px;
        box-shadow: 0 3px 8px rgba(0,0,0,0.25);
        z-index: 1100;
      }
      .toast.show {
        opacity: 1;
        pointer-events: auto;
      }
    </style>
</x-master-layout>
