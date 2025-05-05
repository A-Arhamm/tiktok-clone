import { defineStore } from 'pinia'
import axios from '../plugins/axios'
import { useUserStore } from './user'

const $axios = axios().provide.axios

export const useNotificationsStore = defineStore('notifications', {
  state: () => ({
    notifications: [],
  }),
  getters: {
    unreadCount: (state) => {
      return state.notifications.filter(n => !n.read_at).length
    }
  },
  actions: {
    async fetchNotifications() {
      const userStore = useUserStore()
      if (!userStore.id) {
        this.clearNotifications()
        return
      }
      try {
        const res = await $axios.get('/api/notifications')
        this.notifications = res.data.map(notification => {
          if (typeof notification.data === 'string') {
            try {
              notification.data = JSON.parse(notification.data)
            } catch (e) {
              console.error('Failed to parse notification data JSON:', e)
            }
          }
          return notification
        })
      } catch (error) {
        console.error('Failed to fetch notifications:', error)
      }
    },
    clearNotifications() {
      this.notifications = []
    }
  },
  persist: true,
})
