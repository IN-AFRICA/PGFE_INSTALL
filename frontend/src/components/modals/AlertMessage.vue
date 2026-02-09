<script setup lang="ts">
import {
  Dialog,
  DialogTrigger,
  DialogHeader,
  DialogContent,
  DialogTitle,
  DialogDescription,
  DialogFooter,
  DialogClose,
} from '../ui/dialog'

import { Button } from './../ui/button'

const props = defineProps<{
  title: string
  message: string
  action?: 'warning' | 'success' | 'danger' | 'default'
}>()

const actions = {
  warning: {
    icon: 'hugeicons--warning',
    class: 'bg-orange-100/50 dark:bg-orange-900/50 text-orange-600',
  },
  danger: {
    icon: 'hugeicons--delete-02',
    class: 'bg-red-100/50 dark:bg-red-900/50 text-red-600',
  },
  success: {
    icon: 'hugeicons--success-01',
    class: 'bg-emerald-100/50 dark:bg-emerald-900/50 text-emerald-600',
  },
  default: {
    icon: '',
    class: 'bg-primary-100/50 dark:bg-primary-900/50 text-primary-600',
  },
}

const selectedAction = actions[props.action ?? 'default']
</script>
<template>
  <Dialog>
    <DialogTrigger as-child>
      <slot name="trigger" />
    </DialogTrigger>
    <DialogContent class="flex flex-col items-center text-center sm:max-w-[400px]">
      <div
        :class="[
          'mx-auto size-12 flex items-center justify-center rounded-full',
          selectedAction.class,
        ]"
      >
        <span :class="['flex iconify text-xl', selectedAction.icon]" />
      </div>
      <DialogHeader>
        <DialogTitle class="text-center">
          {{ title }}
        </DialogTitle>
        <DialogDescription class="text-center">
          {{ message }}
        </DialogDescription>
      </DialogHeader>
      <DialogFooter class="flex justify-center mt-4 items-center gap-2">
        <DialogClose as-child>
          <Button type="reset" size="sm" variant="outline" class="h-10"> Non, Annuler </Button>
        </DialogClose>
        <slot name="confirm-action-button" />
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
