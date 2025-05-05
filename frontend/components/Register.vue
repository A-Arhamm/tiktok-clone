<template>
    <div v-if="!showOTPVerification">
        <div class="text-center text-[28px] mb-4 font-bold">Sign up</div>

        <div class="px-6 pb-2">
            <TextInput placeholder="Full name" v-model:input="name" inputType="text" :autoFocus="true"
                :error="errors && errors.name ? errors.name[0] : ''" />
        </div>

        <div class="px-6 pb-2">
            <TextInput placeholder="Email address" v-model:input="email" inputType="email"
                :error="errors && errors.email ? errors.email[0] : ''" />
        </div>

        <div class="px-6 pb-2">
            <TextInput placeholder="Phone number" v-model:input="phoneNumber" inputType="text"
                :error="errors && errors.phone_number ? errors.phone_number[0] : ''" />
        </div>

        <div class="px-6 pb-2">
            <TextInput placeholder="Password" v-model:input="password" inputType="password"
                :error="errors && errors.password ? errors.password[0] : ''" />
        </div>

        <div class="px-6 pb-2">
            <TextInput placeholder="Confirm password" v-model:input="confirmPassword" inputType="password"
                :error="errors && errors.confirmPassword ? errors.confirmPassword[0] : ''" />
        </div>
        <div class="px-6 text-[12px] text-gray-600">Forgot password?</div>

        <div class="px-6 pb-2 mt-6">
            <button :disabled="(!name || !email || !password || !confirmPassword)"
                :class="(!name || !email || !password || !confirmPassword) ? 'bg-gray-200' : 'bg-[#F02C56]'"
                @click.prevent="register()"
                class="w-full text-[17px] font-semibold text-white bg-[#F02C56] py-3 rounded-sm">
                Sign up
            </button>
        </div>
    </div>

    <OTPVerification v-else :userId="registeredUserId" @verified="onVerified" />
</template>

<script setup>
import OTPVerification from './OTPVerification.vue'
const { $userStore, $generalStore } = useNuxtApp()

let name = ref(null)
let email = ref(null)
let phoneNumber = ref(null)
let password = ref(null)
let confirmPassword = ref(null)
let errors = ref(null)
let showOTPVerification = ref(false)
let registeredUserId = ref(null)

const register = async () => {
    errors.value = null

    try {
        await $userStore.getTokens()
        const response = await $userStore.register(
            name.value,
            email.value,
            phoneNumber.value,
            password.value,
            confirmPassword.value
        )
        showOTPVerification.value = true

        registeredUserId.value = response.data.user_id
    } catch (error) {
        console.log(error)
        errors.value = error.response.data.errors
    }
}

function onVerified() {
    $userStore.getUser()
    $generalStore.isLoginOpen = false
    showOTPVerification.value = false
}
</script>
