<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { useAuthStore } from '@/stores/auth'
import { usePostApi } from '@/composables/usePostApi'
import { usePutApi } from '@/composables/usePutApi'
import { API_ROUTES } from '@/utils/constants/api_route'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const isEditing = ref(false)
const infrastructureId = ref<number | null>(null)
const loading = ref(false)

const pageTitle = computed(() => isEditing.value ? 'Modifier une Infrastructure' : 'Ajouter une Infrastructure')

const breadcrumb = computed(() => [
  { label: 'Infrastructure', href: '/infra' },
  { label: 'Opérations', href: '/infra/operations' },
  { label: 'Infrastructures', href: '/infra/operations/infrastructures' },
  { label: isEditing.value ? 'Modifier' : 'Nouveau', href: '#' },
])

// Data from API
const categories = ref<Array<{ id: number; name: string }>>([])
const bailleurs = ref<Array<{ id: number; name: string }>>([])

const formData = ref({
  name: '',
  date_construction: '',
  montant_construction: '',
  emplacement: '',
  infra_categorie_id: '',
  infra_bailleur_id: '',
  status: 'bon'
})

const { postData, loading: creating, success: createSuccess } = usePostApi()
const { putData, loading: updating, success: updateSuccess } = usePutApi()

const isSubmitting = computed(() => creating.value || updating.value)

// Load categories and bailleurs from API
const loadReferenceData = async () => {
  try {
    // Load categories
    const categoriesResponse = await fetch(
      'https://pgfe-back.inafrica.tech/api/v1/infrastructures/categories',
      {
        headers: {
          Authorization: `Bearer ${authStore.token}`,
          Accept: 'application/json',
        },
      }
    )
    if (categoriesResponse.ok) {
      const result = await categoriesResponse.json()
      categories.value = result.data || []
    }

    // Load bailleurs
    const bailleursResponse = await fetch(
      'https://pgfe-back.inafrica.tech/api/v1/infrastructures/bailleurs',
      {
        headers: {
          Authorization: `Bearer ${authStore.token}`,
          Accept: 'application/json',
        },
      }
    )
    if (bailleursResponse.ok) {
      const result = await bailleursResponse.json()
      bailleurs.value = result.data || []
    }
  } catch (error) {
    console.error('Erreur lors du chargement des données de référence:', error)
  }
}

onMounted(async () => {
  // Load reference data first
  await loadReferenceData()

  if (route.params.id) {
    isEditing.value = true
    infrastructureId.value = Number(route.params.id)

    try {
      loading.value = true
      const response = await fetch(
        `https://pgfe-back.inafrica.tech/api/v1/infrastructures/infrastructures/${infrastructureId.value}`,
        {
          headers: {
            Authorization: `Bearer ${authStore.token}`,
            Accept: 'application/json',
          },
        }
      )

      if (response.ok) {
        const result = await response.json()
        const data = result.data || result

        formData.value = {
          name: data.name || '',
          date_construction: data.date_construction || '',
          montant_construction: data.montant_construction || '',
          emplacement: data.emplacement || '',
          infra_categorie_id: data.infra_categorie_id ? String(data.infra_categorie_id) : '',
          infra_bailleur_id: data.infra_bailleur_id ? String(data.infra_bailleur_id) : '',
          status: data.status || 'bon'
        }
      } else {
        showCustomToast({ message: 'Erreur lors du chargement des données', type: 'error' })
        router.push('/infra/operations/infrastructures')
      }
    } catch (error) {
      showCustomToast({ message: 'Erreur lors du chargement des données', type: 'error' })
      router.push('/infra/operations/infrastructures')
    } finally {
      loading.value = false
    }
  }
})

