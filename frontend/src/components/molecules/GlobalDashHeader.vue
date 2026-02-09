<script setup lang="ts">
import RightDashHeader from '../atoms/RightDashHeader.vue'
</script>
<template>
  <header class="flex items-center fixed z-50 md:sticky top-0 w-full">
    <nav
      class="flex justify-between items-center h-16 border-b border-border/60 w-full bg-white px-4 sm:px-8 md:hidden"
    >
      <div>
        <router-link to="/">
          <img src="/pgfe-logo.png" alt="pattern background" class="size-18 object-cover" />
        </router-link>
      </div>
      <RightDashHeader />
    </nav>
  </header>
</template>
