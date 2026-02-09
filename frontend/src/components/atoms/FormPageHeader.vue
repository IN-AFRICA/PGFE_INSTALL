<script lang="ts" setup>
import { Button } from '../ui/button'

defineProps<{ linkBack: string; title: string }>()
</script>
<template>
  <div class="flex items-center">
    <Button variant="outline" size="icon" as-child>
      <RouterLink :to="linkBack" class="flex items-center">
        <span class="flex iconify hugeicons--arrow-left-02"></span>
      </RouterLink>
    </Button>
    <h3 class="ml-3 font-semibold text-lg text-foreground-title">
      {{ title }}
    </h3>
  </div>
</template>
