<script lang="ts" setup>
import type { BreadcrumbProps } from '@/types'

const props = withDefaults(defineProps<BreadcrumbProps>(), {
  homeIcon: 'hugeicons--home-01',
  separatorIcon: 'hugeicons--arrow-left-01',
})
</script>
<template>
  <nav
    class="flex text-foreground text-sm border border-border rounded-lg bg-background-soft px-2 py-1.5"
    aria-label="Breadcrumb"
  >
    <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
      <li
        v-for="(item, index) in items"
        :key="index"
        class="inline-flex items-center"
        :aria-current="item.isActive ? 'page' : undefined"
      >
        <!-- First item (home) -->
        <template v-if="index === 0">
          <component
            :is="item.href ? 'a' : 'span'"
            :href="item.href"
            class="inline-flex items-center text-sm font-medium text-foreground hover:text-primary"
          >
            <span
              v-if="item.icon || homeIcon"
              aria-hidden="true"
              :class="`flex iconify ${item.icon || homeIcon} mr-2.5`"
            ></span>
            {{ item.label }}
          </component>
        </template>

        <!-- Subsequent items -->
        <template v-else>
          <div class="flex items-center">
            <span
              aria-hidden="true"
              :class="`iconify ${separatorIcon} flex mx-1 text-gray-400`"
            ></span>
            <component
              :is="item.href && !item.isActive ? 'a' : 'span'"
              :href="item.href && !item.isActive ? item.href : undefined"
              :class="
                item.isActive
                  ? 'ms-1 text-sm font-medium text-gray-500 md:ms-2'
                  : 'ms-1 text-sm font-medium text-foreground hover:text-primary'
              "
            >
              {{ item.label }}
            </component>
          </div>
        </template>
      </li>
    </ol>
  </nav>
</template>
