import axios from "axios"

const apiClient = axios.create({
  baseURL: process.env.NEXT_PUBLIC_CHURCHCRM_URL || "/api",
  withCredentials: true,
  headers: {
    "Content-Type": "application/json",
  },
})

// Types pour les réponses API
export interface Person {
  PersonId: number
  FirstName: string
  LastName: string
  FullName: string
  Email?: string
  Phone?: string
  Gender?: string
  BirthDate?: string
  Created?: string
  Modified?: string
  FamilyId?: number
  Address?: string
  Photo?: string
}

export interface PeopleResponse {
  people: Person[]
  count: number
}

export interface Payment {
  Id: number
  GroupKey: string
  Amount: number
  Nondeductible: number
  Schedule: string
  Method: string
  Comment: string
  PledgeOrPayment: string
  Date: string
  DateLastEdited: string
  EditedBy: string
  Fund: string
  FormattedFY: string
  FamilyId?: number
  FamilyName?: string
}

export interface PaymentsResponse {
  payments: Payment[]
}

export interface Deposit {
  Id: number
  Date: string
  Comment: string
  Closed: boolean
  Type: string
  Total?: number
}

export interface DepositResponse {
  deposits: Deposit[]
}

export interface DonationFund {
  Id: number
  Name: string
  Description: string
  Active: boolean
}

// API Functions
export const api = {
  // Personnes
  getLatestPeople: async (limit = 20): Promise<Person[]> => {
    const response = await apiClient.get<PeopleResponse>("/persons/latest")
    return response.data.people.slice(0, limit)
  },

  getUpdatedPeople: async (limit = 20): Promise<Person[]> => {
    const response = await apiClient.get<PeopleResponse>("/persons/updated")
    return response.data.people.slice(0, limit)
  },

  searchPeople: async (query: string): Promise<Person[]> => {
    const response = await apiClient.get<Person[]>(`/persons/search/${query}`)
    return response.data
  },

  getPerson: async (id: number): Promise<Person> => {
    const response = await apiClient.get<Person>(`/persons/${id}`)
    return response.data
  },

  getPersonPhoto: (id: number): string => {
    return `${process.env.NEXT_PUBLIC_CHURCHCRM_URL || ""}/api/person/${id}/photo`
  },

  getPersonAvatar: (id: number, name: string): string => {
    return `${process.env.NEXT_PUBLIC_CHURCHCRM_URL || ""}/api/person/${id}/avatar`
  },

  // Paiements / Dons
  getPayments: async (): Promise<Payment[]> => {
    const response = await apiClient.get<PaymentsResponse>("/payments/")
    return response.data.payments
  },

  getFamilyPayments: async (familyId: number): Promise<Payment[]> => {
    const response = await apiClient.get<{ data: Payment[] }>(`/payments/family/${familyId}/list`)
    return response.data.data
  },

  createPayment: async (payment: Partial<Payment>): Promise<Payment> => {
    const response = await apiClient.post<{ payment: Payment }>("/payments/", payment)
    return response.data.payment
  },

  deletePayment: async (groupKey: string): Promise<void> => {
    await apiClient.delete(`/payments/${groupKey}`)
  },

  // Dépôts
  getDeposits: async (): Promise<Deposit[]> => {
    const response = await apiClient.get<Deposit[]>("/deposits")
    return response.data
  },

  getDepositsDashboard: async (): Promise<Deposit[]> => {
    const response = await apiClient.get<Deposit[]>("/deposits/dashboard")
    return response.data
  },

  getDeposit: async (id: number): Promise<Deposit> => {
    const response = await apiClient.get<Deposit>(`/deposits/${id}`)
    return response.data
  },

  createDeposit: async (deposit: Partial<Deposit>): Promise<Deposit> => {
    const response = await apiClient.post<Deposit>("/deposits", deposit)
    return response.data
  },

  updateDeposit: async (id: number, deposit: Partial<Deposit>): Promise<Deposit> => {
    const response = await apiClient.post<Deposit>(`/deposits/${id}`, deposit)
    return response.data
  },

  deleteDeposit: async (id: number): Promise<void> => {
    await apiClient.delete(`/deposits/${id}`)
  },

  getDepositPayments: async (id: number): Promise<Payment[]> => {
    const response = await apiClient.get<Payment[]>(`/deposits/${id}/payments`)
    return response.data
  },

  // Fonds de dons
  getDonationFunds: async (): Promise<DonationFund[]> => {
    const response = await apiClient.get<DonationFund[]>("/donationFunds")
    return response.data
  },
}

// Hook pour vérifier la connexion à l'API
export async function checkApiConnection(): Promise<boolean> {
  try {
    await apiClient.get("/persons/latest", { timeout: 2000 })
    return true
  } catch {
    return false
  }
}

export default apiClient
