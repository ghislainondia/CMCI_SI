"use client"

import { AppShell } from "@/components/layout/app-shell"
import { StatCard } from "@/components/dashboard/stat-card"
import { ActivityFeed } from "@/components/dashboard/activity-feed"
import { Users, UserPlus, HandCoins, CalendarCheck } from "lucide-react"

export default function DashboardPage() {
  return (
    <AppShell>
      <div className="space-y-8">
        {/* Stats Grid */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <StatCard
            title="Total membres"
            value="156"
            change={5}
            icon={Users}
          />
          <StatCard
            title="Nouveaux membres"
            value="12"
            change={20}
            icon={UserPlus}
          />
          <StatCard
            title="Dons ce mois"
            value="450 000 XOF"
            change={15}
            icon={HandCoins}
          />
          <StatCard
            title="Présence culte"
            value="87%"
            change={-3}
            icon={CalendarCheck}
          />
        </div>

        {/* Content Grid */}
        <div className="grid gap-6 lg:grid-cols-7">
          {/* Chart placeholder */}
          <div className="lg:col-span-4">
            <div className="rounded-lg border border-border bg-card p-6">
              <h3 className="text-lg font-semibold mb-4">
                Évolution des membres
              </h3>
              <div className="h-64 flex items-center justify-center text-muted-foreground">
                <div className="text-center">
                  <CalendarCheck className="h-12 w-12 mx-auto mb-2 opacity-50" />
                  <p>Graphique bientôt disponible</p>
                </div>
              </div>
            </div>
          </div>

          {/* Activity Feed */}
          <div className="lg:col-span-3">
            <ActivityFeed />
          </div>
        </div>

        {/* Quick Actions */}
        <div className="rounded-lg border border-border bg-card p-6">
          <h3 className="text-lg font-semibold mb-4">Actions rapides</h3>
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <button className="flex items-center gap-3 p-3 rounded-lg border border-border hover:bg-accent transition-colors">
              <div className="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                <UserPlus className="h-5 w-5 text-primary" />
              </div>
              <span className="text-sm font-medium">Ajouter un membre</span>
            </button>
            <button className="flex items-center gap-3 p-3 rounded-lg border border-border hover:bg-accent transition-colors">
              <div className="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                <HandCoins className="h-5 w-5 text-primary" />
              </div>
              <span className="text-sm font-medium">Enregistrer un don</span>
            </button>
            <button className="flex items-center gap-3 p-3 rounded-lg border border-border hover:bg-accent transition-colors">
              <div className="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                <CalendarCheck className="h-5 w-5 text-primary" />
              </div>
              <span className="text-sm font-medium">Créer une réunion</span>
            </button>
            <button className="flex items-center gap-3 p-3 rounded-lg border border-border hover:bg-accent transition-colors">
              <div className="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                <Users className="h-5 w-5 text-primary" />
              </div>
              <span className="text-sm font-medium">Voir le rapport</span>
            </button>
          </div>
        </div>
      </div>
    </AppShell>
  )
}
