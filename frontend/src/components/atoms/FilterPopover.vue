<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button variant="ghost" size="sm" class="h-10 rounded-md border bg-white">
        <span class="hidden sm:flex">Filtre</span>
        <span class="iconify hugeicons--filter"></span>
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-80">
      <div class="grid gap-4">
        <div class="space-y-2">
          <h4 class="font-medium leading-none">Filtrage</h4>
        </div>
        <div class="flex flex-col gap-3.5">
          <slot />
        </div>
      </div>
    </PopoverContent>
  </Popover>
</template>

<script setup lang="ts">
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { Button } from '@/components/ui/button'
</script>
