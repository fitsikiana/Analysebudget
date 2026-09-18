-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 22 juil. 2026 à 08:59
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gestion_budgetaire`
--

-- --------------------------------------------------------

--
-- Structure de la table `budgets`
--

CREATE TABLE `budgets` (
  `id_budget` int(11) NOT NULL,
  `montant` decimal(15,2) DEFAULT NULL,
  `annee` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `budgets`
--

INSERT INTO `budgets` (`id_budget`, `montant`, `annee`) VALUES
(22, 938576.00, '2025-2026');

-- --------------------------------------------------------

--
-- Structure de la table `departements`
--

CREATE TABLE `departements` (
  `id_departement` int(11) NOT NULL,
  `nom_departement` varchar(50) DEFAULT NULL,
  `annee` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `departements`
--

INSERT INTO `departements` (`id_departement`, `nom_departement`, `annee`) VALUES
(19, 'Etude', '2026-2027'),
(20, 'Administration', '2026-2027'),
(21, 'Logistique', '2026-2027'),
(22, 'Etude', '2025-2026'),
(23, 'Administration', '2025-2026'),
(24, 'Logistique', '2025-2026');

-- --------------------------------------------------------

--
-- Structure de la table `depense_prevision`
--

CREATE TABLE `depense_prevision` (
  `id_depense_prevision` int(11) NOT NULL,
  `id_departement` int(11) DEFAULT NULL,
  `id_budget` int(11) DEFAULT NULL,
  `rubrique` varchar(50) DEFAULT NULL,
  `montant_departement` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `depense_prevision`
--

INSERT INTO `depense_prevision` (`id_depense_prevision`, `id_departement`, `id_budget`, `rubrique`, `montant_departement`) VALUES
(184, 22, 22, 'Vacation Prof', 75200000.00),
(185, 23, 22, 'Emprunts', 100000.00),
(186, 24, 22, 'Mobilier', 3825000.00),
(187, 22, 22, 'Rattrapage', 1071000.00),
(188, 23, 22, 'Remboursements des apports', 10288600.00),
(189, 24, 22, 'Matériel informatique', 1755000.00),
(190, 22, 22, 'Vacation et indemnités impayés 2024-2025', 9445000.00),
(191, 23, 22, 'Rémunération personnel Oct-Sept 2026', 63240000.00),
(192, 24, 22, 'Réparation', 1475000.00),
(193, 22, 22, 'Crédit de communication', 510000.00),
(194, 23, 22, 'Rémunération personnel Juil 2025-Sept', 14850000.00),
(195, 24, 22, 'Charge exceptionnelle', 100000.00),
(196, 22, 22, 'Vacation renforcement', 500000.00),
(197, 23, 22, 'Charges personnel cantine Juil-Oct', 1132000.00),
(198, 22, 22, 'Vacation de réunion Prof', 1400000.00),
(199, 23, 22, 'CNAPS arriéré juillet + majoration', 572000.00),
(200, 22, 22, 'Assurances', 1250000.00),
(201, 23, 22, 'Crédit de communication', 244000.00),
(202, 22, 22, 'Indemnité de repas réunion Tana (2 fois)', 80000.00),
(203, 23, 22, 'Matériel de nettoyage', 234500.00),
(204, 22, 22, 'Frais de déplacement réunion Tana (2 fois)', 100000.00),
(205, 23, 22, 'Produits de nettoyage', 448900.00),
(206, 22, 22, 'Frais publicitaire', 1050000.00),
(207, 23, 22, 'Charges patronales CNAPS', 945000.00),
(208, 22, 22, 'Déplacements (Achats, sensibilisation)', 200000.00),
(209, 23, 22, 'Indemnité repas réunion Tana (2 fois)', 160000.00),
(210, 22, 22, 'Fournitures de bureau', 4489000.00),
(211, 23, 22, 'Frais déplacement réunion Tana (2 fois)', 300000.00),
(212, 22, 22, 'Sortie de promotion et réception', 3925000.00),
(213, 23, 22, 'JIRAMA', 1440000.00),
(214, 22, 22, 'Charge exceptionnelle', 500000.00),
(215, 23, 22, 'Rano', 665000.00),
(216, 23, 22, 'Fournitures de bureau', 370000.00),
(217, 23, 22, 'STARLINK oct 2025-Sept 2026', 2400000.00),
(218, 23, 22, 'Enveloppe Pst chq Mardi', 200000.00),
(219, 23, 22, 'Charge exceptionnelle', 200000.00),
(220, 22, 22, 'Formation', 200000.00),
(221, 24, 22, '20000', 46192.00),
(222, 22, 22, 'Examen', 300000.00),
(223, 24, 22, '50000', 46192.00),
(224, 22, 22, 'Ecolage', 300000.00),
(225, 24, 22, '40000', 46192.00);

-- --------------------------------------------------------

--
-- Structure de la table `depense_realisees`
--

CREATE TABLE `depense_realisees` (
  `id_depense_realisee` int(11) NOT NULL,
  `id_budget` int(11) DEFAULT NULL,
  `id_departement` int(11) DEFAULT NULL,
  `rubrique` varchar(50) DEFAULT NULL,
  `montant` decimal(15,2) DEFAULT NULL,
  `date_depense` date DEFAULT NULL,
  `annee` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `depense_realisees`
--

INSERT INTO `depense_realisees` (`id_depense_realisee`, `id_budget`, `id_departement`, `rubrique`, `montant`, `date_depense`, `annee`) VALUES
(89, 22, 22, 'Formation', 200000.00, '2026-06-19', '2025-2026'),
(90, 22, 23, 'Logiciel', 20000.00, '2026-06-19', '2025-2026'),
(91, 22, 24, 'Securité', 100000.00, '2026-06-19', '2025-2026'),
(92, 22, 22, 'Examen', 300000.00, '2026-06-19', '2025-2026'),
(93, 22, 23, 'Transport', 50000.00, '2026-06-19', '2025-2026'),
(94, 22, 24, 'Menage', 200000.00, '2026-06-19', '2025-2026'),
(95, 22, 22, 'Ecolage', 300000.00, '2026-06-19', '2025-2026'),
(96, 22, 23, 'salaire de prof', 40000.00, '2026-06-19', '2025-2026'),
(97, 22, 24, 'Electricite', 200000.00, '2026-06-19', '2025-2026');

-- --------------------------------------------------------

--
-- Structure de la table `imports_history`
--

CREATE TABLE `imports_history` (
  `id` int(11) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_hash` varchar(32) NOT NULL,
  `imported_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `imports_history`
--

INSERT INTO `imports_history` (`id`, `file_type`, `file_hash`, `imported_at`) VALUES
(23, 'budget', 'd13f78abd7614d26e5fbbb91eb22fa75', '2026-07-06 05:43:22'),
(24, 'depense', 'e152de8c67192f1537c1f40e9cc36500', '2026-07-06 05:43:40'),
(25, 'budget', '8cbf14c770012eee858c9a175ecad402', '2026-07-20 05:38:16'),
(26, 'depense', 'cd64217bf96acb624d575714059079dd', '2026-07-20 05:55:15'),
(29, 'budget', 'cd64217bf96acb624d575714059079dd', '2026-07-21 05:22:56');

-- --------------------------------------------------------

--
-- Structure de la table `import_logs`
--

CREATE TABLE `import_logs` (
  `id_import` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `nom_fichier` varchar(50) DEFAULT NULL,
  `type_import` varchar(20) DEFAULT NULL,
  `date_import` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `import_logs`
--

INSERT INTO `import_logs` (`id_import`, `id_utilisateur`, `nom_fichier`, `type_import`, `date_import`) VALUES
(26, 4, 'budget.xlsx', 'Budget Annuel', '2026-07-06'),
(27, 4, 'depenses.xlsx', 'Depenses Realisees', '2026-07-06'),
(28, 4, 'Budget_previsionnel_reconstitue.xlsx', 'Budget Annuel', '2026-07-20'),
(29, 4, 'depense2025.xlsx', 'Depenses Realisees', '2026-07-20'),
(30, 4, 'Budget_previsionnel_reconstitue.xlsx', 'Budget Annuel', '2026-07-20'),
(31, 4, 'depense2025.xlsx', 'Depenses Realisees', '2026-07-20'),
(32, 4, 'depense2025.xlsx', 'Budget Annuel', '2026-07-21');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id_utilisateur` int(11) NOT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mot_de_passe` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id_utilisateur`, `nom`, `email`, `mot_de_passe`) VALUES
(4, 'Iavisoa Fitsikina', 'admin@gmail.com', '$2y$12$aiUPJ.uYD.QvMpLkR/Gn7epQ4eJazYNhRXFjXkjOun9CqRATlD1CG');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `budgets`
--
ALTER TABLE `budgets`
  ADD PRIMARY KEY (`id_budget`);

