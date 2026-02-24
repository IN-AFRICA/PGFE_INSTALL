const getApiConfig = () => {
  return {
    API_BASE: 'https://pgfe-back.inafrica.tech/api/v1/',
    SANCTUM_BASE: 'https://pgfe-back.inafrica.tech/',
  }
}

const config = getApiConfig()
export const BASE_URL = config.API_BASE
export const SANCTUM_BASE_URL = config.SANCTUM_BASE

export const API_ROUTES = {
  CSRF_COOKIE: 'sanctum/csrf-cookie',
  LOGIN: 'auth/login',
  LOGOUT: 'auth/logout',
  USER_INFOS: 'auth/me',
  SYNC: 'sync',

  // Routes for Filiere
  CREATE_FILLIERE: 'school/filiaires/store',
  GET_FILLIERES: 'school/filiaires/lists',
  UPDATE_FILLIERE: 'school/filiaires/:filiaire/update',
  DELETE_FILLIERE: 'school/filiaires/:filiaire/delete',
  //Routes for classRooms
  CREATE_CLASSROOM: 'school/classrooms',
  GET_CLASSROOMS: 'school/classrooms',
  UPDATE_CLASSROOM: 'school/classrooms/:classroom',
  DELETE_CLASSROOM: 'school/classrooms/:classroom',
  EXPORT_CLASSROOMS: 'classrooms/export',

  // == Geo routes ==
  // GET
  GET_COUNTRIES: 'geo/countries',
  GET_PROVINCES: 'geo/provinces',
  GET_TERRITORIES: 'geo/territories',
  GET_COMMUNES: 'geo/communes',
  // POST
  CREATE_COUNTRY: 'geo/countries',
  CREATE_PROVINCE: 'geo/provinces',
  CREATE_TERRITORY: 'geo/territories',
  CREATE_COMMUNE: 'geo/communes',

  //Routes liste
  GET_PARENTS: 'parents',
  CREATE_PARENT: 'parents',
  GET_TYPES: 'geo/types',
  GET_ACADEMIC_LEVELS: 'academic/levels',
  GET_SCHOOLS: 'school/schools',
  //Student routes
  CREATE_STUDENT: 'students',
  GET_STUDENTS: 'students',
  UPDATE_STUDENT: 'students/:student',
  DELETE_STUDENT: 'students/:student',
  GET_STUDENT_BY_ID: 'students/:student',
  GET_STUDENT_REGISTRATIONS: 'students/registrations/list',
  EXPORT_STUDENT_REGISTRATIONS: 'students/registrations/export',
  EXPORT_STUDENT_REGISTRATIONS_PDF: 'students/registrations/export-pdf',
  GET_SEMESTRE: 'school/semesters',

  //PRESENCE - Routes manquantes ajoutées
  CREATE_STUDENT_PRESENCE: 'presence/presences',
  GET_STUDENT_PRESENCE: 'presence/presences',
  UPDATE_STUDENT_PRESENCE: 'presence/presences/:id',
  DELETE_STUDENT_PRESENCE: 'presence/presences/:id',

  //Visite de classe
  CREATE_VISITE_CLASSE: 'presence/visits',
  GET_VISITE_CLASSES: 'presence/visits',
  GET_VISIT_BY_ID: 'presence/visits/:id',
  UPDATE_VISITE_CLASSE: 'presence/visits/:id',
  DELETE_VISITE_CLASSE: 'presence/visits/:id',
  // gestion disciplinaire

  // Student exits routes
  GET_STUDENT_EXIT: 'presence/student-exits',
  CREATE_STUDENT_EXIT: 'presence/student-exits',
  UPDATE_STUDENT_EXIT: 'presence/student-exits/:studentExitId',
  DELETE_STUDENT_EXIT: 'presence/student-exits/:studentExit',

  // Indiscipline cases routes
  GET_INDISCIPLINE_CASES: 'presence/indiscipline-cases',
  CREATE_INDISCIPLINE_CASE: 'presence/indiscipline-cases',
  UPDATE_INDISCIPLINE_CASE: 'presence/indiscipline-cases/:indisciplineCase',
  DELETE_INDISCIPLINE_CASE: 'presence/indiscipline-cases/:indisciplineCase',

  // Disciplinary actions (punishments)
  GET_DISCIPLINARY_ACTIONS: 'disciplinary-actions',

  // Courses routes
  GET_COURSES: 'school/courses',
  GET_COURSE: 'school/courses/:course',
  CREATE_COURSE: 'school/courses',
  UPDATE_COURSE: 'school/courses/:course',
  DELETE_COURSE: 'school/courses/:course',

  // Dashboard route
  GET_DASHBOARD: 'students/dashboard',

  // Fonctions routes
  GET_FONCTIONS: 'geo/fonctions',

  //note de conduite
  GET_NOTE_CONDUITES: 'conduite/notes',
  CREATE_NOTE_CONDUITE: 'conduite/notes',
  GET_NOTE_CONDUITE_BY_ID: (id: number | string) => `conduite/notes/${id}`,
  UPDATE_NOTE_CONDUITE: (id: number | string) => `conduite/notes/${id}`,
  DELETE_NOTE_CONDUITE: (id: number | string) => `conduite/notes/${id}`,

  //fiche cotation import & export
  GET_FICHE_COTATIONS: 'academic/fiche-cotations',
  EXPORT_FICHE_COTATION: 'academic/fiche-cotations/export',
  IMPORT_FICHE_COTATION: 'academic/fiche-cotations/import',
  INITIALIZE_FICHE_COTATION: 'academic/fiche-cotations/initialize',

  //repechage
  IMPORT_REPECHAGE: 'students/repechage/import',
  EXPORT_REPECHAGE: 'students/repechage/export',
  EXPORT_PDF_REPECHAGE: 'students/repechage/export-pdf',
  GET_REPECHAGES: 'students/repechage',

  //import laureat
  IMPORT_LAUREAT: 'students/validation-aureat/import',
  EXPORT_LAUREAT: (id: number | string) => `students/validation-aureat/export?class=${id}`,
  GET_LAUREAT: 'students/validation-aureat',

  //school years
  GET_SCHOOL_YEARS: 'school/years',

  //Semester
  GET_SEMESTERS: 'school/semesters',

  //conduite
  GET_CONDUITE: 'conduite',

  //c cycle
  GET_CYCLES: 'academic/cycles',

  EXPORT_STUDENT_PRESENCE_EXCEL: 'presence/presences/export',
  EXPORT_STUDENT_PRESENCE_PDF: 'presence/presences/export-pdf',

  // Délibérations
  GET_DELIBERATIONS: 'academic/deliberations',
  CREATE_DELIBERATION: 'academic/deliberations',
  UPDATE_DELIBERATION: 'academic/deliberations/:id',
  DELETE_DELIBERATION: 'academic/deliberations/:id',
  INITIALIZE_DELIBERATION: 'academic/deliberations/initialize',

  // Délibération générale
  GET_GENERAL_DELIB_STUDENT: 'academic/deliberations/general/students/:student',
  VALIDATE_GENERAL_DELIB_STUDENT: 'academic/deliberations/general/students/:student/validate',

  GET_BULLETIN_JSON: 'presence/bulletin/json',
  PRINT_BULLETIN: 'presence/bulletin/print',

  // Abandon cases routes
  GET_ABANDON_CASES: 'presence/abandon-cases',
  CREATE_ABANDON_CASE: 'presence/abandon-cases',
  UPDATE_ABANDON_CASE: 'presence/abandon-cases/:abandonCase',
  DELETE_ABANDON_CASE: 'presence/abandon-cases/:abandonCase',

  // COMPTABILITE

  //frais scolaire
  GET_SCHOOL_FEES: 'accounting/fees',
  CREATE_SCHOOL_FEE: 'accounting/fees',
  GET_SCHOOL_FEE_BY_ID: (id: number | string) => `accounting/fees/${id}`,
  UPDATE_SCHOOL_FEE: (id: number | string) => `accounting/fees/${id}`,
  DELETE_SCHOOL_FEE: (id: number | string) => `accounting/fees/${id}`,

  //currencies
  GET_CURRENCIES: 'currencies',
  CREATE_CURRENCY: 'currencies',
  GET_CURRENCY_BY_ID: (id: number | string) => `currencies/${id}`,
  UPDATE_CURRENCY: (id: number | string) => `currencies/${id}`,
  DELETE_CURRENCY: (id: number | string) => `currencies/${id}`,

  //types de frais
  GET_FEE_TYPES: 'accounting/fees/types',
  CREATE_FEE_TYPE: 'accounting/fees/types',
  GET_FEE_TYPE_BY_ID: (id: number | string) => `accounting/fees/types/${id}`,
  UPDATE_FEE_TYPE: (id: number | string) => `accounting/fees/types/${id}`,
  DELETE_FEE_TYPE: (id: number | string) => `accounting/fees/types/${id}`,

  //payments
  GET_PAYMENTS: 'accounting/payments/transactions',
  CREATE_PAYMENT: 'accounting/payments/transactions',
  GET_PAYMENT_BY_ID: (id: number | string) => `accounting/payments/transactions/${id}`,
  UPDATE_PAYMENT: (id: number | string) => `accounting/payments/transactions/${id}`,
  DELETE_PAYMENT: (id: number | string) => `accounting/payments/transactions/${id}`,

  //payments methode
  GET_PAYMENT_METHODE: 'accounting/payments/methods',
  CREATE_PAYMENT_METHODE: 'accounting/payments/methods',
  GET_PAYMENT_METHODE_BY_ID: (id: number | string) => `accounting/payments/methods/${id}`,
  UPDATE_PAYMENT_METHODE: (id: number | string) => `accounting/payments/methods/${id}`,
  DELETE_PAYMENT_METHODE: (id: number | string) => `accounting/payments/methods/${id}`,

  //payment motif
  GET_PAYMENT_MOTIFS: 'accounting/payments/motifs',
  CREATE_PAYMENT_MOTIF: 'accounting/payments/motifs',
  GET_PAYMENT_MOTIF_BY_ID: (id: number | string) => `accounting/payments/motifs/${id}`,
  UPDATE_PAYMENT_MOTIF: (id: number | string) => `accounting/payments/motifs/${id}`,
  DELETE_PAYMENT_MOTIF: (id: number | string) => `accounting/payments/motifs/${id}`,

  //Exchange rates
  GET_EXCHANGE_RATES: 'exchange-rates',
  CREATE_EXCHANGE_RATE: 'exchange-rates',
  GET_EXCHANGE_RATE_BY_ID: (id: number | string) => `exchange-rates/${id}`,
  UPDATE_EXCHANGE_RATE: (id: number | string) => `exchange-rates/${id}`,
  DELETE_EXCHANGE_RATE: (id: number | string) => `exchange-rates/${id}`,

  //compte bacaire
  CREATE_BANK_ACCOUNT: 'accounting/accounts',
  GET_BANK_ACCOUNTS: 'accounting/accounts',
  GET_BANK_ACCOUNT_BY_ID: (id: number | string) => `accounting/accounts/${id}`,
  UPDATE_BANK_ACCOUNT: (id: number | string) => `accounting/accounts/${id}`,
  DELETE_BANK_ACCOUNT: (id: number | string) => `accounting/accounts/${id}`,

  //plan comptable

  //compte parent
  GET_COMPTE: 'accounting/account-plans',
  CREATE_COMPTE: 'accounting/account-plans',
  GET_ONE_COMPTE: (id: number | string) => `accounting/account-plans/${id}`,
  UPDATE_COMPTE: (id: number | string) => `accounting/account-plans/${id}`,
  DELETE_COMPTE: (id: number | string) => `accounting/account-plans/${id}`,
  EXPORT_ACCOUNTS_PDF: 'accounting/account-plans/export-pdf',
  EXPORT_ACCOUNTS_EXCEL: 'accounting/account-plans/export',

  //sous compte
  GET_SUB_COMPTE: 'accounting/sub-account-plans',
  CREATE_SUB_COMPTE: 'accounting/sub-account-plans',
  GET_ONE_SUB_COMPTE: (id: number | string) => `accounting/sub-account-plans/${id}`,
  UPDATE_SUB_COMPTE: (id: number | string) => `accounting/sub-account-plans/${id}`,
  DELETE_SUB_COMPTE: (id: number | string) => `accounting/sub-account-plans/${id}`,
  EXPORT_SUB_ACCOUNTS_PDF: 'accounting/sub-account-plans/export-pdf',
  EXPORT_SUB_ACCOUNTS_EXCEL: 'accounting/sub-account-plans/export',

  //class compte
  GET_CLASS_COMPTE: 'accounting/class-comptabilities',
  CREATE_CLASS_COMPTE: 'accounting/class-comptabilities',
  GET_ONE_CLASS_COMPTE: (id: number | string) => `accounting/class-comptabilities/${id}`,
  UPDATE_CLASS_COMPTE: (id: number | string) => `accounting/class-comptabilities/${id}`,
  DELETE_CLASS_COMPTE: (id: number | string) => `accounting/class-comptabilities/${id}`,

  //categorie compte
  GET_CATEGORIE_COMPTE: 'accounting/category-comptabilities',
  CREATE_CATEGORIE_COMPTE: 'accounting/category-comptabilities',
  GET_ONE_CATEGORIE_COMPTE: (id: number | string) => `accounting/category-comptabilities/${id}`,
  UPDATE_CATEGORIE_COMPTE: (id: number | string) => `accounting/category-comptabilities/${id}`,
  DELETE_CATEGORIE_COMPTE: (id: number | string) => `accounting/category-comptabilities/${id}`,

  // plan analytique
  GET_ANALYTICS_PLAN: 'accounting/analytic-plans',
  CREATE_ANALYTICS_PLAN: 'accounting/analytic-plans',
  GET_ONE_ANALYTICS_PLAN: (id: number | string) => `accounting/analytic-plans/${id}`,
  UPDATE_ANALYTICS_PLAN: (id: number | string) => `accounting/analytic-plans/${id}`,
  DELETE_ANALYTICS_PLAN: (id: number | string) => `accounting/analytic-plans/${id}`,

  //budget
  GET_BUDGET: 'accounting/budget-comptabilities',
  CREATE_BUDGET: 'accounting/budget-comptabilities',
  GET_ONE_BUDGET: (id: number | string) => `accounting/budget-comptabilities/${id}`,
  UPDATE_BUDGET: (id: number | string) => `accounting/budget-comptabilities/${id}`,
  DELETE_BUDGET: (id: number | string) => `accounting/budget-comptabilities/${id}`,

  // A_NOUVEAU
  GET_A_NEW_COMPTABILITY: 'accounting/a-new-comptabilities',
  CREATE_A_NEW_COMPTABILITY: 'accounting/a-new-comptabilities',
  GET_ONE_A_NEW_COMPTABILITY: (id: number | string) => `accounting/a-new-comptabilities/${id}`,
  UPDATE_A_NEW_COMPTABILITY: (id: number | string) => `accounting/a-new-comptabilities/${id}`,
  DELETE_A_NEW_COMPTABILITY: (id: number | string) => `accounting/a-new-comptabilities/${id}`,

  CREATE_CLOTURE_EXERCICE: 'accounting/exercices',

  //dashboard comptabilite
  GET_DATA_DASHBOARD: 'accounting/dashboard',

  //entree
  GET_INPUTACCOUNT: 'accounting/input-accounts',
  CREATE_INPUTACCOUNT: 'accounting/input-accounts',
  GET_ONE_INPUTACCOUNT: (id: number | string) => `accounting/input-accounts/${id}`,
  UPDATE_INPUTACCOUNT: (id: number | string) => `accounting/input-accounts/${id}`,
  DELETE_INPUTACCOUNT: (id: number | string) => `accounting/input-accounts/${id}`,

  //sortie
  GET_OUTPUTACCOUNT: 'accounting/output-accounts',
  CREATE_OUTPUTACCOUNT: 'accounting/output-accounts',
  GET_ONE_OUTPUTACCOUNT: (id: number | string) => `accounting/output-accounts/${id}`,
  UPDATE_OUTPUTACCOUNT: (id: number | string) => `accounting/output-accounts/${id}`,
  DELETE_OUTPUTACCOUNT: (id: number | string) => `accounting/output-accounts/${id}`,

  //journal
  GET_JOURNAL: 'accounting/journals',
  GET_ONE_JOURNAL: (id: number | string) => `accounting/journals/${id}`,
  CREATE_JOURNAL: 'accounting/journals',
  UPDATE_JOURNAL: (id: number | string) => `accounting/journals/${id}`,
  ABANDONED_JOURNAL_ENTRY: (id: number | string) => `accounting/journals/${id}`,
  // Export journal
  EXPORT_JOURNALS: 'accounting/journals/export',
  EXPORT_JOURNALS_PDF: 'accounting/journals/export-pdf',

  //Immobilisation
  GET_IMMOBILISATION: 'accounting/immo-accounts',
  CREATE_IMMOBILISATION: 'accounting/immo-accounts',
  GET_ONE_IMMOBILISATION: (id: number | string) => `accounting/immo-accounts/${id}`,
  UPDATE_IMMOBILISATION: (id: number | string) => `accounting/immo-accounts/${id}`,
  DELETE_IMMOBILISATION_ENTRY: (id: number | string) => `accounting/immo-accounts/${id}`,

  //amortissement
  GET_AMORTISSEMENT: 'accounting/immo-ammortissemen-comptabilities',
  CREATE_AMORTISSEMENT: 'accounting/immo-ammortissemen-comptabilities',
  DELETE_AMORTISSEMENT: (id: number | string) =>
    `accounting/immo-ammortissemen-comptabilities/${id}`,
  UPDATE_AMORTISSEMENT: (id: number | string) =>
    `accounting/immo-ammortissemen-comptabilities/${id}`,
  //module rh
  CREATE_PERSONALS: 'hr/personals',
  GET_PERSONALS: 'hr/personals',
  GET_ACADEMIC_PERSONALS: 'hr/academic-personals',
  GET_PERSONAL: (id: number) => `hr/personals/${id}`,
  GET_STATS_MONTH: 'hr/academic-personals/stats-by-month',
  CREATE_ACADEMIC_PERSONAL: 'hr/academic-personals',
  GET_ACADEMIC_PERSONAL: (id: number) => `hr/academic-personals/${id}`,
  PUT_ACADEMIC_PERSONAL: (id: number) => `hr/academic-personals/${id}`,
  DELETE_ACADEMIC_PERSONAL: (id: number) => `hr/academic-personals/${id}`,

  //fonction
  CREATE_FONCTION: 'geo/fonctions',

  //presence
  ASSIGN_PRESENCE: 'presence/person-presences',
  GET_PRESENCES: 'presence/person-presences',
  INITIALISE_PRESENCE: 'presence/person-presences/initialize',
  UPDATE_PRESENCE: (id: number) => `presence/person-presences/personnels/${id}`,
  UPDATE_STATUS_PRESENCE: (id: number) => `presence/person-presences/${id}`,
  //academic level
  GET_ACADEMIC_LEVEL: 'academic/levels',
  CREATE_ACADEMIC_LEVEL: 'academic/levels',
  GET_ONE_ACADEMIC_LEVEL: (id: number) => `academic/levels/${id}`,
  UPDATE_ACADEMIC_LEVEL: (id: number) => `academic/levels/${id}`,
  DELETE_ACADEMIC_LEVEL: (id: number) => `academic/levels/${id}`,

  //personnel afectation
  GET_PERSONNEL_AFFECTATION: 'hr/person_affectations',
  CREATE_PERSONNEL_AFFECTATION: 'hr/person_affectations',
  GET_ONE_PERSONNEL_AFFECTATION: (id: number) => `hr/person_affectations/${id}`,
  UPDATE_PERSONNEL_AFFECTATION: (id: number) => `hr/person_affectations/${id}`,
  DELETE_PERSONNEL_AFFECTATION: (id: number) => `hr/person_affectations/${id}`,

  //personel congees
  CREATE_PERSONNEL_CONGE: 'hr/person-conges',
  GET_PERSONNEL_CONGE: 'hr/person-conges',
  GET_ONE_PERSONNEL_CONGE: (id: number) => `hr/person-conges/${id}`,
  UPDATE_PERSONNEL_CONGE: (id: number) => `hr/person-conges/${id}`,
  DELETE_PERSONNEL_CONGE: (id: number) => `hr/person-conges/${id}`,

  //evaluation personel :
  CREATE_EVALUATION: 'hr/person-evaluations',
  GET_EVALUATIONS: 'hr/person-evaluations',
  GET_ONE_EVALUATION: (id: number) => `hr/person-evaluations/${id}`,
  UPDATE_EVALUATION: (id: number) => `hr/person-evaluations/${id}`,
  DELETE_EVALUATION: (id: number) => `hr/person-evaluations/${id}`,

  //salaires personnel
  CREATE_SALAIRE: 'hr/person_salaires',
  GET_SALAIRES: 'hr/person_salaires',
  GET_ONE_SALAIRE: (id: number) => `hr/person_salaires/${id}`,
  UPDATE_SALAIRE: (id: number) => `hr/person_salaires/${id}`,
  DELETE_SALAIRE: (id: number) => `hr/person_salaires/${id}`,

  //mois
  GET_MOIS: 'mois',

  //formation continue
  CREATE_FORMATION_CONTINUE: 'students/formation-continues',
  GET_FORMATIONS_CONTINUES: 'students/formation-continues',
  GET_ONE_FORMATION_CONTINUE: (id: number) => `students/formation-continues/${id}`,
  UPDATE_FORMATION_CONTINUE: (id: number) => `students/formation-continues/${id}`,
  DELETE_FORMATION_CONTINUE: (id: number) => `students/formation-continues/${id}`,

  // Stock routes
  GET_STOCK_PROVIDERS: 'stock/providers',
  CREATE_STOCK_PROVIDER: 'stock/providers',
  UPDATE_STOCK_PROVIDER: (id: number | string) => `stock/providers/${id}`,
  DELETE_STOCK_PROVIDER: (id: number | string) => `stock/providers/${id}`,

  // Stock articles routes
  GET_STOCK_ARTICLES: 'stock/articles',
  CREATE_STOCK_ARTICLE: 'stock/articles',
  UPDATE_STOCK_ARTICLE: (id: number | string) => `stock/articles/${id}`,
  UPDATE_STOCK_ARTICLE_STATE: (id: number | string) => `stock/articles/${id}/state`,
  DELETE_STOCK_ARTICLE: (id: number | string) => `stock/articles/${id}`,

  // Stock categories
  GET_STOCK_CATEGORIES: 'stock/categories',
  CREATE_STOCK_CATEGORY: 'stock/categories',
  UPDATE_STOCK_CATEGORY: (id: number | string) => `stock/categories/${id}`,
  DELETE_STOCK_CATEGORY: (id: number | string) => `stock/categories/${id}`,

  // Dashboard
  GET_STOCK_DASHBOARD: 'stock/dashboard',

  // Stock Inventories
  GET_STOCK_INVENTORIES: 'stock/inventories',
  CREATE_STOCK_INVENTORY: 'stock/inventories',
  UPDATE_STOCK_INVENTORY: (id: number | string) => `stock/inventories/${id}`,
  DELETE_STOCK_INVENTORY: (id: number | string) => `stock/inventories/${id}`,

  // Stock entries routes
  GET_STOCK_ENTRIES: 'stock/entries',
  CREATE_STOCK_ENTRY: 'stock/entries',
  UPDATE_STOCK_ENTRY: (id: number | string) => `stock/entries/${id}`,
  DELETE_STOCK_ENTRY: (id: number | string) => `stock/entries/${id}`,

  // Stock operations (General)
  GET_STOCK_OPERATIONS: 'stock/operations',

  // Stock exits routes
  GET_STOCK_EXITS: 'stock/exits',
  CREATE_STOCK_EXIT: 'stock/exits',
  UPDATE_STOCK_EXIT: (id: number | string) => `stock/exits/${id}`,
  DELETE_STOCK_EXIT: (id: number | string) => `stock/exits/${id}`,

  // ============================================
  // MODULE INFRASTRUCTURE - Routes nettoyées
  // ============================================

  // Dashboard
  GET_INFRA_DASHBOARD: 'infrastructures/dashboard',

  // Categories d'équipements
  GET_INFRA_CATEGORIES: 'infrastructures/categories',
  CREATE_INFRA_CATEGORY: 'infrastructures/categories',
  UPDATE_INFRA_CATEGORY: (id: number | string) => `infrastructures/categories/${id}`,
  DELETE_INFRA_CATEGORY: (id: number | string) => `infrastructures/categories/${id}`,

  // Bailleurs (Sponsors/Donors)
  GET_INFRA_BAILLEURS: 'infrastructures/bailleurs',
  CREATE_INFRA_BAILLEUR: 'infrastructures/bailleurs',
  UPDATE_INFRA_BAILLEUR: (id: number | string) => `infrastructures/bailleurs/${id}`,
  DELETE_INFRA_BAILLEUR: (id: number | string) => `infrastructures/bailleurs/${id}`,

  // Équipements
  GET_INFRA_EQUIPMENTS: 'infrastructures/equipments',
  CREATE_INFRA_EQUIPMENT: 'infrastructures/equipments',
  UPDATE_INFRA_EQUIPMENT: (id: number | string) => `infrastructures/equipments/${id}`,
  DELETE_INFRA_EQUIPMENT: (id: number | string) => `infrastructures/equipments/${id}`,

  // Inventaires d'équipements
  GET_INFRA_INVENTORIES: 'infrastructures/inventories',
  CREATE_INFRA_INVENTORY: 'infrastructures/inventories',
  DELETE_INFRA_INVENTORY: (id: number | string) => `infrastructures/inventories/${id}`,

  // Items d'inventaires d'équipements
  CREATE_INVENTORY_ITEM: (inventoryId: number | string) =>
    `infrastructures/inventories/${inventoryId}/item`,
  GET_INVENTORY_ITEMS: (inventoryId: number | string) =>
    `infrastructures/inventories/${inventoryId}/item`,

  // Inventaires d'infrastructures (suivi des bâtiments)
  GET_INFRA_INFRASTRUCTURE_INVENTAIRES: 'infrastructures/infrastructure-inventaires',
  CREATE_INFRA_INFRASTRUCTURE_INVENTAIRE: 'infrastructures/infrastructure-inventaires',
  GET_INFRA_INFRASTRUCTURE_INVENTAIRE: (id: number | string) =>
    `infrastructures/infrastructure-inventaires/${id}`,
  DELETE_INFRA_INFRASTRUCTURE_INVENTAIRE: (id: number | string) =>
    `infrastructures/infrastructure-inventaires/${id}`,

  // Items d'inventaires d'infrastructures
  CREATE_INFRA_INFRASTRUCTURE_INVENTAIRE_ITEM: (inventoryId: number | string) =>
    `infrastructures/infrastructure-inventaires/${inventoryId}/item`,
  GET_INFRA_INFRASTRUCTURE_INVENTAIRE_ITEMS: (inventoryId: number | string) =>
    `infrastructures/infrastructure-inventaires/${inventoryId}/item`,

  // Infrastructures (Bâtiments/Salles)
  GET_INFRA_INFRASTRUCTURES: 'infrastructures/infrastructures',
  CREATE_INFRA_INFRASTRUCTURE: 'infrastructures/infrastructures',
  UPDATE_INFRA_INFRASTRUCTURE: (id: number | string) => `infrastructures/infrastructures/${id}`,
  DELETE_INFRA_INFRASTRUCTURE: (id: number | string) => `infrastructures/infrastructures/${id}`,

  // États (Conditions)
  GET_INFRA_STATES: 'infrastructures/states',
  CREATE_INFRA_STATE: 'infrastructures/states',
  UPDATE_INFRA_STATE: (id: number | string) => `infrastructures/states/${id}`,
  DELETE_INFRA_STATE: (id: number | string) => `infrastructures/states/${id}`,

  // Types
  GET_INFRA_TYPES: 'infrastructures/types',
  CREATE_INFRA_TYPE: 'infrastructures/types',
  UPDATE_INFRA_TYPE: (id: number | string) => `infrastructures/types/${id}`,
  DELETE_INFRA_TYPE: (id: number | string) => `infrastructures/types/${id}`,

  // === MODULE LOCATION (RENTAL) ===

  // Rental - Sessions routes
  GET_RENTAL_SESSIONS: 'rental/sessions',
  CREATE_RENTAL_SESSION: 'rental/sessions',
  GET_RENTAL_SESSION: (id: number | string) => `rental/sessions/${id}`,
  UPDATE_RENTAL_SESSION: (id: number | string) => `rental/sessions/${id}`,
  DELETE_RENTAL_SESSION: (id: number | string) => `rental/sessions/${id}`,

  // Rental - Clients routes
  GET_RENTAL_CLIENTS: 'rental/clients',
  CREATE_RENTAL_CLIENT: 'rental/clients',
  GET_RENTAL_CLIENT: (id: number | string) => `rental/clients/${id}`,
  UPDATE_RENTAL_CLIENT: (id: number | string) => `rental/clients/${id}`,
  DELETE_RENTAL_CLIENT: (id: number | string) => `rental/clients/${id}`,

  // Rental - Equipments routes
  GET_RENTAL_EQUIPMENTS: 'rental/equipments',
  CREATE_RENTAL_EQUIPMENT: 'rental/equipments',
  GET_RENTAL_EQUIPMENT: (id: number | string) => `rental/equipments/${id}`,
  UPDATE_RENTAL_EQUIPMENT: (id: number | string) => `rental/equipments/${id}`,
  DELETE_RENTAL_EQUIPMENT: (id: number | string) => `rental/equipments/${id}`,

  // Rental - Projects routes
  GET_RENTAL_PROJECTS: 'rental/projects',
  CREATE_RENTAL_PROJECT: 'rental/projects',
  GET_RENTAL_PROJECT: (id: number | string) => `rental/projects/${id}`,
  UPDATE_RENTAL_PROJECT: (id: number | string) => `rental/projects/${id}`,
  DELETE_RENTAL_PROJECT: (id: number | string) => `rental/projects/${id}`,

  // Rental - Contracts routes
  GET_RENTAL_CONTRACTS: 'rental/contracts',
  CREATE_RENTAL_CONTRACT: 'rental/contracts',
  GET_RENTAL_CONTRACT: (id: number | string) => `rental/contracts/${id}`,
  UPDATE_RENTAL_CONTRACT: (id: number | string) => `rental/contracts/${id}`,
  DELETE_RENTAL_CONTRACT: (id: number | string) => `rental/contracts/${id}`,

  // Rental - Contract Equipments routes
  GET_RENTAL_CONTRACT_EQUIPMENTS: 'rental/contract-equipments',
  CREATE_RENTAL_CONTRACT_EQUIPMENT: 'rental/contract-equipments',
  GET_RENTAL_CONTRACT_EQUIPMENT: (id: number | string) => `rental/contract-equipments/${id}`,
  UPDATE_RENTAL_CONTRACT_EQUIPMENT: (id: number | string) => `rental/contract-equipments/${id}`,
  DELETE_RENTAL_CONTRACT_EQUIPMENT: (id: number | string) => `rental/contract-equipments/${id}`,

  // Rental - Payments routes
  GET_RENTAL_PAYMENTS: 'rental/payments',
  CREATE_RENTAL_PAYMENT: 'rental/payments',
  GET_RENTAL_PAYMENT: (id: number | string) => `rental/payments/${id}`,
  UPDATE_RENTAL_PAYMENT: (id: number | string) => `rental/payments/${id}`,
  DELETE_RENTAL_PAYMENT: (id: number | string) => `rental/payments/${id}`,
}
