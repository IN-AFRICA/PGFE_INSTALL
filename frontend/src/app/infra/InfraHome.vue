<script setup lang="ts">
import DashLayout from '@/components/templates/DashLayout.vue'

const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Infrastructure', href: '/infra', isActive: true },
  ],
}
</script>

<template>
  <DashLayout :breadcrumb="breadcrumbItems" active-route="/infra" module-name="infra">
    <div class="pb-6 mx-auto w-full max-w-7xl">
      <div class="bg-white rounded-lg shadow-sm p-8 text-center">
        <p class="mt-2 text-gray-600">Bienvenue dans le module Infrastructure</p>
      </div>
    </div>
  </DashLayout>
</template>

<style scoped></style>
