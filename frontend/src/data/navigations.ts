export const modulesItems = [
  {
    id: 1,
    text: 'Ressources Humaines',
    icon: 'hugeicons--user-group',
    link: '/rh/',
    permission: 'personnel.module',
  },
  {
    id: 2,
    text: 'Insertion Professionnelle',
    icon: 'hugeicons--user-account',
    link: '',
  },
  {
    id: 3,
    text: 'Infrastructures Equipements',
    icon: 'hugeicons--building-06',
    link: '/infra/prealables',
    permission: 'infrastructure.full',
  },
  {
    id: 4,
    text: 'Gestion Élèves',
    icon: 'hugeicons--student',
    link: '/apprenants',
    permission: 'students.view',
  },
  {
    id: 5,
    text: 'Gestion Stock',
    icon: 'hugeicons--package',
    link: '/stock',
    permission: 'stock.full',
  },
  {
    id: 6,
    text: 'Location Vente',
    icon: 'hugeicons--shopping-cart-01',
    link: '/location',
    permission: 'sales.full', // Using sales.full for now
  },
  {
    id: 7,
    text: 'Comptabilité Simplifée',
    icon: 'hugeicons--money-bag-01',
    link: '/comptabilite',
    permission: 'accounting.module',
  },
  {
    id: 8,
    text: 'Administration',
    icon: 'hugeicons--settings-02',
    link: '/admin',
    permission: 'users.view', // Or admin role check
  },
  {
    id: 9,
    text: 'Horaire',
    icon: 'hugeicons--calendar-03',
    link: '#',
  },
  {
    id: 10,
    text: 'Synchroniser',
    icon: 'hugeicons--refresh-04',
    link: '#',
  }
]

export const studentModulesItemNav = [
  {
    id: 1,
    text: 'Tableau de bord',
    icon: 'hugeicons--dashboard-square-02',
    link: '/apprenants',
    permission: 'students.view',
  },
  {
    id: 2,
    text: 'Saisie préalable',
    icon: 'hugeicons--file-edit',
    link: '/apprenants/saisie-prealable',
    permission: 'students.view',
  },
  {
    id: 3,
    text: 'Saisie des opérations',
    icon: 'hugeicons--task-add-01',
    link: '/apprenants/operations',
    permission: 'students.view',
  } /*
    {
        id: 4,
        text: "Rapports",
        icon: "hugeicons--analytics-02",
        link: "/apprenants/rapports",
    },*/,
]

export const rhModulesItemNav = [
  {
    id: 1,
    text: 'Tableau de bord',
    icon: 'hugeicons--dashboard-square-02',
    link: '/rh',
    permission: 'personnel.module',
  },
  {
    id: 2,
    text: 'Saisi préalable',
    icon: 'hugeicons--file-edit',
    link: '/rh/saisie/personnel',
    permission: 'personnel.module',
  },
  /* {
         id: 3,
         text: "Saisie des opérations",
         icon: "hugeicons--task-add-01",
         link: "/rh/operations",
     },*/
]

export const comptaModuleItemsNav = [
  {
    id: 1,
    text: 'Tableau de bord',
    icon: 'hugeicons--dashboard-square-02',
    link: '/comptabilite',
    permission: 'accounting.module',
  },
  {
    id: 2,
    text: 'Saisi préalable',
    icon: 'hugeicons--file-edit',
    link: '/comptabilite/saisie-prealable',
    permission: 'accounting.module',
  },
  {
    id: 3,
    text: 'Saisie des opérations',
    icon: 'hugeicons--task-add-01',
    link: '/comptabilite/saisie-operations',
    permission: 'accounting.module',
  },
  {
    id: 4,
    text: 'Frais',
    icon: 'hugeicons--wallet-03',
    link: '/comptabilite/frais',
    permission: 'fees.full',
  },
]

export const adminModuleItemsNav = [
  {
    id: 1,
    text: 'Tableau de bord',
    icon: 'hugeicons--dashboard-square-02',
    link: '/admin',
    permission: 'users.view',
  },
  {
    id: 2,
    text: 'Type',
    icon: 'hugeicons--tick-double-02 ',
    link: '/admin/type',
    permission: 'academic-levels.full', // Assumption
  },
  {
    id: 3,
    text: 'Ecoles',
    icon: 'hugeicons--school ',
    link: '/admin/ecoles',
    permission: 'schools.view',
  },
]
export const infraModuleItemNav = [
  {
    id: 2,
    text: 'Préalables',
    icon: 'hugeicons--file-edit',
    link: '/infra/prealables',
    permission: 'infrastructure.full',
  },
  {
    id: 3,
    text: 'Opérations',
    icon: 'hugeicons--bookmark-add-02',
    link: '/infra/operations',
    permission: 'infrastructure.full',
  },
]

export const stockModuleItemsNav = [
  {
    id: 1,
    text: 'Tableau de bord',
    icon: 'hugeicons--dashboard-square-02',
    link: '/stock/dashboard',
    permission: 'stock.full',
  },
  {
    id: 2,
    text: 'Préalables',
    icon: 'hugeicons--file-edit',
    link: '/stock/prealables/articles',
    permission: 'stock.full',
  },
  {
    id: 3,
    text: 'Opérations',
    icon: 'hugeicons--task-add-01',
    link: '/stock/operations',
    permission: 'stock.full',
  },
]

export const locationModuleItemNav = [
  {
    id: 1,
    text: 'Préalables',
    icon: 'hugeicons--file-edit',
    link: '/location/prealables',
    permission: 'location.full',
  },
  {
    id: 2,
    text: 'Opérations',
    icon: 'hugeicons--task-add-01',
    link: '/location/operations',
    permission: 'location.full',
  },
]

export const modulesNavigation = {
  students: studentModulesItemNav,
  rh: rhModulesItemNav,
  compta: comptaModuleItemsNav,
  admin: adminModuleItemsNav,
  infra: infraModuleItemNav,
  stock: stockModuleItemsNav,
  location: locationModuleItemNav,
}
