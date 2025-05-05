<template>
  <div class="p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded mb-4">
    <p class="mb-2">Please verify your phone number.</p>
    <button
      class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded"
      @click="$emit('startVerification')"
    >
      Verify Phone Number
    </button>
  </div>
</template>

<script setup>
defineProps(['startVerification'])
</script>
