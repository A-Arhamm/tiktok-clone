<template>
    <MainLayout>
        <div v-if="$profileStore.name"
            class="pt-[90px] 2xl:pl-[185px] lg:pl-[230px] lg:pr-0 pr-2 w-full max-w-[1800px] 2xl:mx-auto">
            <div class="flex w-full items-center">
                <img class="max-w-[120px] rounded-full" :src="$profileStore.image">
                <div class="ml-5 w-full">
                    <div class="text-[30px] font-bold truncate">
                        {{ $generalStore.allLowerCaseNoCaps($profileStore.name) }}
                    </div>
                    <div class="text-[18px] truncate">{{ $profileStore.name }}</div>
                    <button v-if="$profileStore.id === $userStore.id" @click="$generalStore.isEditProfileOpen = true"
                        class="flex items-center rounded-md py-1.5 px-3.5 mt-3 text-[15px] font-semibold border hover:bg-gray-100">
                        <Icon class="mt-0.5 mr-1" name="mdi:pencil" size="18" />
                        <div>Edit profile</div>
                    </button>

                    <button v-if="$profileStore.id === $userStore.id" @click="togglePrivate"
                        :class="['flex item-center rounded-md py-1.5 px-3.5 mt-3 ml-3 text-[15px] font-semibold border', $profileStore.isPrivate ? 'bg-gray-300 text-black' : 'text-white bg-[#F02C56]']">
                        {{ $profileStore.isPrivate ? 'Private Account' : 'Public Account' }}
                    </button>

                    <button v-else @click="toggleFollow"
                        :class="['flex item-center rounded-md py-1.5 px-8 mt-3 font-semibold', $profileStore.isFollowing ? 'bg-gray-300 text-black' : 'text-white bg-[#F02C56]']">
                        {{ $profileStore.isFollowing ? 'Following' : 'Follow' }}
                    </button>
                </div>
            </div>

            <div class="flex items-center pt-4">
                <div class="mr-4">
                    <span class="font-bold">{{ $profileStore.followingCount }}</span>
                    <span class="text-gray-500 font-light text-[15px] pl-1.5">Following</span>
                </div>
                <div class="mr-4">
                    <span class="font-bold">{{ $profileStore.followersCount }}</span>
                    <span class="text-gray-500 font-light text-[15px] pl-1.5">Followers</span>
                </div>
                <div class="mr-4">
                    <span class="font-bold">{{ allLikes }}</span>
                    <span class="text-gray-500 font-light text-[15px] pl-1.5">Likes</span>
                </div>
            </div>

            <div class="pt-4 mr-4 text-gray-500 font-light text-[15px] pl-1.5 max-w-[500px]">
                {{ $profileStore.bio }}
            </div>

            <div class="w-full flex items-center pt-4 border-b">
                <div 
                    class="w-60 text-center py-2 text-[17px] font-semibold cursor-pointer"
                    :class="{'border-b-2 border-b-black': $profileStore.activeTab === 'videos', 'text-gray-500': $profileStore.activeTab !== 'videos'}"
                    @click="switchTab('videos')"
                >
                    Videos
                </div>
                <div 
                    class="w-60 text-center py-2 text-[17px] font-semibold cursor-pointer"
                    :class="{'border-b-2 border-b-black': $profileStore.activeTab === 'liked', 'text-gray-500': $profileStore.activeTab !== 'liked'}"
                    @click="switchTab('liked')"
                >
                    <Icon name="material-symbols:lock-open" class="mb-0.5" /> Liked
                </div>
            </div>

            <div v-if="$profileStore.accountPrivate" class="mt-4 text-center text-gray-500 font-semibold text-lg">
                Account is private
            </div>

            <div v-else class="mt-4 grid 2xl:grid-cols-6 xl:grid-cols-5 lg:grid-cols-4 md:grid-cols-3 grid-cols-2 gap-3">
                <div v-if="show && $profileStore.activeTab === 'videos'" v-for="post in $profileStore.posts" :key="post.id">
                    <PostUser :post="post" />
                </div>
                <div v-if="show && $profileStore.activeTab === 'liked'" v-for="post in $profileStore.likedPosts" :key="post.id">
                    <PostUser :post="post" />
                </div>
            </div>
        </div>
    </MainLayout>

</template>

<script setup>
import MainLayout from '~/layouts/MainLayout.vue';
import { storeToRefs } from 'pinia';
const { $userStore, $profileStore, $generalStore } = useNuxtApp()
const { posts, allLikes } = storeToRefs($profileStore)

const route = useRoute()
let show = ref(false)

definePageMeta({ middleware: 'auth' })

async function toggleFollow() {
    if ($profileStore.isFollowing) {
        await $profileStore.unfollowUser($profileStore.id)
    } else {
        await $profileStore.followUser($profileStore.id)
    }

    await $profileStore.getProfile($profileStore.id)
}

function togglePrivate() {
    $profileStore.togglePrivateAccount()
}

function switchTab(tab) {
    $profileStore.activeTab = tab
    if (tab === 'liked') {
        $profileStore.fetchLikedPosts($profileStore.id)
    }
}

onMounted(async () => {
    try {
        await $profileStore.getProfile(route.params.id)
    } catch (error) {
        console.log(error)
    }
})

watch(() => posts.value, () => {
    setTimeout(() => show.value = true, 300)
})
</script>
