"use client"

import { AppShell } from "@/components/layout/app-shell"
import { Card, CardContent } from "@/components/ui/card"
import { Calendar as CalendarIcon, Clock } from "lucide-react"

export default function CalendarPage() {
  return (
    <AppShell>
      <div className="space-y-6">
        <div className="flex justify-between items-center">
          <div>
            <h2 className="text-2xl font-bold">Calendrier</h2>
            <p className="text-muted-foreground">
              Gérez les événements, réunions et activités de l'église
            </p>
          </div>
        </div>

        <Card>
          <CardContent className="p-12">
            <div className="text-center text-muted-foreground">
              <CalendarIcon className="h-16 w-16 mx-auto mb-4 opacity-50" />
              <h3 className="text-lg font-semibold mb-2">Calendrier bientôt disponible</h3>
              <p className="mb-6">
                Cette fonctionnalité est en cours de développement
              </p>
              <div className="flex items-center justify-center gap-2 text-sm">
                <Clock className="h-4 w-4" />
                <span>Prévu pour la prochaine version</span>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Aperçu des fonctionnalités prévues */}
        <div className="grid gap-4 md:grid-cols-3">
          <Card>
            <CardContent className="p-6">
              <h3 className="font-semibold mb-2">Événements</h3>
              <p className="text-sm text-muted-foreground">
                Cultes, célébrations et événements spéciaux
              </p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-6">
              <h3 className="font-semibold mb-2">Réunions</h3>
              <p className="text-sm text-muted-foreground">
                Assemblées de maison et réunions de groupe
              </p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-6">
              <h3 className="font-semibold mb-2">Ministères</h3>
              <p className="text-sm text-muted-foreground">
                Activités et planning des différents ministères
              </p>
            </CardContent>
          </Card>
        </div>
      </div>
    </AppShell>
  )
}
