import type { RouteRecordRaw } from 'vue-router'

const studentRoutes: RouteRecordRaw[] = [
  {
    meta: { permission: 'students.view' },
    path: '/apprenants',
    name: 'apprenants-home-module',
    component: () => import('../app/student/student/HomeModule1.vue'),
  } /*
  {
    path: "/apprenants/saisie-prealable",
    name: "apprenants-module-saisie-school",
    component: () => import('../app/student/student/saisie-prealable/SaisiSchool.vue')
  },*/,
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/saisie-prealable',
    name: 'apprenants-module-saisie',
    component: () => import('../app/student/student/saisie-prealable/MainSaisiPrealable.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/saisie-prealable/classes',
    name: 'apprenants-module-saisie-classes',
    component: () => import('../app/student/student/saisie-prealable/SaisiClasse.vue'),
  },

  {
    meta: { permission: 'students.view' },
    path: '/apprenants/saisie-prealable/cours',
    name: 'apprenants-module-saisie-cours',
    component: () => import('../app/student/student/saisie-prealable/SaisiCours.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/saisie-prealable/cours/nouveau',
    name: 'apprenants-module-nouveau-cours',
    component: () => import('@/components/forms/saisie-prealable/NewCourseForm.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/saisie-prealable/niveau-academique',
    name: 'apprenants-module-saisie-niveau-academique',
    component: () => import('../app/student/student/saisie-prealable/SaisiNiveauAcademic.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/saisie-prealable/account-class',
    name: 'apprenants-module-saisie-account-class',
    component: () => import('../app/student/student/saisie-prealable/SaisiAccountClasse.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/saisie-prealable/academic-personnal',
    name: 'apprenants-module-saisie-academic-personnal',
    component: () => import('../app/student/student/saisie-prealable/SaisiPersonnalAcademic.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/saisie-prealable/account-type',
    name: 'apprenants-module-saisie-account-type',
    component: () => import('../app/student/student/saisie-prealable/SaisiAccountTypes.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/saisie-prealable/account-number',
    name: 'apprenants-module-saisie-account-number',
    component: () => import('../app/student/student/saisie-prealable/SaisiAccountNumber.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/saisie-prealable/fonction',
    name: 'apprenants-module-saisie-fonction',
    component: () => import('../app/student/student/saisie-prealable/SaisiFonction.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/saisie-prealable/parent',
    name: 'apprenants-module-saisie-parent',
    component: () => import('../app/student/student/saisie-prealable/SaisiParent.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/nouveau-personnel-academique',
    name: 'apprenants-module-saisie-operation-new-academic-personnel',
    component: () => import('../app/student/student/saisie-prealable/NewAcademicPersonnel.vue'),
  },

  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations',
    name: 'apprenants-module-saisie-operation',
    component: () => import('../app/student/student/operations/MainSaisieOperations.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/nouveau-eleve',
    name: 'apprenants-module-saisie-operation-new-inscription',
    component: () => import('../components/forms/operations/NewStudent.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/edition-eleve/:id',
    name: 'apprenants-module-saisie-operation-edition-inscription',
    component: () => import('../components/forms/operations/EditStudent.vue'),
  },

  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/presences',
    name: 'apprenants-module-presence',
    component: () => import('../app/student/student/operations/StudentsPresence.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/fiche-cotation',
    name: 'apprenants-module-fiche-cotation',
    component: () => import('../app/student/student/operations/FicheCotation.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/repechage',
    name: 'apprenants-module-fiche-repechage',
    component: () => import('../app/student/student/operations/ListeRepechage.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/deliberation',
    name: 'apprenants-module-fiche-deliberation',
    component: () => import('../app/student/student/operations/ListeDeliberation.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/deliberation-generale/:studentId',
    name: 'DeliberationGenerale',
    component: () => import('../app/student/student/operations/DeliberationGenerale.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/validation-laureats',
    name: 'apprenants-module-fiche-validation-laureats',
    component: () => import('../app/student/student/operations/ValidationLaureats.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/visite-classe',
    name: 'apprenants-module-fiche-visite-classe',
    component: () => import('../app/student/student/operations/VisiteClasses.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/visite-classe/nouvelle',
    name: 'apprenants-module-nouvelle-visite-classe',
    component: () => import('@/components/forms/operations/NewVisiteClasse.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/edit-visite-classe/:id',
    name: 'apprenants-module-edit-visite-classe',
    component: () => import('@/components/forms/operations/EditVisitesClasse.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/gestion-disciplinaire',
    name: 'apprenants-module-gestion-disciplinaire',
    component: () => import('../app/student/student/gest-disciplinaire/SortieElves.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/gestion-disciplinaire/sortie',
    name: 'gestion-disciplinaire-newsortie',
    component: () => import('@/components/forms/gest-disciplinaire/NewSortie.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/gestion-disciplinaire/cas',
    name: 'gestion-disciplinaire-newcas',
    component: () => import('@/components/forms/gest-disciplinaire/NewCasDisc.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/gestion-disciplinaire/note',
    name: 'gestion-disciplinaire-newnote',
    component: () => import('@/components/forms/gest-disciplinaire/NewNote.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/gestion-disciplinaire/abandon',
    name: 'gestion-disciplinaire-newabandon',
    component: () => import('@/components/forms/gest-disciplinaire/NewAbandon.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/gestion-disciplinaire/abandon/:id',
    name: 'gestion-disciplinaire-editabandon',
    component: () => import('@/components/forms/gest-disciplinaire/NewAbandon.vue'),
    props: true, // permet de récupérer :id comme prop
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/gestion-disciplinaire/indiscipline',
    name: 'apprenants-module-gestion-disciplinaire-indiscipline',
    component: () => import('../app/student/student/gest-disciplinaire/CasIndisciplines.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/gestion-disciplinaire/abandons',
    name: 'apprenants-module-gestion-disciplinaire-abandons',
    component: () => import('../app/student/student/gest-disciplinaire/ListeAbandon.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/gestion-disciplinaire/note-conduite',
    name: 'apprenants-module-gestion-disciplinaire-note-conduite',
    component: () => import('../app/student/student/gest-disciplinaire/NoteConduite.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/gestion-disciplinaire/note-conduite/edit/:id',
    name: 'apprenants-module-gestion-disciplinaire-edit-note-conduite',
    component: () => import('../components/forms/gest-disciplinaire/EditNote.vue'),
  },
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/gestion-disciplinaire/note-conduite/nouvelle',
    name: 'apprenants-module-gestion-disciplinaire-new-note-conduite',
    component: () => import('../components/forms/gest-disciplinaire/NewNote.vue'),
  },

  // == BULLETIN == //
  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/bulletin-scolaire',
    name: 'apprenants-module-bulletin-scolaire',
    component: () => import('@/app/student/student/operations/bulletin/BulletinScolaire.vue'),
  },

  {
    meta: { permission: 'students.view' },
    path: '/apprenants/operations/student-infos/:id',
    name: 'apprenants-module-eleve-details',
    component: () => import('@/app/student/student/operations/StudentInfos.vue'),
  },
]

export { studentRoutes }