--
-- Index pour la table `departements`
--
ALTER TABLE `departements`
  ADD PRIMARY KEY (`id_departement`);

--
-- Index pour la table `depense_prevision`
--
ALTER TABLE `depense_prevision`
  ADD PRIMARY KEY (`id_depense_prevision`),
  ADD KEY `id_departement` (`id_departement`),
  ADD KEY `id_budget` (`id_budget`);

--
-- Index pour la table `depense_realisees`
--
ALTER TABLE `depense_realisees`
  ADD PRIMARY KEY (`id_depense_realisee`),
  ADD KEY `id_budget` (`id_budget`),
  ADD KEY `id_departement` (`id_departement`);

--
-- Index pour la table `imports_history`
--
ALTER TABLE `imports_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_file_hash_type` (`file_hash`,`file_type`);

--
-- Index pour la table `import_logs`
--
ALTER TABLE `import_logs`
  ADD PRIMARY KEY (`id_import`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id_utilisateur`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `budgets`
--
ALTER TABLE `budgets`
  MODIFY `id_budget` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT pour la table `departements`
--
ALTER TABLE `departements`
  MODIFY `id_departement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `depense_prevision`
--
ALTER TABLE `depense_prevision`
  MODIFY `id_depense_prevision` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=226;

--
-- AUTO_INCREMENT pour la table `depense_realisees`
--
ALTER TABLE `depense_realisees`
  MODIFY `id_depense_realisee` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT pour la table `imports_history`
--
ALTER TABLE `imports_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT pour la table `import_logs`
--
ALTER TABLE `import_logs`
  MODIFY `id_import` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `depense_prevision`
--
ALTER TABLE `depense_prevision`
  ADD CONSTRAINT `depense_prevision_ibfk_1` FOREIGN KEY (`id_departement`) REFERENCES `departements` (`id_departement`) ON DELETE CASCADE,
  ADD CONSTRAINT `depense_prevision_ibfk_2` FOREIGN KEY (`id_budget`) REFERENCES `budgets` (`id_budget`) ON DELETE CASCADE;

--
-- Contraintes pour la table `depense_realisees`
--
ALTER TABLE `depense_realisees`
  ADD CONSTRAINT `depense_realisees_ibfk_1` FOREIGN KEY (`id_budget`) REFERENCES `budgets` (`id_budget`) ON DELETE CASCADE,
  ADD CONSTRAINT `depense_realisees_ibfk_2` FOREIGN KEY (`id_departement`) REFERENCES `departements` (`id_departement`) ON DELETE CASCADE;

--
-- Contraintes pour la table `import_logs`
--
ALTER TABLE `import_logs`
  ADD CONSTRAINT `import_logs_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
