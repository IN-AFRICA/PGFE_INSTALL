<script lang="ts" setup>
import CAnimationWrapper from '@/components/atoms/CAnimationWrapper.vue'
import ModuleCard from '@/components/atoms/ModuleCard.vue'
import { modulesItems } from '@/data/navigations'
import DashSibarNavigation from '@/components/molecules/DashSibarNavigation.vue'
import { useAuthStore } from '@/stores/auth'
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { usePostApi } from '@/composables/usePostApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import api from '@/services/api'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { useSyncAnimation } from '@/composables/useSyncAnimation'
import { useAppStore } from '@/stores/app'

const authStore = useAuthStore()
const appStore = useAppStore()
const router = useRouter()
const { postData, success: syncSuccess } = usePostApi()
const {
  isSyncing,
  showOverlay,
  startAnimation,
  endAnimation,
} = useSyncAnimation()

const syncCardRef = ref<HTMLElement | null>(null)
const cardRefs = ref<(HTMLElement | null)[]>([])
const orbitContainerRef = ref<HTMLElement | null>(null)

const filteredModules = computed(() =>
  modulesItems.filter((item) => (!item.permission ? true : authStore.can(item.permission))),
)

const regularModules = computed(() => filteredModules.value.filter((item) => item.id !== 10))
const syncModule = computed(() => filteredModules.value.find((item) => item.id === 10))

const setCardRef = (el: unknown, index: number) => {
  cardRefs.value[index] = (el as any)?.$el ?? null
}

const finishSyncAnimation = async (toastMessage?: string, toastType: 'success' | 'error' = 'success') => {
  const syncEl = syncCardRef.value
  const cardEls = cardRefs.value.filter((el): el is HTMLElement => el !== null)
  const orbitEl = orbitContainerRef.value

  if (syncEl && orbitEl) {
    await endAnimation(syncEl, cardEls, orbitEl)
  }
  
  const savedRoute = appStore.exitSyncLock()
  
  if (toastMessage) {
    showCustomToast({ message: toastMessage, type: toastType })
  }

  // Restore previous route if we were locked out
  if (savedRoute && savedRoute.path !== '/') {
    router.replace(savedRoute.fullPath)
  }
}

const handleSyncClick = async () => {
  if (isSyncing.value || appStore.isGlobalSyncing) return

  const syncEl = syncCardRef.value
  const cardEls = cardRefs.value.filter((el): el is HTMLElement => el !== null)

  if (!syncEl) return

  // Mark ourself as the sync lock initiator (saving the root route)
  appStore.enterSyncLock(router.currentRoute.value)

  startAnimation(syncEl, cardEls, orbitContainerRef)

  await postData(API_ROUTES.SYNC, {})

  if (syncSuccess.value) {
    await finishSyncAnimation('Synchronisation effectuée avec succès', 'success')
  } else {
    await finishSyncAnimation('Erreur lors de la synchronisation', 'error')
  }
}

// === Global Lock Polling ===
let pollInterval: ReturnType<typeof setInterval> | null = null

async function checkSyncStatus() {
  try {
    await api.get(API_ROUTES.SYNC_STATUS)
    // If 200 OK, sync is finished
    await finishSyncAnimation('Synchronisation globale terminée.', 'success')
    if (pollInterval) clearInterval(pollInterval)
  } catch (err: any) {
    const status = err?.response?.status
    if (status === 503) {
      // Still syncing
      return
    }
    // Any other error means the sync lock is probably gone
    if (status === 200 || status === 404 || !status) {
      await finishSyncAnimation()
      if (pollInterval) clearInterval(pollInterval)
    }
  }
}

onMounted(() => {
  // If the store says we are globally syncing, but the animation isn't running
  // (Meaning we were redirected here by the 503 interceptor)
  if (appStore.isGlobalSyncing && !isSyncing.value) {
    const syncEl = syncCardRef.value
    const cardEls = cardRefs.value.filter((el): el is HTMLElement => el !== null)
    
    // We delay the animation start slightly to allow the DOM to render the cards
    setTimeout(() => {
      if (syncEl) {
        startAnimation(syncEl, cardEls, orbitContainerRef)
        pollInterval = setInterval(checkSyncStatus, 15000)
      }
    }, 100)
  }
})

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval)
})
</script>

<template>
  <div class="relative px-4 sm:px-8 lg:px-4 xl:px-0">
    <div class="fixed inset-0 pointer-events-none select-none">
      <img src="/screen-pattern.png" alt="pattern background" class="size-full object-cover" />
    </div>

    <CAnimationWrapper class="relative max-w-4xl mx-auto w-full py-6" as="div">
      <div class="flex justify-center mb-8">
        <img src="/pgfe-logo.png" width="500" class="h-32 w-auto" />
      </div>
      <div class="mb-4">
        <h2 class="text-2xl font-semibold text-fg-title uppercase mb-4">Selectionnez le module</h2>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-5 lg:gap-6">
        <ModuleCard
          v-for="(item, idx) in regularModules"
          :key="item.id"
          :ref="(el) => setCardRef(el, idx)"
          :is-active="item.id === 1"
          v-bind="item"
        />

        <div
          v-if="syncModule"
          ref="syncCardRef"
          class="flex flex-col items-center justify-center p-4 md:p-6 rounded-lg cursor-pointer
                 transition-all duration-200 ease-linear relative z-10
                 bg-white border border-border/50 shadow-lg shadow-gray-200/30
                 hover:bg-primary hover:text-white hover:shadow-xl text-foreground-muted
                 min-h-[120px]"
          :class="{ 'sync-active': isSyncing }"
          @click="handleSyncClick"
        >
          <span aria-hidden="true" :class="['text-4xl md:text-5xl flex iconify', syncModule.icon]" />
          <span class="text-center select-none mt-4 font-medium">{{ syncModule.text }}</span>
        </div>
      </div>
    </CAnimationWrapper>

    <Teleport to="body">
      <Transition name="overlay-fade">
        <div
          v-if="showOverlay"
          class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center"
        >
          <div
            ref="orbitContainerRef"
            class="absolute inset-0 flex items-center justify-center pointer-events-none"
            style="z-index: 1"
          />
          <div
            v-if="syncModule"
            class="relative flex flex-col items-center justify-center bg-white rounded-lg p-6 shadow-2xl min-h-[140px] min-w-[160px]"
            style="z-index: 10"
          >
            <span
              aria-hidden="true"
              :class="['text-5xl flex iconify sync-icon-spin', syncModule.icon]"
            />
            <span class="text-center select-none mt-3 font-medium text-primary text-sm">Synchronisation en cours...</span>
          </div>
        </div>
      </Transition>
    </Teleport>

    <DashSibarNavigation :onlySettings="true" />
  </div>
</template>

<style scoped>
.overlay-fade-enter-active,
.overlay-fade-leave-active {
  transition: opacity 0.5s ease;
}
.overlay-fade-enter-from,
.overlay-fade-leave-to {
  opacity: 0;
}
.sync-icon-spin {
  font-size: 2.5rem;
  animation: spin 12s linear infinite;
}
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
