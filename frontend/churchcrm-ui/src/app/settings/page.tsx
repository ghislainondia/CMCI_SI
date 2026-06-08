"use client"

import { AppShell } from "@/components/layout/app-shell"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Settings as SettingsIcon } from "lucide-react"

export default function SettingsPage() {
  return (
    <AppShell>
      <div className="space-y-6">
        <div>
          <h2 className="text-2xl font-bold">Paramètres</h2>
          <p className="text-muted-foreground">
            Configurez votre application ChurchCRM
          </p>
        </div>

        <Card>
          <CardContent className="p-12">
            <div className="text-center text-muted-foreground">
              <SettingsIcon className="h-16 w-16 mx-auto mb-4 opacity-50" />
              <h3 className="text-lg font-semibold mb-2">Paramètres bientôt disponibles</h3>
              <p>
                Cette page sera accessible depuis l'interface ChurchCRM
                existante
              </p>
            </div>
          </CardContent>
        </Card>
      </div>
    </AppShell>
  )
}
