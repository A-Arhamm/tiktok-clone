<template>
    <MainLayout>
        <div class="pt-[80px] w-[calc(100%-90px)] max-w-[690px]">
            <PhoneVerificationPrompt
                v-if="showVerificationPrompt"
                @startVerification="startVerification"
            />
            <PhoneOtpInput
                v-if="showOtpInput"
                @verified="onVerified"
                @cancel="cancelVerification"
            />
            <div v-if="verificationSuccess" class="p-4 mb-4 bg-green-100 text-green-700 rounded">
                Your phone number is verified now.
            </div>
            <div v-for="post in $generalStore.posts" :key="post">
                <PostMain v-if="post" :post="post" />
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import MainLayout from '~/layouts/MainLayout.vue'
import PhoneVerificationPrompt from '~/components/PhoneVerificationPrompt.vue'
import PhoneOtpInput from '~/components/PhoneOtpInput.vue'
import { useUserStore } from '~/stores/user'

const { $generalStore } = useNuxtApp()
const userStore = useUserStore()

const showVerificationPrompt = ref(false)
const showOtpInput = ref(false)
const verificationSuccess = ref(false)

onMounted(async () => {
  try {
    await $generalStore.getAllUsersAndPosts()
    await userStore.getTokens()
    if (userStore.id) {
      await userStore.getUser()
      if (!userStore.phone_verified_at) {
        showVerificationPrompt.value = true
      } else {
        showVerificationPrompt.value = false
      }
    }
  } catch (error) {
    console.log(error)
  }
})

function startVerification() {
    showVerificationPrompt.value = false
    showOtpInput.value = true
    userStore.sendPhoneOtp().catch(err => {
        console.error('Failed to send OTP:', err)
    })
}

function onVerified() {
    showOtpInput.value = false
    verificationSuccess.value = true
    userStore.getUser() // refresh user data to get updated phone_verified_at
    setTimeout(() => {
        verificationSuccess.value = false
    }, 3000)
}

function cancelVerification() {
    showOtpInput.value = false
    showVerificationPrompt.value = true
}
</script>
