<script lang="ts" setup>
import CAnimationWrapper from '@/components/atoms/CAnimationWrapper.vue'
import ModuleCard from '@/components/atoms/ModuleCard.vue'
import { modulesItems } from '@/data/navigations'
import DashSibarNavigation from '@/components/molecules/DashSibarNavigation.vue'
import { useAuthStore } from '@/stores/auth'
import { computed, ref } from 'vue'
import { usePostApi } from '@/composables/usePostApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { useSyncAnimation } from '@/composables/useSyncAnimation'

const authStore = useAuthStore()
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

const handleSyncClick = async () => {
  if (isSyncing.value) return

  const syncEl = syncCardRef.value
  const cardEls = cardRefs.value.filter((el): el is HTMLElement => el !== null)

  if (!syncEl) return

  const finish = async (toast: Parameters<typeof showCustomToast>[0]) => {
    const orbitEl = orbitContainerRef.value
    if (orbitEl) await endAnimation(syncEl, cardEls, orbitEl)
    showCustomToast(toast)
  }

  startAnimation(syncEl, cardEls, orbitContainerRef)

  await postData(API_ROUTES.SYNC, {})

  if (syncSuccess.value) {
    finish({ message: 'Synchronisation effectuée avec succès', type: 'success' })
  } else {
    finish({ message: 'Erreur lors de la synchronisation', type: 'error' })
  }
}
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
