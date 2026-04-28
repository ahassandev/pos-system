<template>
  <div class="dashboard">
    <h1>Dashboard</h1>
    
    <div class="stats-grid">
      <div class="stat-card">
        <h3>Today's Sales</h3>
        <p class="value">${{ stats.total_sales_today?.toFixed(2) }}</p>
      </div>
      <div class="stat-card">
        <h3>Total Products</h3>
        <p class="value">{{ stats.total_products }}</p>
      </div>
      <div class="stat-card">
        <h3>Total Customers</h3>
        <p class="value">{{ stats.total_customers }}</p>
      </div>
    </div>

    <div class="chart-section">
      <h3>Revenue (Last 7 Days)</h3>
      <div class="chart-wrapper">
        <Bar v-if="chartData.labels.length > 0" :data="chartData" :options="chartOptions" />
        <div v-else class="chart-loader">Loading chart...</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const stats = ref({
  total_sales_today: 0,
  total_products: 0,
  total_customers: 0,
  revenue_chart: []
});

const chartData = computed(() => {
  const data = stats.value.revenue_chart || [];
  
  // Fallback to dummy data if empty
  const labels = data.length > 0 ? data.map(d => d.day_name) : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
  const values = data.length > 0 ? data.map(d => d.total) : [0, 0, 0, 0, 0, 0, 0];

  return {
    labels: labels,
    datasets: [
      {
        label: 'Revenue',
        backgroundColor: '#6366f1',
        borderRadius: 8,
        data: values,
        barThickness: 30,
      }
    ]
  };
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#1e293b',
      padding: 12,
      bodyFont: { size: 14 },
      callbacks: {
        label: (context) => `$${context.raw.toFixed(2)}`
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: { display: false },
      ticks: {
        callback: (value) => `$${value}`
      }
    },
    x: {
      grid: { display: false }
    }
  }
};

onMounted(async () => {
  try {
    const response = await axios.get('/api/dashboard/stats');
    stats.value = response.data;
  } catch (err) {
    console.error('Failed to fetch stats', err);
  }
});
</script>

<style scoped>
.dashboard {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
}

.stat-card {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04);
  border: 1px solid #f1f5f9;
}

.stat-card h3 {
  margin: 0;
  color: #64748b;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.stat-card .value {
  margin: 0.75rem 0 0;
  font-size: 2.25rem;
  font-weight: 800;
  color: #1e293b;
}

.chart-section {
  background: white;
  padding: 2rem;
  border-radius: 20px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04);
  border: 1px solid #f1f5f9;
}

.chart-section h3 {
  margin: 0 0 2rem;
  color: #1e293b;
  font-size: 1.25rem;
}

.chart-wrapper {
  height: 300px; /* Fixed height as requested */
  width: 100%;
}

.chart-loader {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
}
</style>
