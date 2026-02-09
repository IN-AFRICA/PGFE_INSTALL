<script setup lang="ts">
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import DashFormLayout from '@/components/templates/DashStudentFormLayout.vue'
import { Button } from '@/components/ui/button'
// import CustomDatePicker from '@/components/ui/CustomDatePicker.vue';
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import Spinner from '@/components/ui/spinner/Spinner.vue'
import { usePostApi } from '@/composables/usePostApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { eventBus } from '@/utils/eventBus'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import ListAcademicalLevel from '@/utils/widgets/vues/ListAcademicalLevel.vue'
// import ListClassRoom from '@/utils/widgets/vues/ListClassRoom.vue';
import ListCommune from '@/utils/widgets/vues/ListCommune.vue'
import ListCountrie from '@/utils/widgets/vues/ListCountrie.vue'
import ListFather from '@/utils/widgets/vues/ListFather.vue'
import ListFonctions from '@/utils/widgets/vues/ListFonctions.vue'
import ListMother from '@/utils/widgets/vues/ListMother.vue'
// import ListPersonnalAcademic from '@/utils/widgets/vues/ListPersonnalAcademic.vue';
import ListProvince from '@/utils/widgets/vues/ListProvince.vue'
import ListSchool from '@/utils/widgets/vues/ListSchool.vue'
import ListTerritory from '@/utils/widgets/vues/ListTerritory.vue'
import ListType from '@/utils/widgets/vues/ListType.vue'
import { toTypedSchema } from '@vee-validate/zod'
import { useField, useForm } from 'vee-validate'
import { useRouter } from 'vue-router'
import z from 'zod'

const router = useRouter()

const schemaForm = z.object({
  country_id: z
    .number({ required_error: 'Veuillez sélectionner le pays' })
    .min(1, 'Veuillez sélectionner le pays'),
  province_id: z
    .number({ required_error: 'Veuillez sélectionner la province' })
    .min(1, 'Veuillez sélectionner la province'),
  territory_id: z
    .number({ required_error: 'Veuillez sélectionner le territoire' })
    .min(1, 'Veuillez sélectionner le territoire'),
  commune_id: z
    .number({ required_error: 'Veuillez sélectionner la commune' })
    .min(1, 'Veuillez sélectionner la commune'),
  school_id: z
    .number({ required_error: "Veuillez sélectionner l'école" })
    .min(1, "Veuillez sélectionner l'école"),
  type_id: z
    .number({ required_error: 'Veuillez sélectionner le type' })
    .min(1, 'Veuillez sélectionner le type'),
  father_id: z
    .number({ required_error: 'Veuillez sélectionner le père' })
    .min(1, 'Veuillez sélectionner le père'),
  mother_id: z
    .number({ required_error: 'Veuillez sélectionner la mère' })
    .min(1, 'Veuillez sélectionner la mère'),
  academic_level_id: z
    .number({ required_error: 'Veuillez sélectionner le niveau scolaire' })
    .min(1, 'Veuillez sélectionner le niveau scolaire'),
  fonction_id: z
    .number({ required_error: 'Veuillez sélectionner la fonction' })
    .min(1, 'Veuillez sélectionner la fonction'),
  matricule: z.string({ required_error: 'Veuillez saisir le matricule' }).min(2).max(100),
  name: z.string({ required_error: 'Veuillez saisir le nom' }).min(2).max(100),
  firstname: z.string({ required_error: 'Veuillez saisir le prénom' }).min(2).max(100),
  lastname: z.string({ required_error: 'Veuillez saisir le post nom' }).min(2).max(100),
  phone_number: z
    .string({ required_error: 'Veuillez saisir le numéro de téléphone' })
    .min(2)
    .max(30),
  email: z.string({ required_error: "Veuillez saisir l'email" }).email('Email invalide'),
  identity_card: z
    .string({ required_error: "Veuillez saisir le numéro de carte d'identité" })
    .min(2)
    .max(100),
  gender: z
    .string({ required_error: 'Veuillez sélectionner le genre' })
    .min(1, 'Veuillez sélectionner le genre'),
  civil_status: z
    .string({ required_error: "Veuillez sélectionner l'état civil" })
    .min(1, "Veuillez sélectionner l'état civil"),
  birth_date: z
    .string({ required_error: 'Veuillez saisir la date de naissance' })
    .min(1, 'Veuillez saisir la date de naissance'),
  birth_place: z.string({ required_error: 'Veuillez saisir le lieu de naissance' }).min(2).max(100),
  address: z.string({ required_error: "Veuillez saisir l'adresse" }).min(2).max(255),
})

