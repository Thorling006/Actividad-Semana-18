<template>
  <div class="panel" style="margin-top: 1rem;">
    <div class="panel-header">
      <h2 class="panel-title">Registro de Ventas</h2>
      <span class="stock-badge sales" v-if="sales.length">{{ sales.length }} Ventas Realizadas</span>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="empty-state">
      <div class="spinner" style="margin: 0 auto 10px;"></div>
      Cargando ventas...
    </div>

    <!-- Empty State -->
    <div v-else-if="sales.length === 0" class="empty-state">
      No se han registrado ventas todavía.
    </div>

    <!-- Transactions Table -->
    <div v-else class="sales-table-wrapper">
      <table class="sales-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Total</th>
            <th>Fecha y Hora</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="sale in sales" :key="sale.id">
            <td>#{{ sale.id }}</td>
            <td style="font-weight: 500; color: #fff;">
              {{ sale.product ? sale.product.name : 'Producto Eliminado' }}
            </td>
            <td>
              <span class="sales-qty">{{ sale.quantity }}</span>
            </td>
            <td>
              <span class="sales-total-badge">{{ formatPrice(sale.total_price) }}</span>
            </td>
            <td style="color: var(--text-secondary); font-size: 0.85rem;">
              {{ formatDate(sale.created_at) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SaleList',
  props: {
    sales: {
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
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    };

    const formatDate = (dateString) => {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      });
    };

    return {
      formatPrice,
      formatDate
    };
  }
};
</script>