const handleSubmit = async () => {
  // Validation
  if (!formData.value.name) {
    showCustomToast({ message: 'Le nom est requis', type: 'error' })
    return
  }
  if (!formData.value.date_construction) {
    showCustomToast({ message: 'La date de construction est requise', type: 'error' })
    return
  }
  if (!formData.value.montant_construction) {
    showCustomToast({ message: 'Le montant de construction est requis', type: 'error' })
    return
  }
  if (!formData.value.emplacement) {
    showCustomToast({ message: "L'emplacement est requis", type: 'error' })
    return
  }
  if (!formData.value.infra_categorie_id) {
    showCustomToast({ message: 'La catégorie est requise', type: 'error' })
    return
  }
  if (!formData.value.infra_bailleur_id) {
    showCustomToast({ message: 'Le bailleur est requis', type: 'error' })
    return
  }

  const payload = {
    ...formData.value,
    infra_categorie_id: Number(formData.value.infra_categorie_id),
    infra_bailleur_id: Number(formData.value.infra_bailleur_id),
    school_id: authStore.user?.school_id,
    montant_construction: Number(formData.value.montant_construction),
  }

  if (isEditing.value && infrastructureId.value) {
    await putData(API_ROUTES.UPDATE_INFRA_INFRASTRUCTURE(infrastructureId.value), payload)
    if (updateSuccess.value) {
      showCustomToast({ message: 'Infrastructure mise à jour avec succès', type: 'success' })
      router.push('/infra/operations/infrastructures')
    }
  } else {
    await postData(API_ROUTES.CREATE_INFRA_INFRASTRUCTURE, payload)
    if (createSuccess.value) {
      showCustomToast({ message: 'Infrastructure créée avec succès', type: 'success' })
      router.push('/infra/operations/infrastructures')
    }
  }
}

const handleCancel = () => {
  router.push('/infra/operations/infrastructures')
}
</script>

<template>
  <DashFormLayout
    :link-back="'/infra/operations/infrastructures'"
    :title="pageTitle"
    group-route="/infra/operations"
    module="infra"
    :breadcrumb="breadcrumb"
  >
    <div v-if="loading" class="flex items-center justify-center py-20">
      <IconifySpinner class="text-3xl" />
      <span class="ml-3 text-gray-600">Chargement...</span>
    </div>

    <form v-else @submit.prevent="handleSubmit" class="w-full flex flex-col space-y-8">
      <FormSection
        title="Informations générales"
        class="grid sm:grid-cols-2 gap-y-6 gap-x-10"
      >
        <InputWrapper>
          <Label for="name">Nom de l'infrastructure<SpanRequired /></Label>
          <Input
            id="name"
            v-model="formData.name"
            placeholder="Ex: Bâtiment A"
            required
            class="border-gray-200"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="date_construction">Date de construction<SpanRequired /></Label>
          <Input
            id="date_construction"
            v-model="formData.date_construction"
            type="date"
            required
            class="border-gray-200"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="montant_construction">Montant de construction ($)<SpanRequired /></Label>
          <Input
            id="montant_construction"
            v-model="formData.montant_construction"
            type="number"
            step="0.01"
            placeholder="Ex: 50000"
            required
            class="border-gray-200"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="status">Statut</Label>
          <Select v-model="formData.status">
            <SelectTrigger class="border-gray-200">
              <SelectValue placeholder="Sélectionner un statut" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="bon">Bon</SelectItem>
              <SelectItem value="moyen">Moyen</SelectItem>
              <SelectItem value="mauvais">Mauvais</SelectItem>
              <SelectItem value="en_renovation">En rénovation</SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>
      </FormSection>

      <FormSection
        title="Classification"
        class="grid sm:grid-cols-2 gap-y-6 gap-x-10"
      >
        <InputWrapper>
          <Label for="infra_categorie_id">Catégorie<SpanRequired /></Label>
          <Select v-model="formData.infra_categorie_id" required>
            <SelectTrigger class="border-gray-200">
              <SelectValue placeholder="Sélectionner une catégorie" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="categorie in categories"
                :key="categorie.id"
                :value="String(categorie.id)"
              >
                {{ categorie.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label for="infra_bailleur_id">Bailleur<SpanRequired /></Label>
          <Select v-model="formData.infra_bailleur_id" required>
            <SelectTrigger class="border-gray-200">
              <SelectValue placeholder="Sélectionner un bailleur" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="bailleur in bailleurs"
                :key="bailleur.id"
                :value="String(bailleur.id)"
              >
                {{ bailleur.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>
      </FormSection>

      <FormSection
        title="Localisation"
        class="grid gap-y-6"
      >
        <InputWrapper>
          <Label for="emplacement">Emplacement<SpanRequired /></Label>
          <Textarea
            id="emplacement"
            v-model="formData.emplacement"
            placeholder="Ex: Aile Nord, Étage 2, Campus principal"
            required
            class="border-gray-200 min-h-[100px]"
          />
        </InputWrapper>
      </FormSection>

      <div class="flex justify-end gap-3">
        <Button type="button" variant="outline" @click="handleCancel" :disabled="isSubmitting">
          Annuler
        </Button>
        <Button type="submit" :disabled="isSubmitting">
          <IconifySpinner v-if="isSubmitting" class="mr-2" />
          <span v-if="isSubmitting">Enregistrement...</span>
          <span v-else>Enregistrer</span>
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
