import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import { useGeneralStore } from '../stores/general'
import { useUserStore } from '../stores/user'

export default defineNuxtPlugin((nuxtApp) => {
  if (process.client) {
    const key = process.env && process.env.REVERB_APP_KEY ? process.env.REVERB_APP_KEY : '2udzflfzjmm1qpzazz4a'
    const host = process.env && process.env.REVERB_HOST ? process.env.REVERB_HOST : 'localhost'
    const port = process.env && process.env.REVERB_PORT ? process.env.REVERB_PORT : 8080
    const scheme = process.env && process.env.REVERB_SCHEME ? process.env.REVERB_SCHEME : 'http'

    window.Pusher = Pusher

    window.Echo = new Echo({
      broadcaster: 'reverb',
      key: key,
      wsHost: host,
      wsPort: port,
      wssPort: port,
      forceTLS: scheme === 'https',
      encrypted: scheme === 'https',
      disableStats: true,
      enabledTransports: ['ws', 'wss'],
    })

    nuxtApp.provide('echo', window.Echo)

    window.Echo.connector.pusher.connection.bind('connected', () => {
      const generalStore = useGeneralStore()
      const userStore = useUserStore()
      window.Echo.channel('post.comments.' + window.location.pathname.split('/').pop())
        .listen('CommentUpdated', (e) => {
          if (generalStore.selectedPost && generalStore.selectedPost.id === e.postId) {
            console.log('Received CommentUpdated event:', e)
            userStore.updateComments(generalStore.selectedPost)
          }
        })
    })
  }
})
