/**
 * DiscipleMakerManager - Gestion des faiseurs de disciple dans ChurchCRM
 *
 * Ce module gère l'affichage et la modification des relations de discipulat
 */

window.DiscipleMakerManager = (function () {
  "use strict";

  /**
   * Initialise le gestionnaire de faiseur de disciple
   */
  function init() {
    console.log("Initialisation du gestionnaire de faiseur de disciple...");

    // Initialiser les événements sur la page de profil
    if ($("#discipleMakerSection").length) {
      initDiscipleMakerSection();
    }

    // Initialiser les événements sur la page de vue faiseur
    if ($("#disciplesSection").length) {
      initDisciplesSection();
    }
  }

  /**
   * Initialise la section faiseur de disciple sur la page profil
   */
  function initDiscipleMakerSection() {
    const personId = window.CRM.currentPersonID;

    // Charger le faiseur de disciple actuel
    loadDiscipleMaker(personId);

    // Gérer le changement de faiseur de disciple
    $("#discipleMakerSelect").on("change", function () {
      const newMakerId = $(this).val() || null;
      updateDiscipleMaker(personId, newMakerId);
    });

    // Gérer le bouton de suppression
    $("#removeDiscipleMakerBtn").on("click", function () {
      if (confirm("Êtes-vous sûr de vouloir supprimer le faiseur de disciple ?")) {
        updateDiscipleMaker(personId, null);
      }
    });
  }

  /**
   * Initialise la section disciples sur la page faiseur
   */
  function initDisciplesSection() {
    const makerId = window.CRM.currentPersonID;

    // Charger la liste des disciples
    loadDisciples(makerId);

    // Gérer le transfert de disciples
    $("#transferDisciplesBtn").on("click", function () {
      showTransferModal(makerId);
    });
  }

  /**
   * Charge le faiseur de disciple d'une personne
   */
  function loadDiscipleMaker(personId) {
    $.ajax({
      url: window.CRM.root + "/api/disciples/person/" + personId + "/maker",
      method: "GET",
      success: function (response) {
        displayDiscipleMaker(response.discipleMaker);
      },
      error: function (error) {
        console.error("Erreur chargement faiseur de disciple:", error);
      },
    });
  }

  /**
   * Affiche le faiseur de disciple dans l'interface
   */
  function displayDiscipleMaker(discipleMaker) {
    if (discipleMaker) {
      $("#discipleMakerInfo").removeClass("d-none");
      $("#noDiscipleMaker").addClass("d-none");

      $("#discipleMakerName").text(discipleMaker.fullName);
      $("#discipleMakerEmail").text(discipleMaker.email || "N/A");

      if (discipleMaker.photo) {
        $("#discipleMakerPhoto").attr("src", window.CRM.root + "/api/person/" + discipleMaker.id + "/thumbnail");
      } else {
        $("#discipleMakerPhoto").attr("src", window.CRM.root + "/Images/Person/nophoto.png");
      }

      // Mettre à jour le select
      $("#discipleMakerSelect").val(discipleMaker.id);
    } else {
      $("#discipleMakerInfo").addClass("d-none");
      $("#noDiscipleMaker").removeClass("d-none");
      $("#discipleMakerSelect").val("");
    }
  }

  /**
   * Met à jour le faiseur de disciple d'une personne
   */
  function updateDiscipleMaker(personId, discipleMakerId) {
    const data = discipleMakerId ? { discipleMakerId: parseInt(discipleMakerId) } : {};

    $.ajax({
      url: window.CRM.root + "/api/disciples/person/" + personId + "/maker",
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify(data),
      success: function (response) {
        window.CRM.displayAlert("success", "Faiseur de disciple mis à jour avec succès");
        loadDiscipleMaker(personId);

        // Si on est sur une page de liste, recharger
        if ($(".disciple-makers-list").length) {
          location.reload();
        }
      },
      error: function (error) {
        console.error("Erreur mise à jour faiseur de disciple:", error);
        const errorMsg = error.responseJSON?.message || "Erreur lors de la mise à jour du faiseur de disciple";
        window.CRM.displayAlert("error", errorMsg);
      },
    });
  }

  /**
   * Charge la liste des disciples d'un faiseur
   */
  function loadDisciples(makerId) {
    // Charger les disciples
    $.ajax({
      url: window.CRM.root + "/api/disciples/maker/" + makerId + "/disciples",
      method: "GET",
      success: function (response) {
        displayDisciples(response.disciples);
      },
      error: function (error) {
        console.error("Erreur chargement disciples:", error);
      },
    });

    // Charger les statistiques
    $.ajax({
      url: window.CRM.root + "/api/disciples/maker/" + makerId + "/stats",
      method: "GET",
      success: function (response) {
        displayDisciplesStats(response);
      },
      error: function (error) {
        console.error("Erreur chargement statistiques:", error);
      },
    });
  }

  /**
   * Affiche la liste des disciples
   */
  function displayDisciples(disciples) {
    const container = $("#disciplesList");
    container.empty();

    if (disciples.length === 0) {
      container.html('<p class="text-muted">Aucun disciple assigné</p>');
      return;
    }

    const html = disciples
      .map(function (disciple) {
        const photoSrc = disciple.photo
          ? window.CRM.root + "/api/person/" + disciple.id + "/thumbnail"
          : window.CRM.root + "/Images/Person/nophoto.png";

        return `
                <div class="card disciple-card mb-2">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <img src="${photoSrc}" alt="${disciple.fullName}"
                                 class="rounded-circle me-3" width="50" height="50">
                            <div>
                                <h5 class="mb-1">
                                    <a href="${window.CRM.root}/People/view/${disciple.id}">
                                        ${disciple.fullName}
                                    </a>
                                </h5>
                                ${disciple.email ? `<p class="mb-0 text-muted">${disciple.email}</p>` : ""}
                                ${disciple.phone ? `<p class="mb-0 text-muted">${disciple.phone}</p>` : ""}
                            </div>
                        </div>
                    </div>
                </div>
            `;
      })
      .join("");

    container.html(html);
  }

  /**
   * Affiche les statistiques de disciples
   */
  function displayDisciplesStats(stats) {
    $("#totalDisciples").text(stats.totalDisciples);
    $("#activeDisciples").text(stats.activeDisciples);

    if (stats.lastUpdated) {
      $("#lastUpdated").text(new Date(stats.lastUpdated).toLocaleString("fr-FR"));
    }
  }

  /**
   * Affiche la modal de transfert de disciples
   */
  function showTransferModal(fromMakerId) {
    // Charger la liste des faiseurs potentiels
    $.ajax({
      url: window.CRM.root + "/api/disciples/makers",
      method: "GET",
      success: function (makers) {
        const modalHtml = buildTransferModal(makers, fromMakerId);
        $(document.body).append(modalHtml);

        $("#transferDisciplesModal").modal("show");

        // Gérer la soumission du formulaire
        $("#transferDisciplesForm").on("submit", function (e) {
          e.preventDefault();
          const toMakerId = $("#toDiscipleMaker").val();
          transferDisciples(fromMakerId, toMakerId);
        });

        // Nettoyer la modal quand elle est fermée
        $("#transferDisciplesModal").on("hidden.bs.modal", function () {
          $(this).remove();
        });
      },
      error: function (error) {
        console.error("Erreur chargement faiseurs:", error);
      },
    });
  }

  /**
   * Construit la modal de transfert
   */
  function buildTransferModal(makers, fromMakerId) {
    const options = makers
      .filter((m) => m.id !== fromMakerId)
      .map((m) => `<option value="${m.id}">${m.fullName} (${m.disciplesCount || 0} disciples)</option>`)
      .join("");

    return `
            <div class="modal fade" id="transferDisciplesModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Transférer les disciples</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="transferDisciplesForm">
                                <div class="mb-3">
                                    <label for="toDiscipleMaker" class="form-label">
                                        Nouveau faiseur de disciple
                                    </label>
                                    <select id="toDiscipleMaker" class="form-select" required>
                                        <option value="">-- Sélectionner un faiseur --</option>
                                        ${options}
                                    </select>
                                </div>
                                <div class="alert alert-info">
                                    <strong>Note:</strong> Tous les disciples seront transférés vers le nouveau faiseur.
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Annuler
                            </button>
                            <button type="submit" form="transferDisciplesForm" class="btn btn-primary">
                                Transférer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
  }

  /**
   * Transfère les disciples d'un faiseur à un autre
   */
  function transferDisciples(fromId, toId) {
    if (!toId) {
      window.CRM.displayAlert("error", "Veuillez sélectionner un faiseur de disciple");
      return;
    }

    $.ajax({
      url: window.CRM.root + "/api/disciples/transfer",
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify({
        fromDiscipleMakerId: parseInt(fromId),
        toDiscipleMakerId: parseInt(toId),
      }),
      success: function (response) {
        window.CRM.displayAlert("success", response.message);
        $("#transferDisciplesModal").modal("hide");
        loadDisciples(fromId); // Recharger la liste
      },
      error: function (error) {
        console.error("Erreur transfert disciples:", error);
        const errorMsg = error.responseJSON?.message || "Erreur lors du transfert";
        window.CRM.displayAlert("error", errorMsg);
      },
    });
  }

  // API publique
  return {
    init: init,
    loadDiscipleMaker: loadDiscipleMaker,
    loadDisciples: loadDisciples,
  };
})();

// Initialisation au chargement de la page
$(document).ready(function () {
  window.DiscipleMakerManager.init();
});
