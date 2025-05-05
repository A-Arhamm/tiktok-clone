import { defineStore } from 'pinia'
import axios from '../plugins/axios'

const $axios = axios().provide.axios

export const useProfileStore = defineStore('profile', {
    state: () => ({
    id: '',
    name: '',
    bio: '',
    image: '',
    post: null,
    posts: null,
    likedPosts: null,
    allLikes: 0,
    followersCount: 0,
    followingCount: 0,
    isFollowing: false,
    isPrivate: false,
    accountPrivate: false,
    activeTab: 'videos', // 'videos' or 'liked'
  }),
  actions: {
    async getProfile(id) {
      this.resetUser()
      let res = await $axios.get(`/api/profiles/${id}`)
      
      this.$state.id = res.data.user[0].id
      this.$state.name = res.data.user[0].name
      this.$state.bio = res.data.user[0].bio
      this.$state.image = res.data.user[0].image

      this.$state.posts = res.data.posts
      this.$state.followersCount = res.data.followersCount || 0
      this.$state.followingCount = res.data.followingCount || 0
      this.$state.isFollowing = res.data.isFollowing || false
      this.$state.isPrivate = res.data.isPrivate || false
      this.$state.accountPrivate = res.data.accountPrivate || false

      this.allLikesCount()
    },

    async togglePrivateAccount() {
      try {
        const res = await $axios.post('/api/profiles/toggle-private')
        this.isPrivate = res.data.isPrivate
      } catch (error) {
        console.error('Failed to toggle private account:', error)
      }
    },

    async followUser(userId) {
      try {
        await $axios.post(`/api/profiles/${userId}/follow`)
        this.isFollowing = true
        this.followersCount++
      } catch (error) {
        console.error('Failed to follow user:', error)
      }
    },

    async unfollowUser(userId) {
      try {
        await $axios.post(`/api/profiles/${userId}/unfollow`)
        this.isFollowing = false
        this.followersCount--
      } catch (error) {
        console.error('Failed to unfollow user:', error)
      }
    },

    async fetchLikedPosts(userId) {
      try {
        const res = await $axios.get(`/api/profiles/${userId}/liked`)
        this.likedPosts = res.data.likedPosts
      } catch (error) {
        console.error('Failed to fetch liked posts:', error)
      }
    },

    allLikesCount() {
        this.allLikes = 0
        for (let i = 0; i < this.posts.length; i++) {
            const post = this.posts[i];
             for (let j = 0; j < post.likes.length; j++) {
                this.allLikes++
             }
        }
    },

    resetUser() {      
        this.$state.id = ''
        this.$state.name = ''
        this.$state.bio = ''
        this.$state.image = ''
        this.$state.posts = ''
        this.$state.likedPosts = null
        this.$state.followersCount = 0
        this.$state.followingCount = 0
        this.$state.isFollowing = false
        this.activeTab = 'videos'
      }
  },
  persist: true,
})
