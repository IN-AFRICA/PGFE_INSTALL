import { nextTick, ref, type Ref } from 'vue'

export function useSyncAnimation() {
  const isSyncing = ref(false)
  const showOverlay = ref(false)

  let orbitAnimations: Animation[] = []
  let currentSessionId = 0

  function startAnimation(
    syncCardEl: HTMLElement,
    allCardEls: HTMLElement[],
    orbitContainerRef: Ref<HTMLElement | null>,
  ) {
    if (isSyncing.value) return

    currentSessionId++
    const sessionId = currentSessionId

    isSyncing.value = true
    orbitAnimations = []

    const centerX = window.innerWidth / 2
    const centerY = window.innerHeight / 2

    const sRect = syncCardEl.getBoundingClientRect()
    const sTx = centerX - (sRect.left + sRect.width / 2)
    const sTy = centerY - (sRect.top + sRect.height / 2)

    syncCardEl.style.zIndex = '100'
    syncCardEl.style.transition = 'transform 0.6s cubic-bezier(0.4, 0, 0.2, 1)'
    syncCardEl.style.transform = `translate(${sTx}px, ${sTy}px) scale(1.1)`
    syncCardEl.classList.add('sync-active')

    allCardEls.forEach((card) => {
      const rect = card.getBoundingClientRect()
      const tx = centerX - (rect.left + rect.width / 2)
      const ty = centerY - (rect.top + card.offsetHeight / 2)
      card.style.transition = 'transform 0.6s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s ease'
      card.style.transform = `translate(${tx}px, ${ty}px) scale(0)`
      card.style.opacity = '0'
    })

    setTimeout(async () => {
      if (sessionId !== currentSessionId) return

      showOverlay.value = true

      await nextTick()

      if (sessionId !== currentSessionId) return

      const orbitContainerEl = orbitContainerRef.value
      if (!orbitContainerEl) return

      const count = allCardEls.length
      const screenMin = Math.min(window.innerWidth, window.innerHeight)

      const maxRadius = screenMin / 2 - 80
      const radius = Math.min(160 + count * 8, maxRadius)

      const CLONE_MAX_PX = Math.min(80, screenMin * 0.1)
      const CLONE_MIN_PX = 48
      const cloneSizePx = Math.max(CLONE_MIN_PX, Math.round(CLONE_MAX_PX - (count - 3) * 4))
      const iconSizePx = Math.round(cloneSizePx * 0.5)

      allCardEls.forEach((card, index) => {
        const iconEl = card.querySelector('[class*="iconify"]')
        const iconClass = iconEl?.className ?? ''

        const clone = document.createElement('div')
        clone.style.cssText = `
          position: absolute;
          background: white;
          border-radius: 0.5rem;
          box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15);
          width: ${cloneSizePx}px;
          height: ${cloneSizePx}px;
          display: flex;
          align-items: center;
          justify-content: center;
        `

        const iconSpan = document.createElement('span')
        iconSpan.className = iconClass
        iconSpan.style.fontSize = `${iconSizePx}px`
        iconSpan.setAttribute('aria-hidden', 'true')

        clone.appendChild(iconSpan)

        const startAngle = (360 / allCardEls.length) * index
        clone.style.transform = `rotate(${startAngle}deg) translateX(0px) scale(0)`
        orbitContainerEl.appendChild(clone)

        setTimeout(() => {
          if (sessionId !== currentSessionId) return

          clone.style.transition = 'transform 0.8s cubic-bezier(0.17, 0.67, 0.41, 1.18)'
          clone.style.transform = `rotate(${startAngle}deg) translateX(${radius}px) rotate(${-startAngle}deg) scale(1)`

          setTimeout(() => {
            if (sessionId !== currentSessionId) return

            clone.style.transition = 'none'
            const anim = clone.animate(
              [
                {
                  transform: `rotate(${startAngle}deg) translateX(${radius}px) rotate(${-startAngle}deg) scale(1)`,
                },
                {
                  transform: `rotate(${startAngle + 360}deg) translateX(${radius}px) rotate(${-startAngle - 360}deg) scale(1)`,
                },
              ],
              { duration: 12000, iterations: Infinity, easing: 'linear' },
            )
            orbitAnimations.push(anim)
          }, 800)
        }, index * 50)
      })
    }, 700)
  }

  function endAnimation(
    syncCardEl: HTMLElement,
    allCardEls: HTMLElement[],
    orbitContainerEl: HTMLElement,
  ) {
    currentSessionId++

    return new Promise<void>((resolve) => {
      orbitAnimations.forEach((a) => a.cancel())
      orbitAnimations = []

      showOverlay.value = false

      setTimeout(() => {
        orbitContainerEl.innerHTML = ''

        syncCardEl.style.transform = 'translate(0, 0) scale(1)'
        syncCardEl.classList.remove('sync-active')

        allCardEls.forEach((card) => {
          card.style.transform = 'translate(0, 0) scale(1)'
          card.style.opacity = '1'
        })

        setTimeout(() => {
          syncCardEl.style.zIndex = '10'
          isSyncing.value = false
          resolve()
        }, 600)
      }, 500)
    })
  }

  return {
    isSyncing,
    showOverlay,
    startAnimation,
    endAnimation,
  }
}
