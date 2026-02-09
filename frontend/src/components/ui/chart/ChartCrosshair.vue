<script setup lang="ts">
import type { BulletLegendItemInterface } from '@unovis/ts'
import { omit } from '@unovis/ts'
import { VisCrosshair, VisTooltip } from '@unovis/vue'
import { type Component, createApp } from 'vue'
import { ChartTooltip } from '.'

const props = withDefaults(
  defineProps<{
    colors: string[]
    index: string
    items: BulletLegendItemInterface[]
    customTooltip?: Component
  }>(),
  {
    colors: () => [],
  },
)

// Use weakmap to store reference to each datapoint for Tooltip
const wm = new WeakMap()
function template(d: any) {
  if (wm.has(d)) {
    return wm.get(d)
  } else {
    const componentDiv = document.createElement('div')
    const tooltipData = props.items
      .map((item) => ({
        ...item,
        value: d[item.name],
      }))
      .filter((item) => item.value !== undefined)

    const TooltipComponent = props.customTooltip ?? ChartTooltip
    const title = d[props.index] || ''
    const total = d.total || null
    createApp(TooltipComponent, { title, data: tooltipData, total }).mount(componentDiv)
    wm.set(d, componentDiv.innerHTML)
    return componentDiv.innerHTML
  }
}

function color(d: unknown, i: number) {
  return props.colors[i] ?? 'transparent'
}
</script>

<template>
  <VisTooltip :horizontal-shift="20" :vertical-shift="20" />
  <VisCrosshair :template="template" :color="color" />
</template>
