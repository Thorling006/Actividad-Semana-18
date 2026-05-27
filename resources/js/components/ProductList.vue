<template>
  <div class="panel">
    <div class="panel-header">
      <h2 class="panel-title">Catálogo de Productos</h2>
      <span class="stock-badge in-stock" v-if="products.length">{{ products.length }} Productos</span>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="empty-state">
      <div class="spinner" style="margin: 0 auto 10px;"></div>
      Cargando productos...
    </div>

    <!-- Empty State -->
    <div v-else-if="products.length === 0" class="empty-state">
      No hay productos disponibles en el inventario.
    </div>

    <!-- Product Cards Grid -->
    <div v-else class="product-grid">
      <div v-for="product in products" :key="product.id" class="product-card">
        <h3 class="product-name">{{ product.name }}</h3>
        <div class="product-price">{{ formatPrice(product.price) }}</div>
        
        <div class="product-meta">
          <span class="product-stock">Stock: <strong>{{ product.stock }}</strong></span>
          <span :class="['stock-badge', getStockClass(product.stock)]">
            {{ getStockLabel(product.stock) }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProductList',
  props: {
    products: {
      type: Array,
      required: true
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  setup() {
    const formatPrice = (price) => {
      return parseFloat(price).toLocaleString('es-ES', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    };

    const getStockClass = (stock) => {
      if (stock === 0) return 'out-of-stock';
      if (stock <= 5) return 'low-stock';
      return 'in-stock';
    };

    const getStockLabel = (stock) => {
      if (stock === 0) return 'Agotado';
      if (stock <= 5) return 'Stock Bajo';
      return 'Disponible';
    };

    return {
      formatPrice,
      getStockClass,
      getStockLabel
    };
  }
};
</script>
