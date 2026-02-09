<script setup lang="ts">
import { onMounted, onBeforeUnmount } from 'vue'
import ComptaLayout from '@/components/templates/compta/ComptaLayout.vue'
import AccountsTable from './components/AccountsTable.vue'
import SubAccountsTable from './components/SubAccountsTable.vue'
import {
  useAccounts,
  useSubAccounts,
  useClassesAndCategories,
} from '@/composables/usePlanComptable'
import { useAccountsExport, useSubAccountsExport } from '@/composables/useComptaExport'
import { eventBus } from '@/utils/eventBus'

// Data management
const {
  accounts,
  accountsLoading,
  accountsError,
  accountsMeta,
  fetchAccounts,
  deleteAccount,
  deleting: deletingAccount,
} = useAccounts()

const {
  subAccountsData,
  subAccountsLoading,
  subAccountsError,
  fetchSubAccounts,
  deleteSubAccount,
  deleting: deletingSubAccount,
} = useSubAccounts()

const {
  classesData,
  categoriesData,
  classNameById,
  categoryNameById,
  fetchClasses,
  fetchCategories,
} = useClassesAndCategories()

// Export functionality
const { loading: exportAccountLoading, exportAccounts } = useAccountsExport()
const { loading: exportSubAccountLoading, exportSubAccounts } = useSubAccountsExport()

// Refresh all data
async function refreshAll() {
  await Promise.all([fetchAccounts(), fetchSubAccounts(), fetchClasses(), fetchCategories()])
}

// Lifecycle hooks
onMounted(async () => {
  await refreshAll()
  eventBus.on('planComptableUpdated', refreshAll)
  eventBus.on('subaccountComptableUpdated', refreshAll)
})

onBeforeUnmount(() => {
  eventBus.off('planComptableUpdated', refreshAll)
  eventBus.off('subaccountComptableUpdated', refreshAll)
})
</script>

<template>
  <ComptaLayout activeBread="Plan comptable" active-tag-name="plan-compta" group="saisie">
    <div class="grid lg:grid-cols-2 gap-7">
      <AccountsTable
        :accounts="accounts"
        :loading="accountsLoading"
        :error="accountsError"
        :deleting="deletingAccount"
        :classNameById="classNameById"
        :categoryNameById="categoryNameById"
        :classes="classesData || []"
        :categories="categoriesData || []"
        :meta="accountsMeta"
        :exportLoading="exportAccountLoading"
        :onFetchData="fetchAccounts"
        :onDelete="deleteAccount"
        :onExport="exportAccounts"
      />

      <SubAccountsTable
        :subAccounts="subAccountsData || []"
        :loading="subAccountsLoading"
        :error="subAccountsError"
        :deleting="deletingSubAccount"
        :exportLoading="exportSubAccountLoading"
        :onDelete="deleteSubAccount"
        :onExport="exportSubAccounts"
      />
    </div>
  </ComptaLayout>
</template>
