<template>
  <div class="panel">
    <div class="panel-header">
      <h2 class="panel-title">Registrar Venta</h2>
    </div>

    <form @submit.prevent="submitSale">
      <!-- Product Select -->
      <div class="form-group">
        <label for="product-select" class="form-label">Seleccionar Producto</label>
        <select 
          id="product-select" 
          v-model="selectedProductId" 
          class="form-select" 
          required
          :disabled="isSubmitting"
          @change="resetQuantity"
        >
          <option value="" disabled selected>-- Elige un producto --</option>
          <option 
            v-for="product in products" 
            :key="product.id" 
            :value="product.id"
            :disabled="product.stock === 0"
          >
            {{ product.name }} (Stock: {{ product.stock }})
          </option>
        </select>
      </div>

      <!-- Quantity Input -->
      <div class="form-group">
        <label for="quantity-input" class="form-label">Cantidad a Vender</label>
        <input 
          id="quantity-input" 
          v-model.number="quantity" 
          type="number" 
          min="1" 
          class="form-input" 
          placeholder="Escribe la cantidad" 
          required
          :disabled="!selectedProduct || isSubmitting"
        />
      </div>

      <!-- Selected Product Info & Dynamic Validation Preview -->
      <div v-if="selectedProduct" class="form-preview-card">
        <div class="preview-item">
          Precio Unitario: <span class="preview-val">${{ formatPrice(selectedProduct.price) }}</span>
        </div>
        <div class="preview-item">
          Stock Disponible: 
          <span :class="['preview-val', selectedProduct.stock === 0 ? 'text-danger' : '']">
            {{ selectedProduct.stock }} uds
          </span>
        </div>
      </div>

      <!-- Total Preview -->
      <div v-if="selectedProduct && quantity > 0" class="form-preview-card">
        <div class="preview-item" style="font-weight: 600;">Total a Pagar:</div>
        <div class="preview-val total">${{ formatPrice(totalCalculated) }}</div>
      </div>

      <!-- Validation Error Warnings -->
      <div 
        v-if="validationError" 
        class="stock-badge out-of-stock" 
        style="width: 100%; text-align: center; margin-bottom: 1.5rem; display: block; border-radius: 6px; padding: 0.5rem;"
      >
        ⚠️ {{ validationError }}
      </div>

      <!-- Submit Button -->
      <button 
        type="submit" 
        class="btn-submit" 
        :disabled="isSubmitDisabled"
      >
        <span v-if="isSubmitting" class="spinner"></span>
        <span>{{ isSubmitting ? 'Procesando...' : 'Completar Venta' }}</span>
      </button>
    </form>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import axios from 'axios';

export default {
  name: 'SaleForm',
  props: {
    products: {
      type: Array,
      required: true
    }
  },
  emits: ['sale-created', 'toast'],
  setup(props, { emit }) {
    const selectedProductId = ref('');
    const quantity = ref('');
    const isSubmitting = ref(false);

    // Get selected product object
    const selectedProduct = computed(() => {
      if (!selectedProductId.value) return null;
      return props.products.find(p => p.id === selectedProductId.value) || null;
    });

    // Reset quantity input when product changes
    const resetQuantity = () => {
      quantity.value = 1;
    };

    // Calculate total price preview
    const totalCalculated = computed(() => {
      if (!selectedProduct.value || !quantity.value || quantity.value <= 0) return 0;
      return selectedProduct.value.price * quantity.value;
    });

    // Live Validation Messages
    const validationError = computed(() => {
      if (!selectedProduct.value) return '';
      
      if (quantity.value === '') return '';
      
      if (!Number.isInteger(quantity.value) || quantity.value <= 0) {
        return 'La cantidad debe ser un número entero mayor a 0.';
      }
      
      if (quantity.value > selectedProduct.value.stock) {
        return `Cantidad excede el stock. Solo quedan ${selectedProduct.value.stock} unidades de ${selectedProduct.value.name}.`;
      }
      
      return '';
    });

    // Disable button helper
    const isSubmitDisabled = computed(() => {
      return (
        isSubmitting.value ||
        !selectedProduct.value ||
        quantity.value === '' ||
        !!validationError.value
      );
    });

    const formatPrice = (price) => {
      return parseFloat(price).toLocaleString('es-ES', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    };

    // Submit sale to Laravel API
    const submitSale = async () => {
      if (isSubmitDisabled.value) return;

      isSubmitting.value = true;
      try {
        const response = await axios.post('/api/sales', {
          product_id: selectedProductId.value,
          quantity: quantity.value
        });
        
        const data = response.value || response.data;

        // Emit success to parent to update local state (shares the returned sale object)
        emit('sale-created', data.sale);
        
        // Reset form fields
        selectedProductId.value = '';
        quantity.value = '';
      } catch (error) {
        console.error(error);
        const errorMsg = error.response?.data?.message || 'Error al registrar la venta. Inténtalo de nuevo.';
        emit('toast', errorMsg, 'error');
      } finally {
        isSubmitting.value = false;
      }
    };

    return {
      selectedProductId,
      quantity,
      isSubmitting,
      selectedProduct,
      totalCalculated,
      validationError,
      isSubmitDisabled,
      resetQuantity,
      formatPrice,
      submitSale
    };
  }
};
</script>
