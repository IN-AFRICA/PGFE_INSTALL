<script setup lang="ts">
import { Button } from '../ui/button'
</script>

<template>
  <div class="flex gap-3 items-center">
    <Button variant="ghost" size="icon">
      <span class="size-5 iconify hugeicons--setting-07" />
    </Button>
    <Button variant="ghost" size="icon">
      <span class="size-5 iconify hugeicons--notification-03" />
    </Button>
    <Button variant="ghost" size="icon">
      <span class="size-5 iconify hugeicons--user-02" />
    </Button>
  </div>
</template>
