<template>
    <div v-if="!post.user.is_private" :id="`PostMain-${post.id}`" class="flex border-b py-6">
        <div @click="isLoggedIn(post.user)" class="cursor-pointer">
            <img class="rounded-full max-h-[60px]" width="60" :src="post.user.image">
        </div>
        <div class="pl-3 w-full px-4">
            <div class="flex items-center justify-between pb-0.5">
                <button @click="isLoggedIn(post.user)">
                    <span class="font-bold hover:underline cursor-pointer">
                        {{ $generalStore.allLowerCaseNoCaps(post.user.name) }}
                    </span>
                    <span class="text-[13px] text-light text-gray-500 pl-1 cursor-pointer">
                        {{ post.user.name }}
                    </span>
                </button>
            </div>
            <div class="text-[15px] pb-0.5 break-words md:max-w-[400px] max-w-[300px]">{{ post.text }}</div>
            <div class="text-[14px] text-gray-500 pb-0.5">#fun #cool #SuperAwesome</div>
            <div class="text-[14px] pb-0.5 flex items-center font-semibold">
                <Icon name="mdi:music" size="17" />
                <div class="px-1">original sound - AWESOME</div>
                <Icon name="mdi:heart" size="20" />
            </div>

            <div class="mt-2.5 flex">
                <div @click="displayPost(post)"
                    class="relative min-h-[480px] max-h-[580px] max-w-[260px] flex items-center bg-black rounded-xl cursor-pointer">
                    <video v-if="post.video" ref="video" loop muted class="rounded-xl object-cover mx-auto h-full"
                        :src="post.video" />
                    <img class="absolute right-2 bottom-14" width="90" src="~/assets/images/tiktok-logo-white.png">
                </div>
                <div class="relative mr-[75px]">
                    <div class="absolute bottom-0 pl-2">
                        <div class="pb-4 text-center">
                            <button @click="isLiked ? unlikePost(post) : likePost(post)"
                                class="rounded-full bg-gray-200 p-2 cursor-pointer">
                                <Icon name="mdi:heart" size="25" :color="isLiked ? '#F02C56' : ''" />
                            </button>
                            <span class="text-xs text-gray-800 font-semibold">{{ post.likesCount ?? post.likes.length
                                }}</span>
                        </div>

                        <div class="pb-4 text-center">
                            <div class="rounded-full bg-gray-200 p-2 cursor-pointer">
                                <Icon name="bx:bxs-message-rounded-dots" size="25" />
                            </div>
                            <span class="text-xs text-gray-800 font-semibold">{{ post.comments.length }}</span>
                        </div>

                        <div class="text-center">
                            <div class="rounded-full bg-gray-200 p-2 cursor-pointer">
                                <Icon name="ri:share-forward-fill" size="25" />
                            </div>
                            <span class="text-xs text-gray-800 font-semibold">55</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref, computed, toRefs } from 'vue'
import { useNuxtApp } from '#app'

const { $generalStore, $userStore, $echo } = useNuxtApp()
const props = defineProps(['post'])
const { post } = toRefs(props)

const router = useRouter()

let video = ref(null)

onMounted(() => {
    let observer = new IntersectionObserver(function (entries) {
        if (entries[0].isIntersecting) {
            if (video.value && video.value.src && video.value.canPlayType && video.value.canPlayType('video/mp4')) {
                video.value.play().catch((e) => {
                    if (e.name !== 'AbortError') {
                        console.error(e);
                    }
                });
            } else {
                console.error('Video format not supported or video element not ready');
            }
        } else {
            if (video.value) {
                video.value.pause()
            }
        }

    }, { threshold: [0.6] });

    observer.observe(document.getElementById(`PostMain-${post.value.id}`));

    if ($echo) {
        $echo.channel(`post.likes.${post.value.id}`)
            .listen('LikeUpdated', (e) => {
                if (e.postId === post.value.id) {

                    post.value.likesCount = e.likesCount
                }
            })
    }
})

onBeforeUnmount(() => {
    video.value.pause()
    video.value.currentTime = 0
    video.value.src = ''
})

const isLiked = computed(() => {
    let res = post.value.likes.find(like => like.user_id === $userStore.id)
    if (res) {
        return true
    }
    return false
})

const likePost = async (post) => {
    if (!$userStore.id) {
        $generalStore.isLoginOpen = true
        return
    }
    try {
        await $userStore.likePost(post)
    } catch (error) {
        console.log(error)
    }
}

const unlikePost = async (post) => {
    if (!$userStore.id) {
        $generalStore.isLoginOpen = true
        return
    }
    try {
        await $userStore.unlikePost(post, false)
    } catch (error) {
        console.log(error)
    }
}

const isLoggedIn = (user) => {
    if (!$userStore.id) {
        $generalStore.isLoginOpen = true
        return
    }
    setTimeout(() => router.push(`/profile/${user.id}`), 200)
}

const displayPost = (post) => {
    if (!$userStore.id) {
        $generalStore.isLoginOpen = true
        return
    }

    $generalStore.setBackUrl('/')
    $generalStore.selectedPost = null
    setTimeout(() => router.push(`/post/${post.id}`), 200)
}
</script>