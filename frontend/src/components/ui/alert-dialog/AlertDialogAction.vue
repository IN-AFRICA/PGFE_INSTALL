<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue'
import { reactiveOmit } from '@vueuse/core'
import { AlertDialogAction, type AlertDialogActionProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { buttonVariants } from '@/components/ui/button'

const props = defineProps<
  AlertDialogActionProps & { class?: HTMLAttributes['class']; noClass?: boolean }
>()

const delegatedProps = reactiveOmit(props, 'class')

const className = computed(() => (props.noClass ? '' : `${cn(buttonVariants(), props.class)}`))
</script>

<template>
  <AlertDialogAction v-bind="delegatedProps" :class="className">
    <slot />
  </AlertDialogAction>
</template>
