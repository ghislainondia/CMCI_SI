"use client"

import { Sidebar } from "./sidebar"
import { Header } from "./header"
import { usePathname } from "next/navigation"

interface AppShellProps {
  children: React.ReactNode
}

export function AppShell({ children }: AppShellProps) {
  const pathname = usePathname()

  const getTitle = () => {
    switch (pathname) {
      case "/":
        return "Tableau de bord"
      case "/members":
        return "Membres"
      case "/donations":
        return "Dons & Contributioons"
      case "/calendar":
        return "Calendrier"
      case "/settings":
        return "Paramètres"
      default:
        return "Tableau de bord"
    }
  }

  return (
    <div className="min-h-screen bg-background">
      <Sidebar />
      <div className="lg:pl-64">
        <Header title={getTitle()} />
        <main className="p-4 lg:p-8">{children}</main>
      </div>
    </div>
  )
}
