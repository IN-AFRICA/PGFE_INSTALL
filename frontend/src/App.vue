<script setup lang="ts">
import { RouterView } from 'vue-router'
import { Toaster } from 'vue-sonner'
import 'vue-sonner/style.css'
import { TooltipProvider } from '@/components/ui/tooltip'
</script>

<template>
  <RouterView />
  <Toaster position="bottom-center" />
</template>
