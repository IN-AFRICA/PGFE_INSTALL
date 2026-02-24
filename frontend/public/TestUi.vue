<script setup>
import { ref, onMounted } from 'vue';
// Importation des icônes via CDN pour le Playground
import {
  Users, Box, ShoppingCart, Settings,
  Mail, BarChart, Shield, Database,
  FileText, RefreshCw
} from 'https://esm.sh/lucide-vue-next';

const cards = [
  { title: 'Utilisateurs', icon: Users },
  { title: 'Produits', icon: Box },
  { title: 'Commandes', icon: ShoppingCart },
  { title: 'Paramètres', icon: Settings },
  { title: 'Messages', icon: Mail },
  { title: 'Stats', icon: BarChart },
  { title: 'Sécurité', icon: Shield },
  { title: 'Base', icon: Database },
  { title: 'Rapports', icon: FileText },
];

const isAnimating = ref(false);
const showOverlay = ref(false);
const overlayOpacity = ref(0);
const progress = ref(0);
const timeLeft = ref(0);
const rotationAngle = ref(0);

const cardRefs = ref([]);
const syncCardRef = ref(null);

const DURATION = 8000;

const startSync = () => {
  if (isAnimating.value) return;
  isAnimating.value = true;

  const centerX = window.innerWidth / 2;
  const centerY = window.innerHeight / 2;

  const sRect = syncCardRef.value.getBoundingClientRect();
  const sTx = centerX - (sRect.left + sRect.width / 2);
  const sTy = centerY - (sRect.top + sRect.height / 2);

  syncCardRef.value.style.zIndex = "100";
  syncCardRef.value.style.transform = `translate(${sTx}px, ${sTy}px) scale(1.1)`;

  cardRefs.value.forEach(card => {
    if (!card) return;
    const rect = card.getBoundingClientRect();
    const tx = centerX - (rect.left + rect.width / 2);
    const ty = centerY - (rect.top + rect.height / 2);
    card.style.transform = `translate(${tx}px, ${ty}px) scale(0)`;
    card.style.opacity = "0";
  });

  setTimeout(() => {
    showOverlay.value = true;
    setTimeout(() => overlayOpacity.value = 1, 10);
    startCounters();
    startOrbitalRotation();
  }, 600);
};

const startCounters = () => {
  const start = Date.now();
  const timer = setInterval(() => {
    const elapsed = Date.now() - start;
    progress.value = Math.min(Math.floor((elapsed / DURATION) * 100), 100);
    timeLeft.value = Math.max(Math.ceil((DURATION - elapsed) / 1000), 0);
    if (progress.value >= 100) {
      clearInterval(timer);
      resetSync();
    }
  }, 50);
};

const startOrbitalRotation = () => {
  let start = null;
  const step = (timestamp) => {
    if (!start) start = timestamp;
    if (!isAnimating.value) return;
    rotationAngle.value = (timestamp - start) / 20;
    requestAnimationFrame(step);
  };
  requestAnimationFrame(step);
};

const getOrbitStyle = (index) => {
  const total = cards.length;
  const radius = Math.min(200 + (total * 5), (window.innerWidth / 2) - 80);
  const baseAngle = (360 / total) * index;
  const currentAngle = baseAngle + rotationAngle.value;
  const scale = overlayOpacity.value === 1 ? 1 : 0;

  return {
    transform: `rotate(${currentAngle}deg) translateX(${radius}px) rotate(${-currentAngle}deg) scale(${scale})`,
    transition: scale === 0 ? 'none' : 'transform 0.1s linear, scale 0.8s cubic-bezier(0.17, 0.67, 0.41, 1.18)'
  };
};

const resetSync = () => {
  setTimeout(() => {
    overlayOpacity.value = 0;
    setTimeout(() => {
      showOverlay.value = false;
      isAnimating.value = false;
      syncCardRef.value.style.transform = "translate(0, 0) scale(1)";
      syncCardRef.value.style.zIndex = "10";
      cardRefs.value.forEach(card => {
        if (card) {
          card.style.transform = "translate(0, 0) scale(1)";
          card.style.opacity = "1";
        }
      });
    }, 500);
  }, 600);
};
</script>

<template>
  <div class="bg-slate-100 min-h-screen flex items-center justify-center overflow-hidden py-12 font-sans" :class="{ 'pointer-events-none': isAnimating }">

    <div class="w-full max-w-6xl px-6">
      <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">

        <div
          v-for="(card, index) in cards"
          :key="index"
          :ref="el => cardRefs[index] = el"
          class="card group bg-white rounded-2xl shadow-sm p-8 text-center transition-all duration-500 hover:shadow-xl cursor-pointer relative z-10"
        >
          <component :is="card.icon" class="w-12 h-12 mx-auto mb-4 text-slate-600 group-hover:text-indigo-600 transition-colors" />
          <h3 class="font-medium text-slate-700">{{ card.title }}</h3>
        </div>

        <div
          ref="syncCardRef"
          @click="startSync"
          class="group bg-white rounded-2xl shadow-sm p-8 text-center transition-all duration-500 hover:shadow-xl cursor-pointer relative z-10 flex flex-col items-center justify-center min-h-[160px]"
          :class="{ 'sync-active': isAnimating }"
        >
          <div v-if="!isAnimating">
            <RefreshCw class="w-12 h-12 mx-auto mb-4 text-slate-600 group-hover:text-indigo-600 transition-colors" />
            <h3 class="font-medium text-slate-700">Synchroniser</h3>
          </div>

          <div v-else class="text-center">
            <span class="text-4xl font-black text-indigo-600 block">{{ progress }}%</span>
            <span class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">RESTE : {{ timeLeft }}S</span>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showOverlay" class="fixed inset-0 bg-slate-900/85 backdrop-blur-xl z-40 flex items-center justify-center transition-opacity duration-500" :style="{ opacity: overlayOpacity }">
      <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
        <div
          v-for="(card, index) in cards"
          :key="'clone-' + index"
          class="absolute bg-white rounded-2xl shadow-2xl p-4 w-28 h-28 flex flex-col items-center justify-center text-center"
          :style="getOrbitStyle(index)"
        >
          <component :is="card.icon" class="w-8 h-8 mb-2 text-indigo-600" />
          <span class="text-[10px] font-bold text-slate-500 uppercase">{{ card.title }}</span>
        </div>
      </div>
    </div>

  </div>
</template>

<style>
/* Import Tailwind via CDN pour le Playground */
@import url('https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css');

.sync-active {
  animation: pulse-light 2s infinite;
}
@keyframes pulse-light {
  0%, 100% { box-shadow: 0 0 20px rgba(79, 70, 229, 0.4); }
  50% { box-shadow: 0 0 40px rgba(79, 70, 229, 0.7); }
}
</style>