//Validation
const { handleSubmit, resetForm } = useForm({
  validationSchema: toTypedSchema(schemaForm),
})

const { value: country_id, errorMessage: countryIdError } = useField<string>(
  'country_id',
  undefined,
  { initialValue: '' },
)
const { value: province_id, errorMessage: provinceIdError } = useField<string>(
  'province_id',
  undefined,
  { initialValue: '' },
)
const { value: territory_id, errorMessage: territoryIdError } = useField<string>(
  'territory_id',
  undefined,
  { initialValue: '' },
)
const { value: commune_id, errorMessage: communeIdError } = useField<string>(
  'commune_id',
  undefined,
  { initialValue: '' },
)
const { value: school_id, errorMessage: schoolIdError } = useField<string>('school_id', undefined, {
  initialValue: '',
})
const { value: type_id, errorMessage: typeIdError } = useField<string>('type_id', undefined, {
  initialValue: '',
})
const { value: father_id, errorMessage: fatherIdError } = useField<string>('father_id', undefined, {
  initialValue: '',
})
const { value: mother_id, errorMessage: motherIdError } = useField<string>('mother_id', undefined, {
  initialValue: '',
})
const { value: academic_level_id, errorMessage: academicLevelIdError } = useField<string>(
  'academic_level_id',
  undefined,
  { initialValue: '' },
)
const { value: fonction_id, errorMessage: fonctionIdError } = useField<string>(
  'fonction_id',
  undefined,
  { initialValue: '' },
)

const { value: matricule, errorMessage: matriculeError } = useField<string>(
  'matricule',
  undefined,
  { initialValue: '' },
)
const { value: name, errorMessage: nameError } = useField<string>('name', undefined, {
  initialValue: '',
})
const { value: firstname, errorMessage: firstnameError } = useField<string>(
  'firstname',
  undefined,
  { initialValue: '' },
)
const { value: lastname, errorMessage: lastnameError } = useField<string>('lastname', undefined, {
  initialValue: '',
})
const { value: phone_number, errorMessage: phoneNumberError } = useField<string>(
  'phone_number',
  undefined,
  { initialValue: '' },
)
const { value: email, errorMessage: emailError } = useField<string>('email', undefined, {
  initialValue: '',
})
const { value: identity_card, errorMessage: identityCardError } = useField<string>(
  'identity_card',
  undefined,
  { initialValue: '' },
)
const { value: gender, errorMessage: genderError } = useField<string>('gender', undefined, {
  initialValue: '',
})
const { value: civil_status, errorMessage: civilStatusError } = useField<string>(
  'civil_status',
  undefined,
  { initialValue: '' },
)
const { value: birth_date, errorMessage: birthDateError } = useField<string>(
  'birth_date',
  undefined,
  { initialValue: '' },
)
const { value: birth_place, errorMessage: birthPlaceError } = useField<string>(
  'birth_place',
  undefined,
  { initialValue: '' },
)
const { value: address, errorMessage: addressError } = useField<string>('address', undefined, {
  initialValue: '',
})

const { loading, error, response, postData, success } = usePostApi()

