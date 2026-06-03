import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { cn } from "@/lib/utils"
import { LucideIcon } from "lucide-react"

interface StatCardProps {
  title: string
  value: string | number
  change?: number
  icon: LucideIcon
  className?: string
}

export function StatCard({ title, value, change, icon: Icon, className }: StatCardProps) {
  return (
    <Card className={className}>
      <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle className="text-sm font-medium text-muted-foreground">
          {title}
        </CardTitle>
        <Icon className="h-4 w-4 text-muted-foreground" />
      </CardHeader>
      <CardContent>
        <div className="text-2xl font-bold">{value}</div>
        {change !== undefined && (
          <p
            className={cn(
              "text-xs mt-1",
              change > 0 ? "text-green-500" : "text-red-500"
            )}
          >
            {change > 0 ? "+" : ""}
            {change}% par rapport au mois dernier
          </p>
        )}
      </CardContent>
    </Card>
  )
}
