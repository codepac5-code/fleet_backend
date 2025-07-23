<template>
  <div class="issues-index">
    <h2>قائمة التذاكر</h2>

    <!-- الفلاتر -->
    <div class="filters">
      <input v-model="filters.search" placeholder="بحث عن موضوع أو وصف" @input="fetchIssues" />

      <select v-model="filters.status" @change="fetchIssues">
        <option value="">كل الحالات</option>
        <option value="open">مفتوحة</option>
        <option value="processing">جاري المعالجة</option>
        <option value="closed">مغلقة</option>
      </select>

      <select v-model="filters.department" @change="fetchIssues">
        <option value="">كل الأقسام</option>
        <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name_ar }}</option>
      </select>

      <select v-model="filters.agent" @change="fetchIssues">
        <option value="">كل الموظفين</option>
        <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.firstName }} {{ emp.lastName }}</option>
      </select>

      <select v-model="filters.priority" @change="fetchIssues">
        <option value="">كل الأولويات</option>
        <option value="low">منخفضة</option>
        <option value="medium">متوسطة</option>
        <option value="high">عالية</option>
      </select>

      <select v-model="perPage" @change="fetchIssues">
        <option :value="5">5</option>
        <option :value="10">10</option>
        <option :value="25">25</option>
      </select>
    </div>

    <!-- جدول عرض التذاكر -->
    <table class="table">
      <thead>
        <tr>
          <th>الموضوع</th>
          <th>الحالة</th>
          <th>الأولوية</th>
          <th>القسم</th>
          <th>الموظف المكلف</th>
          <th>آخر تحديث</th>
          <th>إجراءات</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="ticket in tickets" :key="ticket.id">
          <td>{{ ticket.title }}</td>
          <td>{{ ticket.status }}</td>
          <td>{{ ticket.priority }}</td>
          <td>{{ ticket.department }}</td>
          <td>
            <img v-if="ticket.agentImageUrl" :src="ticket.agentImageUrl" alt="Agent" class="agent-photo" />
            {{ ticket.agentName }}
          </td>
          <td>{{ new Date(ticket.lastUpdated).toLocaleString() }}</td>
          <td>
            <button @click="deleteIssue(ticket.id)">حذف</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- الترقيم -->
    <div class="pagination">
      <button @click="prevPage" :disabled="page === 1">السابق</button>
      <span>صفحة {{ page }}</span>
      <button @click="nextPage" :disabled="!hasMore">التالي</button>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      tickets: [],
      departments: [],
      employees: [],
      filters: {
        search: '',
        status: '',
        department: '',
        agent: '',
        priority: '',
      },
      page: 1,
      perPage: 10,
      totalItems: 0,
    };
  },
  computed: {
    hasMore() {
      return this.page * this.perPage < this.totalItems;
    },
  },
  methods: {
    fetchIssues() {
      const params = {
        ...this.filters,
        per_page: this.perPage,
        page: this.page,
      };
      axios
        .get('/api/tickets', { params })
        .then((res) => {
          this.tickets = res.data.tickets;
          this.totalItems = res.data.totalItems;
        });
    },
    fetchDepartments() {
      axios.get('/api/departments').then(res => {
        this.departments = res.data;
      });
    },
    fetchEmployees() {
      axios.get('/api/employees').then(res => {
        this.employees = res.data;
      });
    },
    deleteIssue(id) {
      if(confirm('هل أنت متأكد من حذف التذكرة؟')) {
        axios.delete(`/api/tickets/${id}`).then(() => {
          alert('تم الحذف بنجاح');
          this.fetchIssues();
        });
      }
    },
    prevPage() {
      if (this.page > 1) {
        this.page--;
        this.fetchIssues();
      }
    },
    nextPage() {
      if (this.hasMore) {
        this.page++;
        this.fetchIssues();
      }
    }
  },
  mounted() {
    this.fetchDepartments();
    this.fetchEmployees();
    this.fetchIssues();
  },
};
</script>

<style scoped>
.agent-photo {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  margin-right: 5px;
}
.filters {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
}
</style>
