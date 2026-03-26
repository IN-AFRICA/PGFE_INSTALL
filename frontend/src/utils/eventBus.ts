// src/eventBus.ts
import mitt from 'mitt'

// 📌 Définition des types d'événements
type Events = {
  accountClassUpdated: void
  accountNumberUpdated: void
  accountTypeUpdated: void
  accountUpdated: void
  amortissementUpdated: void
  analyticsPlanUpdated: void
  classRoomUpdated: void
  clotureExerciceUpdated: void
  courseUpdated: void
  communeUpdated: void
  countryUpdated: void
  editAccountClass: void
  editAccountNumber: void
  editAccountType: void
  editClassRoom: void
  editFilliere: void
  editFonction: void
  editNiveauAcademique: void
  editParent: void
  editPersonnalAcademic: void
  filiereUpdated: void
  fonctionUpdated: void
  immobilisationUpdated: void
  indisciplineCaseUpdated: void
  infraEquipmentUpdated: void
  journalUpdated: void
  laureatUpdated: void
  motifUpdated: void
  niveauAcademicUpdated: void
  noteConduiteUpdated: void
  paiementCreated: void
  parentCreated: void
  parentUpdated: void
  paymentUpdated: void
  personalUpdated: void
  personnalAcademicUpdated: void
  planComptableUpdated: void
  presenceCreated: void
  presenceUpdated: void
  provinceUpdated: void
  repechageUpdated: void
  schoolUpdated: void
  stockArticleUpdated: void
  stockEntryUpdated: void
  stockExitUpdated: void
  stockProviderUpdated: void
  studentExitUpdated: void
  studentUpdated: void
  subaccountComptableUpdated: void
  territoryUpdated: void
  typeUpdated: void
}





export const eventBus = mitt<Events>()
