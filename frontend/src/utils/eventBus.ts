// src/eventBus.ts
import mitt from 'mitt'

// 📌 Définition des types d'événements
type Events = {
  schoolUpdated: void
  filiereUpdated: void
  classRoomUpdated: void
  studentUpdated: void
  countryUpdated: void
  provinceUpdated: void
  territoryUpdated: void
  communeUpdated: void
  typeUpdated: void
  niveauAcademicUpdated: void
  accountClassUpdated: void
  personnalAcademicUpdated: void
  accountNumberUpdated: void
  accountTypeUpdated: void
  fonctionUpdated: void
  parentUpdated: void
  editClassRoom: any
  editNiveauAcademique: any
  editAccountClass: any
  editPersonnalAcademic: any
  editAccountNumber: any
  editAccountType: any
  editFonction: any
  editParent: any
  courseUpdated: any
  indisciplineCaseUpdated: any
  noteConduiteUpdated: void
  laureatUpdated: void
  presenceUpdated: void
  presenceCreated: void
  personalUpdated: void
  clotureExerciceUpdated: void
  journalUpdated: void
  immobilisationUpdated: void
  motifUpdated: void
  subaccountComptableUpdated: void
  planComptableUpdated: void
  infraEquipmentUpdated: void
  stockArticleUpdated: void
}

export const eventBus = mitt<Events>()
