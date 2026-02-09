import './assets/font.css'
import './index.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { useAuthStore } from './stores/auth'

const app = createApp(App)

app.use(createPinia())
app.use(router)

// Vérifier si l'utilisateur est connecté avant d'accéder à certaines routes

router.beforeEach((to, from, next) => {
  const authState = useAuthStore()
  const isAuthenticated = authState.isAuthenticated

  // Si l'utilisateur n'est pas connecté et essaie d'accéder à une page autre que login
  if (!isAuthenticated && to.name !== 'login') {
    next({ name: 'login' })
  }
  // Si l'utilisateur est connecté et essaie d'accéder à la page login
  else if (isAuthenticated && to.name === 'login') {
    // Vérifier si le token localStorage est égal au token du serveur
    authState
      .verifyToken()
      .then((isValid: boolean) => {
        if (isValid) {
          next({ name: 'root' }) // Remplace 'root' par la route principale de ton app si besoin
        } else {
          authState.logout()
          next({ name: 'login' })
        }
      })
      .catch(() => {
        authState.logout()
        next({ name: 'login' })
      })
  } else {
    next()
  }
})

app.mount('#app')