const onSubmit = handleSubmit(async (values) => {
  await postData(API_ROUTES.CREATE_PERSONNAL_ACADEMIC, values)
  eventBus.emit('studentUpdated')
  if (error.value) {
    showCustomToast({
      message: error.value,
      type: 'error',
    })
    return
  } else if (success.value) {
    showCustomToast({
      message: response.value?.message || 'Personnel scolaire ajouté avec succès',
      type: 'success',
    })
    resetForm()
    router.push('/apprenants/operations')
  }
})
</script>
<template>
  <DashFormLayout
    title="Ajouter un nouveau personnel scolaire"
    link-back="apprenants/saisie-prealable/academic-personnal"
    current-mode="formel"
    group="operations"
    active-tag-name="inscription"
  >
    <form class="w-full flex flex-col space-y-8" @submit.prevent="onSubmit">
      <FormSection
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
        title="Informations personnelles"
      >
        <InputWrapper>
          <Label class="text-sm">
            Matricule
            <SpanRequired />
          </Label>
          <Input
            class="bg-white transition-all h-10 rounded-md"
            placeholder="Isaac"
            v-model="matricule"
          />
          <span v-if="matriculeError" class="text-xs text-red-500">{{ matriculeError }}</span>
        </InputWrapper>
        <InputWrapper>
          <Label class="text-sm">
            Nom
            <SpanRequired />
          </Label>
          <Input
            class="bg-white transition-all h-10 rounded-md"
            placeholder="Kasongo"
            v-model="name"
          />
          <span v-if="nameError" class="text-xs text-red-500">{{ nameError }}</span>
        </InputWrapper>
        <InputWrapper>
          <Label class="text-sm">
            Post nom
            <SpanRequired />
          </Label>
          <Input
            class="bg-white transition-all h-10 rounded-md"
            placeholder="Muleka"
            v-model="lastname"
          />
          <span v-if="lastnameError" class="text-xs text-red-500">{{ lastnameError }}</span>
        </InputWrapper>
        <InputWrapper>
          <Label class="text-sm">
            Prenom
            <SpanRequired />
          </Label>
          <Input
            class="bg-white transition-all h-10 rounded-md"
            placeholder="Isaac"
            v-model="firstname"
          />
          <span v-if="firstnameError" class="text-xs text-red-500">{{ firstnameError }}</span>
        </InputWrapper>
        <InputWrapper>
          <Label class="text-sm">
            Genre
            <SpanRequired />
          </Label>
          <Select v-model="gender">
            <SelectTrigger id="genre" class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionner le genre" />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem value="Masculin">Masculin</SelectItem>
                <SelectItem value="Féminin">Feminin</SelectItem>
                <SelectItem value="Non spécifié">Non spécifié</SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
          <span v-if="genderError" class="text-xs text-red-500">{{ genderError }}</span>
        </InputWrapper>
        <InputWrapper>
          <Label class="text-sm">
            Etat civil
            <SpanRequired />
          </Label>
          <Select v-model="civil_status">
            <SelectTrigger id="etat-civil" class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionner l'état civil" />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem value="Célibataire">Célibataire</SelectItem>
                <SelectItem value="'Marié(e)">Marié(e)</SelectItem>
                <SelectItem value="Divorcé(e)">Divorcé(e)</SelectItem>
                <SelectItem value="Veuf/Veuve">Veuf(ve)</SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
          <span v-if="civilStatusError" class="text-xs text-red-500">{{ civilStatusError }}</span>
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm">
            Date de naissance
            <SpanRequired />
          </Label>
          <Input
            type="date"
            class="bg-white transition-all h-10 rounded-md"
            placeholder="Muleka"
            v-model="birth_date"
          />
          <span v-if="birthDateError" class="text-xs text-red-500">{{ birthDateError }}</span>
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm">
            Lieu de naissance
            <SpanRequired />
          </Label>
          <Input class="bg-white transition-all h-10 rounded-md" v-model="birth_place" />
          <span v-if="birthPlaceError" class="text-xs text-red-500">{{ birthPlaceError }}</span>
        </InputWrapper>
        <InputWrapper :error="fatherIdError">
          <ListFather v-model="father_id" />
          <span v-if="fatherIdError" class="text-xs text-red-500">{{ fatherIdError }}</span>
        </InputWrapper>
        <InputWrapper :error="motherIdError">
          <ListMother v-model="mother_id" />
          <span v-if="motherIdError" class="text-xs text-red-500">{{ motherIdError }}</span>
        </InputWrapper>
        <InputWrapper>
          <Label class="text-sm">
            No carte identité
            <SpanRequired />
          </Label>
          <Input class="bg-white transition-all h-10 rounded-md" v-model="identity_card" />
          <span v-if="identityCardError" class="text-xs text-red-500">{{ identityCardError }}</span>
        </InputWrapper>
        <InputWrapper>
          <Label class="text-sm">
            Téléphone
            <SpanRequired />
          </Label>
          <Input class="bg-white transition-all h-10 rounded-m d" v-model="phone_number" />
          <span v-if="phoneNumberError" class="text-xs text-red-500">{{ phoneNumberError }}</span>
        </InputWrapper>
        <InputWrapper>
          <Label class="text-sm">
            Adresse email
            <SpanRequired />
          </Label>
          <Input class="bg-white transition-all h-10 rounded-md" v-model="email" />
          <span v-if="emailError" class="text-xs text-red-500">{{ emailError }}</span>
        </InputWrapper>
        <!-- Nationnalité actuelle: country_id -->
        <InputWrapper>
          <ListCountrie v-model="country_id" />
          <span v-if="countryIdError" class="text-xs text-red-500">{{ countryIdError }}</span>
        </InputWrapper>
        <!-- Province: province_id -->
        <InputWrapper>
          <ListProvince v-model="province_id" />
          <span v-if="provinceIdError" class="text-xs text-red-500">{{ provinceIdError }}</span>
        </InputWrapper>
        <!-- Ville/ territoire: territory_id -->
        <InputWrapper>
          <ListTerritory v-model="territory_id" />
          <span v-if="territoryIdError" class="text-xs text-red-500">{{ territoryIdError }}</span>
        </InputWrapper>
        <!-- Commune: commune_id -->
        <InputWrapper :error="communeIdError">
          <ListCommune v-model="commune_id" />
          <span v-if="communeIdError" class="text-xs text-red-500">{{ communeIdError }}</span>
        </InputWrapper>
        <InputWrapper>
          <Label class="text-sm">
            Adresse
            <SpanRequired />
          </Label>
          <Input class="bg-white transition-all h-10 rounded-md" v-model="address" />
          <span v-if="addressError" class="text-xs text-red-500">{{ addressError }}</span>
        </InputWrapper>
        <!-- Type: type_id -->
        <InputWrapper>
          <ListType v-model="type_id" />
          <span v-if="typeIdError" class="text-xs text-red-500">{{ typeIdError }}</span>
        </InputWrapper>
        <!-- Niveau: academic_level_id -->
        <InputWrapper>
          <ListAcademicalLevel v-model="academic_level_id" />
          <span v-if="academicLevelIdError" class="text-xs text-red-500">{{
            academicLevelIdError
          }}</span>
        </InputWrapper>
        <!-- Fonction: fonction_id -->
        <InputWrapper>
          <ListFonctions v-model="fonction_id" />
          <span v-if="fonctionIdError" class="text-xs text-red-500">{{ fonctionIdError }}</span>
        </InputWrapper>
        <!-- School: school_id -->
        <InputWrapper>
          <ListSchool v-model="school_id" />
          <span v-if="schoolIdError" class="text-xs text-red-500">{{ schoolIdError }}</span>
        </InputWrapper>
      </FormSection>
      <div class="w-full flex h-px bg-gray-300"></div>
      <div class="flex items-center justify-end gap-2">
        <Button variant="outline" type="button" @click="resetForm">
          <span class="flex iconify hugeicons--cancel-01 mr-1.5"></span>
          Annuler
        </Button>
        <Button type="submit">
          <span v-if="!loading" class="flex items-center gap-2">
            <span class="iconify hugeicons--floppy-disk mr-1"></span>
            <span>Enregistrer</span>
          </span>
          <span v-else>
            <div class="flex items-center gap-2">
              <Spinner />
              <span>Enregistrement en cours...</span>
            </div>
          </span>
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
