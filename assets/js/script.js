/* assets/js/script.js */
    document.addEventListener('DOMContentLoaded', function() {

        // ============================================
        // 1. NAVIGATION DES ONGLETS (TABS)
        // ============================================
        var navItems = document.querySelectorAll('.nav-item[data-tab]');
        var tabPanels = document.querySelectorAll('.tab-panel');
        var breadcrumbText = document.getElementById('breadcrumb-text');

        var breadcrumbMap = {
            'dashboard': 'Tableau de Bord',
            'import-budget': 'Import Budget',
            'import-depense': 'Import Depenses',
            'gestion-utilisateurs': 'Gestion Utilisateurs'
        };

        navItems.forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                navItems.forEach(function(nav) { nav.classList.remove('active'); });
                this.classList.add('active');

                var targetId = this.getAttribute('data-tab');
                tabPanels.forEach(function(panel) { panel.classList.remove('active'); });

                var targetPanel = document.getElementById(targetId);
                if (targetPanel) { targetPanel.classList.add('active'); }
                if (breadcrumbText && breadcrumbMap[targetId]) {
                    breadcrumbText.textContent = breadcrumbMap[targetId];
                }
            });
        });

        // ============================================
        // 2. ELEMENTS DU DASHBOARD
        // ============================================
        var anneeFilterDashboard = document.getElementById('annee-filter-dashboard');
        var deptFilter = document.getElementById('main-dept-filter');

        var kpiBudgetVal = document.getElementById('kpi-budget-value');
        var kpiDepenseVal = document.getElementById('kpi-depense-value');
        var kpiDispoVal = document.getElementById('kpi-dispo-value');
        var kpiTauxVal = document.getElementById('kpi-taux-value');
        var kpiTauxBar = document.getElementById('kpi-taux-bar');
        var kpiBudgetLabel = document.getElementById('kpi-budget-label');

        var detailBody = document.getElementById('detail-body');
        var detailPanelTitle = document.getElementById('detail-panel-title');

        function cleanString(str) {
            if (!str) return '';
            var cleaned = str.toString()
                            .normalize("NFD")
                            .replace(/[\u0300-\u036f]/g, "")
                            .trim()
                            .toLowerCase();
            if (cleaned === 'etudes' || cleaned === 'etudes') return 'etude';
            return cleaned;
        }

        function getUrlParams() {
            var params = new URLSearchParams(window.location.search);
            return {
                annee: params.get('annee') || null,
                dept: params.get('dept') || null,
                error: params.get('error') || null,
                success: params.get('success') || null
            };
        }

        // ============================================
        // 3. FONCTION POUR METTRE A JOUR LES KPI
        // ============================================
        function updateKPICards(budget, depense, label, deptName) {
            var dispo = budget - depense;
            var taux = budget > 0 ? Math.round((depense / budget) * 100 * 10) / 10 : 0;

            if (kpiBudgetVal) {
                kpiBudgetVal.textContent = new Intl.NumberFormat('fr-FR').format(budget) + ' Ar';
                kpiBudgetVal.setAttribute('data-global', budget);
            }
            if (kpiDepenseVal) {
                kpiDepenseVal.textContent = new Intl.NumberFormat('fr-FR').format(depense) + ' Ar';
                kpiDepenseVal.setAttribute('data-global', depense);
            }
            if (kpiDispoVal) {
                kpiDispoVal.textContent = new Intl.NumberFormat('fr-FR').format(dispo) + ' Ar';
                kpiDispoVal.setAttribute('data-global', dispo);
            }
            if (kpiTauxVal) {
                kpiTauxVal.textContent = taux + ' %';
                kpiTauxVal.setAttribute('data-global', taux);
            }
            
            if (kpiTauxBar) {
                kpiTauxBar.style.width = Math.min(taux, 100) + '%';
                kpiTauxBar.classList.remove('high-error', 'medium-warning', 'low-success');
                
                if (taux > 100) {
                    kpiTauxBar.classList.add('high-error'); 
                } else if (taux >= 75 && taux <= 100) {
                    kpiTauxBar.classList.add('medium-warning'); 
                } else {
                    kpiTauxBar.classList.add('low-success'); 
                }
            }

            if (kpiBudgetLabel) {
                if (deptName && deptName !== 'all') {
                    kpiBudgetLabel.innerHTML = label + ' <span class="kpi-dept-name">' + deptName + '</span>';
                } else {
                    kpiBudgetLabel.textContent = label;
                }
            }
        }

        // ============================================
        // 4. FONCTION POUR METTRE A JOUR LE DASHBOARD
        // ============================================
        function updateDashboard(annee, dept) {
            var donnees = window.donneesParAnnee || {};
            var data = donnees[annee];
            
            if (!data) {
                return;
            }

            var budget = data.budget || 0;
            var depense = data.depense || 0;
            var label = "Budget Global";

            if (dept && dept !== 'all') {
                if (data.departements && data.departements[dept] !== undefined) {
                    budget = data.departements[dept] || 0;
                    depense = data.departements[dept] || 0;
                    label = "Budget Alloue";
                } else if (window.departementsStatsData) {
                    var found = window.departementsStatsData.find(function(item) {
                        return cleanString(item.nom_departement) === cleanString(dept);
                    });
                    if (found) {
                        budget = parseFloat(found.budget_alloue) || 0;
                        depense = parseFloat(found.total_depense) || 0;
                        label = "Budget Alloue";
                    }
                }
                
                if (detailPanelTitle) {
                    detailPanelTitle.textContent = "Details des Depenses Realisees — " + dept;
                }
            } else {
                if (detailPanelTitle) {
                    detailPanelTitle.textContent = "Veuillez selectionner un departement pour voir les details";
                }
            }

            updateKPICards(budget, depense, label, dept);
        }

        // ============================================
        // 5. LOGIQUE DE FILTRAGE DEPARTEMENT
        // ============================================
        if (deptFilter) {
            deptFilter.addEventListener('change', function() {
                var selectedDept = this.value;
                var anneeSelectionnee = anneeFilterDashboard ? anneeFilterDashboard.value : 'all';

                if (selectedDept === 'all') {
                    window.location.href = 'dashboard.php?annee=' + anneeSelectionnee;
                } else {
                    window.location.href = 'dashboard.php?annee=' + anneeSelectionnee + '&dept=' + selectedDept;
                }
            });
        }

        // ============================================
        // 6. EVENEMENT DU FILTRE ANNEE
        // ============================================
        if (anneeFilterDashboard) {
            anneeFilterDashboard.addEventListener('change', function() {
                var annee = this.value;
                window.location.href = 'dashboard.php?annee=' + annee;
            });
        }

        // ============================================
        // 7. GESTION DU BOUTON ENVOYER PAR EMAIL
        // ============================================
        var btnEmail = document.getElementById('btn-send-email');
        if (btnEmail) {
            btnEmail.addEventListener('click', function() {
                var inputEmail = document.getElementById('input-email-destinataire');
                var emailVoasoratra = inputEmail ? inputEmail.value.trim() : "";
                var selectedDept = deptFilter ? deptFilter.value : 'all';
                var anneeSelectionnee = anneeFilterDashboard ? anneeFilterDashboard.value : 'all';

                if (selectedDept === 'all') {
                    alert("Veuillez selectionner un departement avant d'envoyer le rapport.");
                    return;
                }

                if (emailVoasoratra === "") {
                    alert("Veuillez saisir une adresse email pour envoyer le rapport.");
                    return;
                }

                btnEmail.textContent = "Envoi en cours...";
                btnEmail.disabled = true;

                fetch('../actions/send_report_email.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        departement: selectedDept,
                        email_destinataire: emailVoasoratra,
                        annee: anneeSelectionnee
                    })
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    alert(data.message);
                    btnEmail.textContent = "ENVOYER PAR EMAIL";
                    btnEmail.disabled = false;
                })
                .catch(function(err) {
                    console.error(err);
                    alert("Erreur lors de l'envoi de l'email.");
                    btnEmail.textContent = "ENVOYER PAR EMAIL";
                    btnEmail.disabled = false;
                });
            });
        }

        // ============================================
        // 8. INITIALISATION
        // ============================================
        function initFilters() {
            var urlParams = getUrlParams();
            
            if (urlParams.annee && anneeFilterDashboard) {
                var optionExists = false;
                for (var i = 0; i < anneeFilterDashboard.options.length; i++) {
                    if (anneeFilterDashboard.options[i].value === urlParams.annee) {
                        optionExists = true;
                        break;
                    }
                }
                if (optionExists) {
                    anneeFilterDashboard.value = urlParams.annee;
                }
            }
            
            if (urlParams.dept && deptFilter) {
                var optionExists = false;
                for (var i = 0; i < deptFilter.options.length; i++) {
                    if (deptFilter.options[i].value === urlParams.dept) {
                        optionExists = true;
                        break;
                    }
                }
                if (optionExists) {
                    deptFilter.value = urlParams.dept;
                }
            } else if (deptFilter) {
                deptFilter.value = 'all';
            }
        }

        initFilters();
        
        var params = getUrlParams();
        var annee = params.annee || (anneeFilterDashboard ? anneeFilterDashboard.value : '2026-2027');
        var dept = params.dept || 'all';
        
        if (window.donneesParAnnee && window.donneesParAnnee[annee]) {
            setTimeout(function() {
                updateDashboard(annee, dept);
            }, 100);
        } else if (window.donneesParAnnee) {
            var annees = Object.keys(window.donneesParAnnee);
            if (annees.length > 0) {
                var nouvelleAnnee = annees[0];
                if (anneeFilterDashboard) {
                    anneeFilterDashboard.value = nouvelleAnnee;
                }
                setTimeout(function() {
                    updateDashboard(nouvelleAnnee, dept);
                }, 100);
            }
        }

        // ============================================
        // 9. HISTORIQUE LOCALSTORAGE
        // ============================================
        var formBudget = document.getElementById('form-import-budget');
        var formDepense = document.getElementById('form-import-depense');
        var fileBudget = document.getElementById('file-input-budget');
        var fileDepense = document.getElementById('file-input-depense');

        function getFormattedCurrentDate() {
            var taty = new Date();
            var j = String(taty.getDate()).padStart(2, '0');
            var m = String(taty.getMonth() + 1).padStart(2, '0');
            var a = taty.getFullYear();
            var h = String(taty.getHours()).padStart(2, '0');
            var mi = String(taty.getMinutes()).padStart(2, '0');
            return j + '/' + m + '/' + a + ' ' + h + ':' + mi;
        }

        function supprimerLog(type, index) {
            var storageKey = (type === 'budget') ? 'logs_import_budget' : 'logs_import_depense';
            var logs = JSON.parse(localStorage.getItem(storageKey)) || [];
            
            if (index >= 0 && index < logs.length) {
                var nomFichier = logs[index].nom_fichier;
                var confirmation = confirm("Etes-vous sur de vouloir supprimer l'historique d'import du fichier \"" + nomFichier + "\" ?");
                
                if (confirmation) {
                    logs.splice(index, 1);
                    localStorage.setItem(storageKey, JSON.stringify(logs));
                    renderLocalLogs(type);
                }
            }
        }

        function renderLocalLogs(type) {
            var storageKey = (type === 'budget') ? 'logs_import_budget' : 'logs_import_depense';
            var tableId = (type === 'budget') ? 'table-log-budget' : 'table-log-depense';
            var tableBody = document.querySelector('#' + tableId + ' tbody');
            
            if (!tableBody) return;

            var logs = JSON.parse(localStorage.getItem(storageKey)) || [];
            tableBody.innerHTML = '';

            if (logs.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: gray;">Aucun historique d\'importation trouve.</td></tr>';
                return;
            }

            logs.forEach(function(item, index) {
                var row = document.createElement('tr');
                // Utiliser "Succes" ou "Refuse" selon le statut
                var statut = item.statut;
                var badgeClass = (statut === 'Succes' || statut === 'Succès') ? 'badge-status-js succes' : 'badge-status-js refuse';
                
                row.innerHTML = '<td>' + escapeHtml(item.nom_fichier) + '</td>' +
                                '<td>' + item.date_action + '</td>' +
                                '<td><span class="' + badgeClass + '">' + statut + '</span></td>' +
                                '<td><button class="btn-supprimer-log" data-type="' + type + '" data-index="' + index + '" title="Supprimer cet historique">Supprimer</button></td>';
                tableBody.appendChild(row);
            });

            var boutonsSupprimer = tableBody.querySelectorAll('.btn-supprimer-log');
            boutonsSupprimer.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var type = this.getAttribute('data-type');
                    var index = parseInt(this.getAttribute('data-index'));
                    supprimerLog(type, index);
                });
            });
        }

        function saveNewLog(type, fileName, status) {
            var storageKey = (type === 'budget') ? 'logs_import_budget' : 'logs_import_depense';
            var logs = JSON.parse(localStorage.getItem(storageKey)) || [];
            
            var newLog = {
                nom_fichier: fileName,
                date_action: getFormattedCurrentDate(), 
                statut: status
            };

            logs.unshift(newLog);
            if (logs.length > 5) { logs.pop(); }

            localStorage.setItem(storageKey, JSON.stringify(logs));
            renderLocalLogs(type);
        }

        // Gestion des imports budget
        if (formBudget) {
            formBudget.addEventListener('submit', function(e) {
                if (fileBudget && fileBudget.files.length > 0) {
                    var storageKey = 'logs_import_budget';
                    var oldLogs = JSON.parse(localStorage.getItem(storageKey)) || [];
                    
                    var tempLog = {
                        nom_fichier: fileBudget.files[0].name,
                        date_action: getFormattedCurrentDate(),
                        statut: 'En cours'
                    };
                    
                    oldLogs.unshift(tempLog);
                    if (oldLogs.length > 5) { oldLogs.pop(); }
                    localStorage.setItem(storageKey, JSON.stringify(oldLogs));
                    renderLocalLogs('budget');
                }
            });
        }

        // Gestion des imports depense
        if (formDepense) {
            formDepense.addEventListener('submit', function(e) {
                if (fileDepense && fileDepense.files.length > 0) {
                    var storageKey = 'logs_import_depense';
                    var oldLogs = JSON.parse(localStorage.getItem(storageKey)) || [];
                    
                    var tempLog = {
                        nom_fichier: fileDepense.files[0].name,
                        date_action: getFormattedCurrentDate(),
                        statut: 'En cours'
                    };
                    
                    oldLogs.unshift(tempLog);
                    if (oldLogs.length > 5) { oldLogs.pop(); }
                    localStorage.setItem(storageKey, JSON.stringify(oldLogs));
                    renderLocalLogs('depense');
                }
            });
        }

        // Mise a jour des statuts apres chargement de la page
        function updateLogsStatus() {
            var urlParams = new URLSearchParams(window.location.search);
            
            // Pour les logs budget
            var storageKeyBudget = 'logs_import_budget';
            var logsBudget = JSON.parse(localStorage.getItem(storageKeyBudget)) || [];
            var updatedBudget = false;
            
            // Verifier si erreur budget
            if (urlParams.has('error') && logsBudget.length > 0) {
                var errorParam = urlParams.get('error');
                if (errorParam && (errorParam.includes('budget') || errorParam.includes('modification') || errorParam.includes('fichier'))) {
                    if (logsBudget[0].statut === 'En cours') {
                        logsBudget[0].statut = 'Refuse';
                        updatedBudget = true;
                    }
                }
            }
            
            // Verifier si succes budget
            if (urlParams.has('success') && logsBudget.length > 0) {
                var successParam = urlParams.get('success');
                if (successParam && (successParam.includes('budget') || successParam.includes('Budget') || successParam.includes('Importation'))) {
                    if (logsBudget[0].statut === 'En cours') {
                        logsBudget[0].statut = 'Succes';
                        updatedBudget = true;
                    }
                }
            }
            
            if (updatedBudget) {
                localStorage.setItem(storageKeyBudget, JSON.stringify(logsBudget));
                renderLocalLogs('budget');
            }
            
            // Pour les logs depense
            var storageKeyDepense = 'logs_import_depense';
            var logsDepense = JSON.parse(localStorage.getItem(storageKeyDepense)) || [];
            var updatedDepense = false;
            
            // Verifier si erreur depense
            if (urlParams.has('error') && logsDepense.length > 0) {
                var errorParam = urlParams.get('error');
                if (errorParam && (errorParam.includes('depense') || errorParam.includes('Depense') || errorParam.includes('fichier'))) {
                    if (logsDepense[0].statut === 'En cours') {
                        logsDepense[0].statut = 'Refuse';
                        updatedDepense = true;
                    }
                }
            }
            
            // Verifier si succes depense
            if (urlParams.has('success') && logsDepense.length > 0) {
                var successParam = urlParams.get('success');
                if (successParam && (successParam.includes('depense') || successParam.includes('Depense') || successParam.includes('Succes'))) {
                    if (logsDepense[0].statut === 'En cours') {
                        logsDepense[0].statut = 'Succes';
                        updatedDepense = true;
                    }
                }
            }
            
            if (updatedDepense) {
                localStorage.setItem(storageKeyDepense, JSON.stringify(logsDepense));
                renderLocalLogs('depense');
            }
        }

        function escapeHtml(text) {
            if (!text) return '';
            return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
        }

        // Initialisation des logs
        renderLocalLogs('budget');
        renderLocalLogs('depense');
        
        // Mise a jour des statuts
        setTimeout(function() {
            updateLogsStatus();
        }, 500);
    });

    function ouvrirPopupModification(id, nom, email) {
        var modal = document.getElementById('user-edit-modal');
        if (modal) {
            document.getElementById('modal-user-id').value = id;
            document.getElementById('modal-user-nom').value = nom;
            document.getElementById('modal-user-email').value = email;
            modal.style.display = 'flex';
        }
    }

    function fermerPopupModification() {
        var modal = document.getElementById('user-edit-modal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function declencherSuppression(id, nom) {
        var confirmation = confirm("Etes-vous sur de vouloir supprimer definitivement l'utilisateur " + nom + " ?");
        if (confirmation) {
            window.location.href = "../actions/update_utilisateurs.php?action=supprimer&id=" + id;
        }
    }