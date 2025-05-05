<template>
  <div class="p-4 bg-white border border-gray-300 rounded shadow max-w-sm mx-auto">
    <h2 class="text-lg font-bold mb-4">Enter OTP</h2>
    <input
      v-model="otp"
      type="text"
      maxlength="6"
      placeholder="Enter 6-digit OTP"
      class="border border-gray-400 rounded px-3 py-2 w-full mb-4"
    />
    <div class="flex justify-between">
      <button
        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded"
        @click="verifyOtp"
        :disabled="loading || otp.length !== 6"
      >
        Verify
      </button>
      <button
        class="text-blue-500 underline"
        @click="$emit('cancel')"
        :disabled="loading"
      >
        Cancel
      </button>
    </div>
    <p v-if="error" class="text-red-500 mt-2">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useUserStore } from '~/stores/user'

const emit = defineEmits(['verified', 'cancel'])

const otp = ref('')
const error = ref('')
const loading = ref(false)

const userStore = useUserStore()

async function verifyOtp() {
  error.value = ''
  loading.value = true
  try {
    await userStore.verifyPhoneOtp(otp.value)
    emit('verified')
  } catch (e) {
    error.value = e.response?.data?.message || 'Verification failed'
  } finally {
    loading.value = false
  }
}
</script>
