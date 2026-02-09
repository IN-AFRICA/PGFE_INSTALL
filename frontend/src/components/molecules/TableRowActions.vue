<script setup lang="ts">
/**
 * TableRowActions
 *
 * Ce composant gère l'affichage des actions au survol d'une ligne de tableau
 * sans provoquer de décalage de mise en page (layout shift).
 *
 * Logique :
 * 1. Les actions (slot 'actions') sont présentes dans le DOM mais invisibles (invisible) par défaut.
 *    Elles occupent donc leur espace, ce qui fixe la largeur de la colonne.
 * 2. Un bouton déclencheur (slot 'trigger', par défaut 3 points) est positionné en absolu par-dessus.
 * 3. Au survol du conteneur (.group) :
 *    - Les actions deviennent visibles (visible).
 *    - Le déclencheur devient invisible (invisible).
 *
 * Utilisation :
 * <TableRowActions>
 *   <template #actions>
 *     <Button @click="edit">Modifier</Button>
 *     <Button @click="delete">Supprimer</Button>
 *   </template>
 * </TableRowActions>
 */
</script>

<template>
  <div class="group flex items-center justify-end relative">
    <!-- 
      Conteneur des actions : 
      - invisible par défaut (garde l'espace)
      - visible au survol du parent (.group)
    -->
    <div class="flex items-center gap-2 invisible group-hover:visible">
      <slot name="actions"></slot>
    </div>

    <!-- 
      Bouton déclencheur (ex: 3 points) :
      - positionné en absolu à droite pour se superposer à l'espace réservé
      - invisible au survol du parent (.group) pour laisser voir les actions
    -->
    <div class="absolute right-0 group-hover:invisible flex items-center justify-center">
      <slot name="trigger">
        <button
          class="rounded-full size-8 flex items-center justify-center hover:bg-gray-100 transition"
        >
          <span class="iconify hugeicons--more-vertical-circle-01"></span>
        </button>
      </slot>
    </div>
  </div>
</template>
