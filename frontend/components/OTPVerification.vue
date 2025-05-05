<template>
  <div class="max-w-md mx-auto p-4 bg-white rounded shadow">
    <h2 class="text-center text-2xl font-bold mb-4">Verify Your Email</h2>
    <p class="mb-4">Please enter the OTP sent to your email address.</p>
    <input v-model="otp" type="text" maxlength="6" placeholder="Enter 6-digit OTP"
      class="w-full p-2 border border-gray-300 rounded mb-4 text-center text-xl tracking-widest" />
    <div v-if="error" class="text-red-600 mb-4">{{ error }}</div>
    <button @click="verifyOtp" :disabled="otp.length !== 6 || loading"
      class="w-full bg-blue-600 text-white py-2 rounded disabled:opacity-50">
      {{ loading ? 'Verifying...' : 'Verify' }}
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useUserStore } from '~/stores/user'
import { useNuxtApp } from '#app'

const props = defineProps({
  userId: {
    type: [String, Number],
    required: false,
    default: null
  }
})

const emit = defineEmits(['verified'])

const otp = ref('')
const error = ref(null)
const loading = ref(false)

const verifyOtp = async () => {
  const nuxtApp = useNuxtApp()
  error.value = null
  if (!props.userId) {
    error.value = 'User ID is missing.'
    return
  }
  loading.value = true
  try {
    // Log userId and OTP before sending request
    console.log('Verifying OTP for userId:', props.userId, 'OTP:', otp.value)

    const response = await nuxtApp.$axios.post('/otp/verify', {
      user_id: props.userId,
      otp: otp.value
    })
    if (response.status === 200) {
      emit('verified')
    }
  } catch (err) {
    console.log(err)
    if (err.response && err.response.data && err.response.data.message) {
      error.value = err.response.data.message
    } else {
      error.value = 'Verification failed. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
input:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.5);
}
</style>
