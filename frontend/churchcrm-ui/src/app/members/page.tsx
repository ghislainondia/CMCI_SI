"use client"

import { useState, useEffect } from "react"
import { AppShell } from "@/components/layout/app-shell"
import { Input } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent } from "@/components/ui/card"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import {
  Search,
  Filter,
  Plus,
  Mail,
  Phone,
  Eye,
  Loader2,
} from "lucide-react"
import { api, type Person } from "@/lib/api"
import { formatDate } from "@/lib/utils"

export default function MembersPage() {
  const [members, setMembers] = useState<Person[]>([])
  const [filteredMembers, setFilteredMembers] = useState<Person[]>([])
  const [loading, setLoading] = useState(true)
  const [searchQuery, setSearchQuery] = useState("")
  const [filter, setFilter] = useState<"all" | "recent" | "updated">("all")
  const [selectedMember, setSelectedMember] = useState<Person | null>(null)

  useEffect(() => {
    async function loadMembers() {
      try {
        const [latest, updated] = await Promise.all([
          api.getLatestPeople(50),
          api.getUpdatedPeople(50),
        ])

        // Fusionner et dédupliquer
        const allMembers = [...latest]
        updated.forEach((u) => {
          if (!allMembers.find((m) => m.PersonId === u.PersonId)) {
            allMembers.push(u)
          }
        })

        setMembers(allMembers)
        setFilteredMembers(allMembers)
      } catch (error) {
        console.error("Erreur lors du chargement des membres:", error)
      } finally {
        setLoading(false)
      }
    }

    loadMembers()
  }, [])

  useEffect(() => {
    let filtered = [...members]

    // Filtre par type
    if (filter === "recent") {
      filtered = filtered.filter((m) => m.Created)
        .sort((a, b) => new Date(b.Created || "").getTime() - new Date(a.Created || "").getTime())
    } else if (filter === "updated") {
      filtered = filtered.filter((m) => m.Modified)
        .sort((a, b) => new Date(b.Modified || "").getTime() - new Date(a.Modified || "").getTime())
    }

    // Recherche
    if (searchQuery.length >= 2) {
      const query = searchQuery.toLowerCase()
      filtered = filtered.filter(
        (m) =>
          m.FullName?.toLowerCase().includes(query) ||
          m.Email?.toLowerCase().includes(query) ||
          m.Phone?.includes(query)
      )
    }

    setFilteredMembers(filtered)
  }, [searchQuery, filter, members])

  return (
    <AppShell>
      <div className="space-y-6">
        {/* Header avec actions */}
        <div className="flex flex-col sm:flex-row gap-4 justify-between">
          <div className="flex-1 flex gap-2">
            {/* Recherche */}
            <div className="relative flex-1 max-w-md">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                type="search"
                placeholder="Rechercher un membre..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="pl-9"
              />
            </div>

            {/* Filtre */}
            <Select value={filter} onValueChange={(v: any) => setFilter(v)}>
              <SelectTrigger className="w-[180px]">
                <Filter className="h-4 w-4 mr-2" />
                <SelectValue placeholder="Filtrer" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Tous</SelectItem>
                <SelectItem value="recent">Récents</SelectItem>
                <SelectItem value="updated">Modifiés</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <Button>
            <Plus className="h-4 w-4 mr-2" />
            Nouveau membre
          </Button>
        </div>

        {/* Contenu principal */}
        <div className="grid gap-6 lg:grid-cols-3">
          {/* Liste des membres */}
          <div className="lg:col-span-2">
            <Card>
              <CardContent className="p-0">
                {loading ? (
                  <div className="flex items-center justify-center py-12">
                    <Loader2 className="h-8 w-8 animate-spin text-muted-foreground" />
                  </div>
                ) : filteredMembers.length === 0 ? (
                  <div className="text-center py-12 text-muted-foreground">
                    Aucun membre trouvé
                  </div>
                ) : (
                  <div className="divide-y divide-border">
                    {filteredMembers.map((member) => (
                      <div
                        key={member.PersonId}
                        onClick={() => setSelectedMember(member)}
                        className={`flex items-center gap-4 p-4 hover:bg-accent cursor-pointer transition-colors ${
                          selectedMember?.PersonId === member.PersonId
                            ? "bg-accent"
                            : ""
                        }`}
                      >
                        {/* Avatar */}
                        <div className="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                          <span className="text-primary font-medium">
                            {member.FirstName?.[0]}
                            {member.LastName?.[0]}
                          </span>
                        </div>

                        {/* Info */}
                        <div className="flex-1 min-w-0">
                          <p className="font-medium truncate">
                            {member.FullName}
                          </p>
                          <div className="flex gap-2 text-sm text-muted-foreground">
                            {member.Email && (
                              <span className="flex items-center gap-1">
                                <Mail className="h-3 w-3" />
                                {member.Email}
                              </span>
                            )}
                            {member.Phone && (
                              <span className="flex items-center gap-1">
                                <Phone className="h-3 w-3" />
                                {member.Phone}
                              </span>
                            )}
                          </div>
                        </div>

                        {/* Badges */}
                        <div className="flex gap-2">
                          {member.Created && (
                            <Badge variant="outline" className="hidden sm:inline-flex">
                              {formatDate(member.Created)}
                            </Badge>
                          )}
                          <Eye className="h-4 w-4 text-muted-foreground" />
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </CardContent>
            </Card>
          </div>

          {/* Détail du membre sélectionné */}
          <div className="lg:col-span-1">
            {selectedMember ? (
              <Card>
                <CardContent className="p-6">
                  <div className="text-center mb-6">
                    <div className="h-20 w-20 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-3">
                      <span className="text-primary text-2xl font-bold">
                        {selectedMember.FirstName?.[0]}
                        {selectedMember.LastName?.[0]}
                      </span>
                    </div>
                    <h3 className="text-xl font-semibold">
                      {selectedMember.FullName}
                    </h3>
                    {selectedMember.Address && (
                      <p className="text-sm text-muted-foreground mt-1">
                        {selectedMember.Address}
                      </p>
                    )}
                  </div>

                  <div className="space-y-4">
                    <div>
                      <p className="text-sm text-muted-foreground">Email</p>
                      <p className="font-medium">{selectedMember.Email || "-"}</p>
                    </div>
                    <div>
                      <p className="text-sm text-muted-foreground">Téléphone</p>
                      <p className="font-medium">{selectedMember.Phone || "-"}</p>
                    </div>
                    <div>
                      <p className="text-sm text-muted-foreground">Genre</p>
                      <p className="font-medium">
                        {selectedMember.Gender === "Male"
                          ? "Homme"
                          : selectedMember.Gender === "Female"
                          ? "Femme"
                          : "-"}
                      </p>
                    </div>
                    {selectedMember.BirthDate && (
                      <div>
                        <p className="text-sm text-muted-foreground">
                          Date de naissance
                        </p>
                        <p className="font-medium">
                          {formatDate(selectedMember.BirthDate)}
                        </p>
                      </div>
                    )}
                    {selectedMember.FamilyId && (
                      <div>
                        <p className="text-sm text-muted-foreground">
                          Assemblée de maison
                        </p>
                        <Badge variant="secondary">
                          Famille #{selectedMember.FamilyId}
                        </Badge>
                      </div>
                    )}
                  </div>

                  <div className="grid grid-cols-2 gap-2 mt-6">
                    <Button variant="outline" size="sm" asChild>
                      <a
                        href={`${process.env.NEXT_PUBLIC_CHURCHCRM_URL || ""}/PersonView.php?PersonID=${selectedMember.PersonId}`}
                        target="_blank"
                        rel="noopener noreferrer"
                      >
                        <Eye className="h-4 w-4 mr-2" />
                        Voir fiche
                      </a>
                    </Button>
                    <Button size="sm">
                      Modifier
                    </Button>
                  </div>
                </CardContent>
              </Card>
            ) : (
              <Card>
                <CardContent className="p-6 text-center text-muted-foreground">
                  Sélectionnez un membre pour voir ses détails
                </CardContent>
              </Card>
            )}
          </div>
        </div>
      </div>
    </AppShell>
  )
}
