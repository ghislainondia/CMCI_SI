"use client"

import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Avatar } from "@/components/ui/avatar"
import { formatDateTime } from "@/lib/utils"

interface Activity {
  id: string
  type: "new_member" | "donation" | "meeting" | "update"
  title: string
  description: string
  person?: string
  timestamp: Date
}

interface ActivityFeedProps {
  activities?: Activity[]
}

const defaultActivities: Activity[] = [
  {
    id: "1",
    type: "new_member",
    title: "Nouveau membre",
    description: "Marie Kouassi a rejoint l'assemblée",
    person: "Marie Kouassi",
    timestamp: new Date(Date.now() - 1000 * 60 * 30),
  },
  {
    id: "2",
    type: "donation",
    title: "Don enregistré",
    description: "50 000 XOF de la famille Assaba",
    timestamp: new Date(Date.now() - 1000 * 60 * 60 * 2),
  },
  {
    id: "3",
    type: "meeting",
    title: "Réunion terminée",
    description: "Assemblée chez Ghislain - 12 présents",
    timestamp: new Date(Date.now() - 1000 * 60 * 60 * 24),
  },
  {
    id: "4",
    type: "update",
    title: "Fiche mise à jour",
    description: "Jean-Baptiste a mis à jour ses coordonnées",
    person: "Jean-Baptiste",
    timestamp: new Date(Date.now() - 1000 * 60 * 60 * 48),
  },
]

export function ActivityFeed({ activities = defaultActivities }: ActivityFeedProps) {
  return (
    <Card>
      <CardHeader>
        <CardTitle>Activités récentes</CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-4">
          {activities.map((activity) => (
            <div key={activity.id} className="flex gap-3">
              <Avatar className="h-9 w-9 bg-primary/10">
                <span className="text-primary text-xs font-medium">
                  {activity.person
                    ? activity.person
                        .split(" ")
                        .map((n) => n[0])
                        .join("")
                    : activity.type === "donation"
                    ? "€"
                    : "📅"}
                </span>
              </Avatar>
              <div className="flex-1 space-y-1">
                <p className="text-sm font-medium leading-none">
                  {activity.title}
                </p>
                <p className="text-sm text-muted-foreground">
                  {activity.description}
                </p>
              </div>
              <span className="text-xs text-muted-foreground whitespace-nowrap">
                {formatDateTime(activity.timestamp)}
              </span>
            </div>
          ))}
        </div>
      </CardContent>
    </Card>
  )
}
