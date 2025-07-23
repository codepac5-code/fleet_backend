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
        <div class="tabs" role="tablist" aria-label="التنقل بين التابات">
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
          <div class="permissions-container">
      
            <!-- Categories Sidebar -->
            <nav class="permission-categories" role="navigation" aria-label="تصنيفات الصلاحيات" id="categoriesNav">
              <!-- categories buttons injected here -->
            </nav>
      
            <!-- Permissions Table -->
            <div class="permissions-table" role="region" aria-live="polite" aria-label="جدول الصلاحيات">
              <table>
                <thead>
                  <tr>
                    <th>الصلاحية</th>
                    <th id="permissionsRolesHeaders"><!-- roles headers injected here --></th>
                  </tr>
                </thead>
                <tbody id="permissionsTableBody">
                  <!-- permissions rows injected here -->
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
          style="display:none;"
        ></div>
      
      </div>
      
      <script>
        (function () {
          // عناصر الـ DOM
          const app = document.getElementById('app');
          const tabRolesBtn = document.getElementById('tabRolesBtn');
          const tabPermissionsBtn = document.getElementById('tabPermissionsBtn');
          const rolesTab = document.getElementById('rolesTab');
          const permissionsTab = document.getElementById('permissionsTab');
          const rolesTableBody = document.getElementById('rolesTableBody');
          const searchRoleInput = document.getElementById('searchRoleInput');
          const openAddRoleBtn = document.getElementById('openAddRoleBtn');
          const addRoleModal = document.getElementById('addRoleModal');
          const newRoleNameInput = document.getElementById('newRoleNameInput');
          const cancelAddRoleBtn = document.getElementById('cancelAddRoleBtn');
          const saveAddRoleBtn = document.getElementById('saveAddRoleBtn');
      
          const categoriesNav = document.getElementById('categoriesNav');
          const permissionsTableBody = document.getElementById('permissionsTableBody');
          const permissionsRolesHeaders = document.getElementById('permissionsRolesHeaders');
      
          const toast = document.getElementById('toast');
      
          // بيانات التطبيق
          let roles = [];
          let filteredRoles = [];
          let permissions = [];
          let categories = [];
          let rolePermissions = {}; // { roleId: { permissionId: true/false } }
      
          let editingRoleId = null;
          let editRoleName = '';
      
          let activeCategoryId = null;
      
          // --- Utils ---
          function showToast(message) {
            toast.textContent = message;
            toast.style.display = 'block';
            setTimeout(() => {
              toast.style.display = 'none';
            }, 8000);
          }
      
          // --- API Calls ---
          async function fetchRoles() {
            try {
              const res = await fetch('/api/roles');
              if (!res.ok) throw new Error('فشل جلب الرولات');
              roles = await res.json();
              filteredRoles = [...roles];
            } catch (e) {
              showToast(e.message);
            }
          }
      
          async function fetchPermissions() {
            try {
              const res = await fetch('/api/permissions');
              if (!res.ok) throw new Error('فشل جلب الصلاحيات');
      
              permissions = await res.json();
      
              // استخراج التصنيفات من الـ parent مباشرة
              const uniqueCategoriesMap = {};
              permissions.forEach(p => {
                const parent = p.parent;
                const catId = parent?.id ?? 0;
                const catName = parent?.name ?? 'غير مصنف';
                uniqueCategoriesMap[catId] = catName;
              });
      
              // تحويل الكائن إلى مصفوفة التصنيفات
              categories = Object.entries(uniqueCategoriesMap).map(([id, name]) => ({
                id: parseInt(id),
                name
              }));
      
              activeCategoryId = categories.length > 0 ? categories[0].id : null;
      
            } catch (e) {
              showToast(e.message);
            }
          }
      
          async function fetchRolePermissions() {
            try {
              // بما أن صلاحيات الرولات تأتي مع الرولات (role.perms)، سنملأ rolePermissions بناء على ذلك
              rolePermissions = {};
              roles.forEach(role => {
                rolePermissions[role.id] = {};
                (role.perms || []).forEach(p => {
                  rolePermissions[role.id][p.id] = true;
                });
              });
            } catch (e) {
              showToast(e.message);
            }
          }
      
          async function createRoleAPI(name) {
            try {
              const res = await fetch('/api/roles', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name }),
              });
              if (!res.ok) throw new Error('فشل إنشاء الرول');
              return await res.json();
            } catch (e) {
              showToast(e.message);
              throw e;
            }
          }
      
          async function updateRoleAPI(id, name) {
            try {
              const res = await fetch(`/api/roles/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name }),
              });
              if (!res.ok) throw new Error('فشل تحديث اسم الرول');
              return await res.json();
            } catch (e) {
              showToast(e.message);
              throw e;
            }
          }
      
          async function deleteRoleAPI(id) {
            try {
              const res = await fetch(`/api/roles/${id}`, { method: 'DELETE' });
              if (!res.ok) throw new Error('فشل حذف الرول');
            } catch (e) {
              showToast(e.message);
              throw e;
            }
          }
      
          // دالة إسناد الصلاحية إلى الرول باسم الصلاحية
          async function assignPermissionToRole(roleId, permissionName) {
            const res = await fetch(`/api/roles/${roleId}/permissions/assign`, {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ permission: permissionName }),
            });
            if (!res.ok) throw new Error('فشل إسناد الصلاحية');
          }
      
          // دالة إزالة الصلاحية من الرول باسم الصلاحية
          async function removePermissionFromRole(roleId, permissionName) {
            const res = await fetch(`/api/roles/${roleId}/permissions/remove`, {
              method: 'POST', // أو DELETE حسب تصميم API
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ permission: permissionName }),
            });
            if (!res.ok) throw new Error('فشل إزالة الصلاحية');
          }
      
          // --- Render functions ---
          function renderRolesTable() {
            rolesTableBody.innerHTML = '';
      
            if (filteredRoles.length === 0) {
              const tr = document.createElement('tr');
              tr.innerHTML = `<td colspan="3" style="text-align: center; color: #999;">لا توجد رولات لعرضها</td>`;
              rolesTableBody.appendChild(tr);
              return;
            }
      
            filteredRoles.forEach((role, index) => {
              const tr = document.createElement('tr');
              tr.dataset.roleId = role.id;
      
              const tdIndex = document.createElement('td');
              tdIndex.textContent = index + 1;
      
              const tdName = document.createElement('td');
      
              if (editingRoleId === role.id) {
                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'edit-input';
                input.value = editRoleName;
                input.setAttribute('aria-label', 'تعديل اسم الرول');
      
                input.addEventListener('input', e => {
                  editRoleName = e.target.value;
                });
      
                input.addEventListener('keydown', e => {
                  if (e.key === 'Enter') {
                    e.preventDefault();
                    saveRoleEdit(role.id);
                  } else if (e.key === 'Escape') {
                    e.preventDefault();
                    cancelRoleEdit();
                  }
                });
      
                tdName.appendChild(input);
                setTimeout(() => input.focus(), 10);
              } else {
                tdName.textContent = role.name;
              }
      
              const tdActions = document.createElement('td');
      
              if (editingRoleId === role.id) {
                const saveBtn = document.createElement('button');
                saveBtn.className = 'action-btn';
                saveBtn.title = 'حفظ';
                saveBtn.innerHTML = '<i class="fas fa-check"></i>';
                saveBtn.addEventListener('click', () => saveRoleEdit(role.id));
      
                const cancelBtn = document.createElement('button');
                cancelBtn.className = 'action-btn cancel';
                cancelBtn.title = 'إلغاء';
                cancelBtn.innerHTML = '<i class="fas fa-times"></i>';
                cancelBtn.addEventListener('click', cancelRoleEdit);
      
                tdActions.appendChild(saveBtn);
                tdActions.appendChild(cancelBtn);
              } else {
                const editBtn = document.createElement('button');
                editBtn.className = 'action-btn';
                editBtn.title = 'تعديل';
                editBtn.innerHTML = '<i class="fas fa-edit"></i>';
                editBtn.addEventListener('click', () => startEditRole(role.id));
      
                const deleteBtn = document.createElement('button');
                deleteBtn.className = 'action-btn delete';
                deleteBtn.title = 'حذف';
                deleteBtn.innerHTML = '<i class="fas fa-trash-alt"></i>';
                deleteBtn.addEventListener('click', () => confirmDeleteRole(role.id, role.name));
      
                tdActions.appendChild(editBtn);
                tdActions.appendChild(deleteBtn);
              }
      
              tr.appendChild(tdIndex);
              tr.appendChild(tdName);
              tr.appendChild(tdActions);
      
              rolesTableBody.appendChild(tr);
            });
          }
      
          function renderCategories() {
            categoriesNav.innerHTML = '';
            categories.forEach(cat => {
              const btn = document.createElement('button');
              btn.type = 'button';
              btn.textContent = cat.name;
              btn.className = (activeCategoryId === cat.id) ? 'active' : '';
              btn.setAttribute('aria-pressed', activeCategoryId === cat.id);
              btn.addEventListener('click', () => {
                activeCategoryId = cat.id;
                renderCategories();
                renderPermissionsTable();
              });
              categoriesNav.appendChild(btn);
            });
          }
      
          function renderPermissionsTable() {
            permissionsTableBody.innerHTML = '';
            permissionsRolesHeaders.innerHTML = '';
      
            // عرض رؤوس أعمدة الرولات
            roles.forEach(role => {
              const span = document.createElement('span');
              span.textContent = role.name;
              span.title = role.name;
              permissionsRolesHeaders.appendChild(span);
            });
      
            // صلاحيات التصنيف المختار
            const filteredPermissions = permissions.filter(p => (p.parent?.id ?? 0) === activeCategoryId);
      
            if (filteredPermissions.length === 0) {
              const tr = document.createElement('tr');
              const td = document.createElement('td');
              td.colSpan = roles.length + 1;
              td.style.textAlign = 'center';
              td.style.color = '#999';
              td.textContent = 'لا توجد صلاحيات لهذا التصنيف';
              tr.appendChild(td);
              permissionsTableBody.appendChild(tr);
              return;
            }
      
            filteredPermissions.forEach(permission => {
              const tr = document.createElement('tr');
              const tdName = document.createElement('td');
              tdName.textContent = permission.name;
              tr.appendChild(tdName);
      
              roles.forEach(role => {
                const td = document.createElement('td');
                td.style.textAlign = 'center';
      
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.setAttribute('aria-label', `صلاحية ${permission.name} للرول ${role.name}`);
                checkbox.checked = !!(rolePermissions[role.id] && rolePermissions[role.id][permission.id]);
      
                checkbox.addEventListener('change', async e => {
                  try {
                    if (e.target.checked) {
                      await assignPermissionToRole(role.id, permission.name);
                      if (!rolePermissions[role.id]) rolePermissions[role.id] = {};
                      rolePermissions[role.id][permission.id] = true;
                    } else {
                      await removePermissionFromRole(role.id, permission.name);
                      if (!rolePermissions[role.id]) rolePermissions[role.id] = {};
                      rolePermissions[role.id][permission.id] = false;
                    }
                    showToast('تم تحديث الصلاحية بنجاح');
                  } catch (error) {
                    showToast(error.message || 'حدث خطأ أثناء التحديث');
                    e.target.checked = !e.target.checked; // إعادة الحالة السابقة عند الخطأ
                  }
                });
      
                td.appendChild(checkbox);
                tr.appendChild(td);
              });
      
              permissionsTableBody.appendChild(tr);
            });
          }
      
          // --- Events for Roles Tab ---
          searchRoleInput.addEventListener('input', e => {
            const val = e.target.value.trim().toLowerCase();
            filteredRoles = roles.filter(r => r.name.toLowerCase().includes(val));
            renderRolesTable();
          });
      
          openAddRoleBtn.addEventListener('click', () => {
            newRoleNameInput.value = '';
            addRoleModal.style.display = 'block';
            newRoleNameInput.focus();
          });
      
          cancelAddRoleBtn.addEventListener('click', () => {
            addRoleModal.style.display = 'none';
          });
      
          saveAddRoleBtn.addEventListener('click', async () => {
            const name = newRoleNameInput.value.trim();
            if (!name) {
              alert('يرجى إدخال اسم الرول');
              return;
            }
            try {
              const newRole = await createRoleAPI(name);
              roles.push(newRole);
              filteredRoles = [...roles];
              renderRolesTable();
              renderPermissionsTable();
              addRoleModal.style.display = 'none';
              showToast('تم إنشاء الرول بنجاح');
            } catch {
              // خطأ يتم التعامل معه في createRoleAPI
            }
          });
      
          // --- Roles Editing ---
          function startEditRole(id) {
            editingRoleId = id;
            const role = roles.find(r => r.id === id);
            editRoleName = role ? role.name : '';
            renderRolesTable();
          }
      
          function cancelRoleEdit() {
            editingRoleId = null;
            editRoleName = '';
            renderRolesTable();
          }
      
          async function saveRoleEdit(id) {
            const name = editRoleName.trim();
            if (!name) {
              alert('يرجى إدخال اسم الرول');
              return;
            }
            try {
              await updateRoleAPI(id, name);
              const role = roles.find(r => r.id === id);
              if (role) role.name = name;
              editingRoleId = null;
              editRoleName = '';
              filteredRoles = [...roles];
              renderRolesTable();
              renderPermissionsTable();
              showToast('تم تحديث اسم الرول بنجاح');
            } catch {
              // التعامل مع الخطأ ضمن updateRoleAPI
            }
          }
      
          function confirmDeleteRole(id, name) {
            if (confirm(`هل أنت متأكد من حذف الرول "${name}"؟`)) {
              deleteRole(id);
            }
          }
      
          async function deleteRole(id) {
            try {
              await deleteRoleAPI(id);
              roles = roles.filter(r => r.id !== id);
              filteredRoles = [...roles];
              renderRolesTable();
              renderPermissionsTable();
              showToast('تم حذف الرول بنجاح');
            } catch {
              // الخطأ يتم التعامل معه في deleteRoleAPI
            }
          }
      
          // --- Tabs switching ---
          tabRolesBtn.addEventListener('click', () => {
            tabRolesBtn.classList.add('active');
            tabPermissionsBtn.classList.remove('active');
            rolesTab.style.display = 'block';
            permissionsTab.style.display = 'none';
          });
      
          tabPermissionsBtn.addEventListener('click', () => {
            tabPermissionsBtn.classList.add('active');
            tabRolesBtn.classList.remove('active');
            permissionsTab.style.display = 'block';
            rolesTab.style.display = 'none';
          });
      
          // --- Init ---
          async function init() {
            await fetchRoles();
            await fetchPermissions();
            await fetchRolePermissions();
            renderRolesTable();
            renderCategories();
            renderPermissionsTable();
          }
      
          init();
      
        })();
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
