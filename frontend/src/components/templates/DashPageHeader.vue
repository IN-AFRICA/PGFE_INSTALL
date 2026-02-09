<script setup lang="ts">
import { cn } from '@/lib/utils'

defineProps<{
  title: string
  tags: {
    name: string
    text: string
    href: string
    activated?: boolean
  }[]
  activeTagName: string
  breadcrumb?: {
    label: string
    href: string
  }[]
}>()
</script>

<template>
  <div class="flex flex-col mt-4">
    <!-- Breadcrumb -->
    <nav
      v-if="breadcrumb && breadcrumb.length"
      class="flex text-foreground text-sm border border-border rounded-lg bg-background-soft px-2 py-1.5 mb-3"
      aria-label="Breadcrumb"
    >
      <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
        <li v-for="(item, index) in breadcrumb" :key="index" class="inline-flex items-center">
          <!-- First item (home) -->
          <template v-if="index === 0">
            <RouterLink
              :to="item.href"
              class="inline-flex items-center text-sm font-medium text-foreground hover:text-primary"
            >
              <span class="flex iconify hugeicons--home-01 mr-2.5" aria-hidden="true"></span>
              {{ item.label }}
            </RouterLink>
          </template>

          <!-- Subsequent items -->
          <template v-else>
            <div class="flex items-center">
              <span
                class="iconify hugeicons--arrow-left-01 flex mx-1 text-gray-400"
                aria-hidden="true"
              ></span>
              <RouterLink
                v-if="index < breadcrumb.length - 1"
                :to="item.href"
                class="ms-1 text-sm font-medium text-foreground hover:text-primary"
              >
                {{ item.label }}
              </RouterLink>
              <span v-else class="ms-1 text-sm font-medium text-gray-500 md:ms-2">{{
                item.label
              }}</span>
            </div>
          </template>
        </li>
      </ol>
    </nav>

    <h1 class="font-semibold text-foreground-title">
      {{ title }}
    </h1>
    <ul class="flex items-center flex-wrap gap-y-3 gap-x-2 mt-4">
      <li v-for="item in tags" :key="item.name">
        <RouterLink
          :to="item.activated === false ? '#' : item.href"
          :class="
            cn('rounded-md px-3 py-1.5', 
              item.activated === false ? 'bg-gray-100 text-gray-400 cursor-not-allowed pointer-events-none select-none'
              : activeTagName === item.name ? 'bg-primary text-white' 
              : 'bg-gray-200 text-foreground-muted hover:bg-gray-300'
            )
          "
        >
          {{ item.text }}
        </RouterLink>
      </li>
    </ul>
  </div>
</template>




// ligne modifié 

/*
10 
activated?: boolean


70 - 76 
        RouterLink
          :to="item.href"
          :class="
            cn('rounded-md px-3 py-1.5 bg-gray-200 text-foreground-muted', {
              'bg-primary text-white': activeTagName === item.name,
              'hover:bg-gray-300': activeTagName !== item.name,
            })
          "
        >
          {{ item.text }}
        /RouterLink
        
        */