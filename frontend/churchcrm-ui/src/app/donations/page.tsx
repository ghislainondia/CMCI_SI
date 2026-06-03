"use client"

import { useState, useEffect } from "react"
import { AppShell } from "@/components/layout/app-shell"
import { StatCard } from "@/components/dashboard/stat-card"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Input } from "@/components/ui/input"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import {
  Plus,
  TrendingUp,
  Calendar,
  Download,
  Loader2,
} from "lucide-react"
import { api, type Payment, type Deposit, type DonationFund } from "@/lib/api"
import { formatCurrency, formatDateTime } from "@/lib/utils"

export default function DonationsPage() {
  const [payments, setPayments] = useState<Payment[]>([])
  const [deposits, setDeposits] = useState<Deposit[]>([])
  const [funds, setFunds] = useState<DonationFund[]>([])
  const [loading, setLoading] = useState(true)
  const [filterFund, setFilterFund] = useState<string>("all")
  const [filterPeriod, setFilterPeriod] = useState<string>("30")

  useEffect(() => {
    async function loadData() {
      try {
        const [paymentsData, depositsData, fundsData] = await Promise.all([
          api.getPayments(),
          api.getDepositsDashboard(),
          api.getDonationFunds(),
        ])

        setPayments(paymentsData)
        setDeposits(depositsData)
        setFunds(fundsData)
      } catch (error) {
        console.error("Erreur lors du chargement des dons:", error)
      } finally {
        setLoading(false)
      }
    }

    loadData()
  }, [])

  // Calculs pour les KPIs
  const totalThisMonth = payments
    .filter((p) => {
      const date = new Date(p.Date)
      const now = new Date()
      return (
        date.getMonth() === now.getMonth() &&
        date.getFullYear() === now.getFullYear()
      )
    })
    .reduce((sum, p) => sum + (p.PledgeOrPayment === "Payment" ? p.Amount : 0), 0)

  const totalThisYear = payments
    .filter((p) => {
      const date = new Date(p.Date)
      const now = new Date()
      return date.getFullYear() === now.getFullYear()
    })
    .reduce((sum, p) => sum + (p.PledgeOrPayment === "Payment" ? p.Amount : 0), 0)

  const avgPerDonation = payments.length > 0
    ? payments
        .filter((p) => p.PledgeOrPayment === "Payment")
        .reduce((sum, p) => sum + p.Amount, 0) /
      payments.filter((p) => p.PledgeOrPayment === "Payment").length
    : 0

  const filteredPayments = payments.filter((p) => {
    if (filterFund !== "all" && p.Fund !== filterFund) return false

    if (filterPeriod) {
      const days = parseInt(filterPeriod)
      const cutoffDate = new Date()
      cutoffDate.setDate(cutoffDate.getDate() - days)
      if (new Date(p.Date) < cutoffDate) return false
    }

    return true
  })

  return (
    <AppShell>
      <div className="space-y-6">
        {/* KPIs */}
        <div className="grid gap-4 md:grid-cols-3">
          <StatCard
            title="Total ce mois"
            value={formatCurrency(totalThisMonth)}
            icon={TrendingUp}
          />
          <StatCard
            title="Total cette année"
            value={formatCurrency(totalThisYear)}
            icon={Calendar}
          />
          <StatCard
            title="Moyenne par don"
            value={formatCurrency(avgPerDonation)}
            icon={TrendingUp}
          />
        </div>

        {/* Filtres et actions */}
        <div className="flex flex-col sm:flex-row gap-4 justify-between">
          <div className="flex gap-2">
            <Select value={filterFund} onValueChange={setFilterFund}>
              <SelectTrigger className="w-[200px]">
                <SelectValue placeholder="Filtre par fonds" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Tous les fonds</SelectItem>
                {funds.map((fund) => (
                  <SelectItem key={fund.Id} value={fund.Name}>
                    {fund.Name}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>

            <Select value={filterPeriod} onValueChange={setFilterPeriod}>
              <SelectTrigger className="w-[150px]">
                <SelectValue placeholder="Période" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="7">7 derniers jours</SelectItem>
                <SelectItem value="30">30 derniers jours</SelectItem>
                <SelectItem value="90">90 derniers jours</SelectItem>
                <SelectItem value="365">Cette année</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div className="flex gap-2">
            <Button variant="outline">
              <Download className="h-4 w-4 mr-2" />
              Exporter
            </Button>
            <Button>
              <Plus className="h-4 w-4 mr-2" />
              Nouveau don
            </Button>
          </div>
        </div>

        {/* Graphique et liste */}
        <div className="grid gap-6 lg:grid-cols-3">
          {/* Liste des dons */}
          <div className="lg:col-span-2">
            <Card>
              <CardHeader>
                <CardTitle>Historique des dons</CardTitle>
              </CardHeader>
              <CardContent>
                {loading ? (
                  <div className="flex items-center justify-center py-12">
                    <Loader2 className="h-8 w-8 animate-spin text-muted-foreground" />
                  </div>
                ) : filteredPayments.length === 0 ? (
                  <div className="text-center py-12 text-muted-foreground">
                    Aucun don trouvé pour cette période
                  </div>
                ) : (
                  <div className="overflow-x-auto">
                    <table className="w-full">
                      <thead>
                        <tr className="border-b border-border">
                          <th className="text-left p-3 text-sm font-medium text-muted-foreground">
                            Date
                          </th>
                          <th className="text-left p-3 text-sm font-medium text-muted-foreground">
                            Famille
                          </th>
                          <th className="text-left p-3 text-sm font-medium text-muted-foreground">
                            Fonds
                          </th>
                          <th className="text-left p-3 text-sm font-medium text-muted-foreground">
                            Méthode
                          </th>
                          <th className="text-right p-3 text-sm font-medium text-muted-foreground">
                            Montant
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        {filteredPayments.map((payment) => (
                          <tr
                            key={payment.Id}
                            className="border-b border-border hover:bg-accent transition-colors"
                          >
                            <td className="p-3 text-sm">
                              {formatDateTime(payment.Date)}
                            </td>
                            <td className="p-3 text-sm">
                              {payment.FamilyName || "-"}
                            </td>
                            <td className="p-3">
                              <Badge variant="outline">{payment.Fund}</Badge>
                            </td>
                            <td className="p-3 text-sm text-muted-foreground">
                              {payment.Method || "-"}
                            </td>
                            <td className="p-3 text-sm text-right font-medium">
                              {formatCurrency(payment.Amount)}
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>

          {/* Dépôts récents */}
          <div className="lg:col-span-1">
            <Card>
              <CardHeader>
                <CardTitle>Dépôts récents</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-3">
                  {deposits.slice(0, 5).map((deposit) => (
                    <div
                      key={deposit.Id}
                      className="flex items-center justify-between p-3 rounded-lg border border-border hover:bg-accent transition-colors cursor-pointer"
                    >
                      <div>
                        <p className="font-medium text-sm">
                          {new Date(deposit.Date).toLocaleDateString("fr-FR", {
                            day: "numeric",
                            month: "short",
                          })}
                        </p>
                        <p className="text-xs text-muted-foreground">
                          {deposit.Type}
                        </p>
                      </div>
                      <Badge
                        variant={deposit.Closed ? "success" : "secondary"}
                      >
                        {deposit.Closed ? "Clôturé" : "Ouvert"}
                      </Badge>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>

            {/* Fonds de dons */}
            <Card className="mt-4">
              <CardHeader>
                <CardTitle>Fonds de dons</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-2">
                  {funds.map((fund) => (
                    <div
                      key={fund.Id}
                      className="flex items-center justify-between p-2 rounded-lg hover:bg-accent transition-colors cursor-pointer"
                    >
                      <span className="text-sm">{fund.Name}</span>
                      <Badge
                        variant={fund.Active ? "success" : "secondary"}
                      >
                        {fund.Active ? "Actif" : "Inactif"}
                      </Badge>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </AppShell>
  )
}
