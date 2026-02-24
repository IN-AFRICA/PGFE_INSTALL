import { defineStore } from 'pinia'
import type { RouteLocationNormalizedGeneric } from 'vue-router'

interface AppState {
    /** True while a global sync is in progress (detected via 503) */
    isGlobalSyncing: boolean
    /** The route the user was on before being redirected to the sync screen */
    savedRoute: RouteLocationNormalizedGeneric | null
}

export const useAppStore = defineStore('app', {
    state: (): AppState => ({
        isGlobalSyncing: false,
        savedRoute: null,
    }),

    actions: {
        /**
         * Activate the global sync lock.
         * Saves the user's current route so we can restore it later.
         */
        enterSyncLock(currentRoute: RouteLocationNormalizedGeneric) {
            if (this.isGlobalSyncing) return // Already locked
            this.isGlobalSyncing = true
            this.savedRoute = currentRoute
        },

        /**
         * Release the global sync lock and clear the saved route.
         * Returns the saved route so the caller can navigate back.
         */
        exitSyncLock(): RouteLocationNormalizedGeneric | null {
            this.isGlobalSyncing = false
            const route = this.savedRoute
            this.savedRoute = null
            return route
        },
    },
})
