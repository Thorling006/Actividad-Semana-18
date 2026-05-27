<template>
  <div class="container">
    <!-- Header -->
    <header class="header">
      <h1 class="logo-text">GlowStock</h1>
      <p class="subtitle">Gestión de Ventas e Inventario Inteligente en Tiempo Real</p>
    </header>

    <!-- Stats Section -->
    <section class="stats-grid">
      <div class="stat-card">
        <div class="stat-info">
          <h3>Productos en Catálogo</h3>
          <div class="stat-value">{{ products.length }}</div>
        </div>
        <div class="stat-icon">📦</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-info">
          <h3>Total de Ventas</h3>
          <div class="stat-value">{{ sales.length }}</div>
        </div>
        <div class="stat-icon sales">📈</div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <h3>Ingresos Totales</h3>
          <div class="stat-value">{{ formatPrice(totalEarnings) }}</div>
        </div>
        <div class="stat-icon earnings">💰</div>
      </div>
    </section>

    <!-- Main Dashboard Grid -->
    <main class="dashboard-grid">
      <!-- Left: Catalog & Sales Log -->
      <div class="dashboard-left">
        <!-- Products List -->
        <ProductList :products="products" :loading="loadingProducts" />
        
        <!-- Sales List -->
        <SaleList :sales="sales" :loading="loadingSales" />
      </div>

      <!-- Right: Sales Form -->
      <div class="dashboard-right">
        <SaleForm 
          :products="products" 
          @sale-created="handleSaleCreated" 
          @toast="showToast"
        />
      </div>
    </main>

    <!-- Toast Notifications Manager -->
    <div class="toast-container">
      <div 
        v-for="toast in toasts" 
        :key="toast.id" 
        :class="['toast', toast.type]"
      >
        <span class="toast-icon">{{ toast.type === 'success' ? '⚡' : '⚠️' }}</span>
        <div class="toast-message">{{ toast.message }}</div>
        <button class="toast-close" @click="removeToast(toast.id)">&times;</button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import ProductList from './components/ProductList.vue';
import SaleForm from './components/SaleForm.vue';
import SaleList from './components/SaleList.vue';

export default {
  name: 'App',
  components: {
    ProductList,
    SaleForm,
    SaleList
  },
  setup() {
    const products = ref([]);
    const sales = ref([]);
    const toasts = ref([]);
    const loadingProducts = ref(true);
    const loadingSales = ref(true);
    let toastIdCounter = 0;

    // Toast Methods
    const showToast = (message, type = 'success') => {
      const id = ++toastIdCounter;
      toasts.value.push({ id, message, type });
      
      // Auto dismiss after 4 seconds
      setTimeout(() => {
        removeToast(id);
      }, 4000);
    };

    const removeToast = (id) => {
      toasts.value = toasts.value.filter(t => t.id !== id);
    };

    // Data Fetching
    const fetchProducts = async () => {
      loadingProducts.value = true;
      try {
        const response = await axios.get('/api/products');
        products.value = response.value || response.data;
      } catch (error) {
        showToast('Error al conectar con la API de productos.', 'error');
        console.error(error);
      } finally {
        loadingProducts.value = false;
      }
    };

    const fetchSales = async () => {
      loadingSales.value = true;
      try {
        const response = await axios.get('/api/sales');
        sales.value = response.value || response.data;
      } catch (error) {
        showToast('Error al conectar con la API de ventas.', 'error');
        console.error(error);
      } finally {
        loadingSales.value = false;
      }
    };

    // Event handlers
    const handleSaleCreated = (newSale) => {
      // 1. Add the new sale to the beginning of the list
      sales.value.unshift(newSale);
      
      // 2. Decrement local stock for the product so the UI updates instantly
      const productIndex = products.value.findIndex(p => p.id === newSale.product_id);
      if (productIndex !== -1) {
        products.value[productIndex].stock -= newSale.quantity;
      }
      
      showToast('¡Venta procesada y stock actualizado con éxito!', 'success');
    };

    // Computed Stats
    const totalEarnings = computed(() => {
      return sales.value.reduce((total, sale) => total + parseFloat(sale.total_price), 0);
    });

    // Formatting helper
    const formatPrice = (price) => {
      return parseFloat(price).toLocaleString('es-ES', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    };

    // Lifecycle
    onMounted(() => {
      fetchProducts();
      fetchSales();
    });

    return {
      products,
      sales,
      toasts,
      loadingProducts,
      loadingSales,
      totalEarnings,
      showToast,
      removeToast,
      handleSaleCreated,
      formatPrice
    };
  }
};
</script>
