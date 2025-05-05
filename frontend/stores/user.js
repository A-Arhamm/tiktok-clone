import { defineStore } from 'pinia'
import axios from '../plugins/axios'
import { useGeneralStore } from './general'

const $axios = axios().provide.axios

export const useUserStore = defineStore('user', {
  state: () => ({
    id: '',
    name: '',
    bio: '',
    image: '',
    phone_number: '',
    phone_verified_at: null,
  }),
  actions: {

    async getTokens() {
      await $axios.get('/sanctum/csrf-cookie')
    },

    async login(email, password) {
      await $axios.post('/login', {
        email: email,
        password: password
      })
    },

    async register(name, email, phoneNumber, password, confirmPassword) {
      return await $axios.post('/register', {
        name: name,
        email: email,
        phone_number: phoneNumber,
        password: password,
        password_confirmation: confirmPassword
      })
    },

    async getUser() {
      try {
        let res = await $axios.get('/api/logged-in-user')
        if (Array.isArray(res.data) && res.data.length > 0) {
          this.$state.id = res.data[0].id
          this.$state.name = res.data[0].name
          this.$state.bio = res.data[0].bio
          this.$state.image = res.data[0].image
          this.$state.phone_number = res.data[0].phone_number || ''
          this.$state.phone_verified_at = res.data[0].phone_verified_at || null
        } else {
          this.resetUser()
        }
      } catch (error) {
        if (error.response && error.response.status === 401) {
          // Unauthorized, reset user without throwing error
          this.resetUser()
        } else {
          throw error
        }
      }
    },

    async updateUserImage(data) {
      return await $axios.post('/api/update-user-image', data)
    },

    async updateUser(name, bio) {
      return await $axios.patch('/api/update-user', {
        name: name,
        bio: bio
      })
    },

    async createPost(data) {
      return await $axios.post('/api/posts', data)
    },

    async deletePost(post) {
      return await $axios.delete(`/api/posts/${post.id}`)
    },

    async addComment(post, comment) {
      let res = await $axios.post('/api/comments', {
        post_id: post.id,
        comment: comment
      })

      if (res.status === 200) {
        await this.updateComments(post)
      }
    },

    async deleteComment(post, commentId) {
      let res = await $axios.delete(`/api/comments/${commentId}`, {
        post_id: post.id
      })

      if (res.status === 200) {
        await this.updateComments(post)
      }
    },

    async updateComments(post) {
      let res = await $axios.get(`/api/profiles/${post.user.id}`)

      for (let i = 0; i < res.data.posts.length; i++) {
          const updatePost = res.data.posts[i];

          if (post.id == updatePost.id) {
              useGeneralStore().selectedPost.comments = updatePost.comments
          }
      }
    },

    async likePost(post, isPostPage) {
      let res = await $axios.post('/api/likes', {
        post_id: post.id,
      })

      console.log(res)

      let singlePost = null

      if (isPostPage) {
        singlePost = post
      } else {
        singlePost = useGeneralStore().posts.find(p => p.id === post.id)
      }
      console.log(singlePost)
      singlePost.likes.push(res.data.like)
    },

    async unlikePost(post, isPostPage) {
      let deleteLike = null
      let singlePost = null

      if (isPostPage) {
        singlePost = post
      } else {
        singlePost = useGeneralStore().posts.find(p => p.id === post.id)
      }

      singlePost.likes.forEach(like => {
        if (like.user_id === this.id) { deleteLike = like }
      });
      
      let res = await $axios.delete('/api/likes/' + deleteLike.id)

      for (let i = 0; i < singlePost.likes.length; i++) {
        const like = singlePost.likes[i];
        if (like.id === res.data.like.id) { singlePost.likes.splice(i, 1); }
      }
    },


    async logout() {
      await $axios.post('/logout')
      this.resetUser()
    },

    async sendPhoneOtp() {
      return await $axios.post('/api/phone/send-otp');
    },

    async verifyPhoneOtp(otp) {
      return await $axios.post('/api/phone/verify-otp', { otp });
    },

    resetUser() {      
      this.$state.id = ''
      this.$state.name = ''
      this.$state.email = ''
      this.$state.bio = ''
      this.$state.image = ''
      this.$state.phone_number = ''
      this.$state.phone_verified_at = null
    }

  },
  persist: true,
})
